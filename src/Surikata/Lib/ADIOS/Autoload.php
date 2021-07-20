<?php

// vseobecna chyba
define('_ADIOS_ERROR_GENERAL', 1001);

// user nema perms na akciu
define('_ADIOS_ERROR_INSUFFICIENT_ACTION_PERMS', 1002);

// user nema prava na tabulku / db
define('_ADIOS_ERROR_INSUFFICIENT_DB_PERMS', 1003);

// akcia neexistuje
define('_ADIOS_ERROR_ACTION_NOT_EXIST', 1004);

// db query error
define('_ADIOS_ERROR_DB_QUERY', 1005);

// neprihlaseny pouzivatel spusta akciu
define('_ADIOS_ERROR_UNAUTHORISED', 1006);

// result json parse error
define('_ADIOS_ERROR_JSON_PARSE', 1007);


define('ADIOS_MODE_FULL', 1);
define('ADIOS_MODE_LITE', 2);




// Autoloader function

spl_autoload_register(function($class) {
  global $___ADIOSObject;

  $class = str_replace("\\", "/", $class);
  $class = trim($class, "/");

  if (strpos($class, "ADIOS/") === FALSE) return;

  $loaded = @include(dirname(__FILE__)."/".str_replace("ADIOS/", "", $class).".php");

  if (!$loaded) {

    if (strpos($class, "ADIOS/Actions") === 0) {

      $class = str_replace("ADIOS/Actions/", "", $class);

      // najprv skusim hladat core akciu
      $tmp = dirname(__FILE__)."/Core/Actions/{$class}.php";
      if (!@include($tmp)) {
        // ak sa nepodari, hladam widgetovsku akciu

        if (preg_match('/([\w]+)\/([\w\/]+)/', $class, $m)) {
          if (!@include($___ADIOSObject->config['dir']."/Widgets/{$m[1]}/Actions/{$m[2]}.php")) {
            // ak ani widgetovska, skusim plugin
            $class = str_replace("Plugins/", "", $class);
            $pathLeft = "";
            $pathRight = "";
            foreach (explode("/", $class) as $pathPart) {
              $pathLeft .= ($pathLeft == "" ? "" : "/").$pathPart;
              $pathRight = str_replace("{$pathLeft}/", "", $class);

              $file = ADIOS_PLUGINS_DIR."/{$pathLeft}/Actions/{$pathRight}.php";
              if (is_file($file)) {
                include($file);
                break;
              }
            }
          }
        }
      }

    } else if (preg_match('/ADIOS\/Widgets\/([\w\/]+)/', $class, $m)) {

      if (!isset($___ADIOSObject)) {
        throw new Exception("ADIOS is not loaded.");
      }

      if (!@include($___ADIOSObject->config['dir']."/Widgets/{$m[1]}/Main.php")) {
        include($___ADIOSObject->config['dir']."/Widgets/{$m[1]}.php");
      }

    } else if (preg_match('/ADIOS\/Plugins\/([\w\/]+)/', $class, $m)) {
      if (!include(ADIOS_PLUGINS_DIR."/{$m[1]}/Main.php")) {
        include(ADIOS_PLUGINS_DIR."/{$m[1]}.php");
      }

    } else if (preg_match('/ADIOS\/([\w\/]+)/', $class, $m)) {
      include($___ADIOSObject->config['dir']."/{$m[1]}.php");
    }

  }
});

