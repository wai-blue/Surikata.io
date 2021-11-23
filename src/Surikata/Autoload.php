<?php

spl_autoload_register(function($class) {
  global $___CASCADAObject;

  $class = trim(str_replace("\\", "/", $class), "/");

  if (preg_match('/^Surikata\/Core\/([\w\/]+)/', $class, $m)) {
    require_once(SURIKATA_ROOT_DIR."/src/Surikata/Core/{$m[1]}.php");
  } else if (preg_match('/^Surikata\/Lib\/([\w\/]+)/', $class, $m)) {
    require_once(SURIKATA_ROOT_DIR."/src/Surikata/Lib/{$m[1]}.php");
  } else if (preg_match('/^Surikata\/Plugins\/([\w\/]+)/', $class, $m)) {
    if (!is_object($___CASCADAObject)) return;

    foreach ($___CASCADAObject->pluginFolders as $pluginFolder) {
      $file_1 = "{$pluginFolder}/{$m[1]}/Main.php";
      $file_2 = "{$pluginFolder}/{$m[1]}.php";
      if (is_file($file_1)) {
        require_once($file_1);
        break;
      } else if (is_file($file_2)) {
        require_once($file_2);
        break;
      }
    }
  } else if (preg_match('/^Surikata\/Themes\/([\w\/]+)/', $class, $m)) {
    if (!is_object($___CASCADAObject)) return;

    foreach ($___CASCADAObject->themeFolders as $themeFolder) {
      $file_1 = "{$themeFolder}/{$m[1]}/Main.php";
      $file_2 = "{$themeFolder}/{$m[1]}.php";

      if (is_file($file_1)) {
        require_once($file_1);
        break;
      } if (is_file($file_2)) {
        require_once($file_2);
        break;
      }
    }
  }
});

