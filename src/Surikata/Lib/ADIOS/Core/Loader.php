<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Exception extends \Exception { }
class NotEnoughPermissionsException extends \Exception { }
class DBException extends \Exception { }
class ActionException extends \Exception { }
class ModelInstallationException extends \Exception { }
class InvalidUidException extends \Exception { }
class FormSaveException extends \Exception { }
class InvalidToken extends \Exception { }

// ADIOS Loader class

class Loader {
  public $gtp = "";
  public $requestedURI = "";
  public $requestedAction = "";
  public $action = "";
  public $uid = "";

  public $logged = FALSE;

  public $config = [];
  public $routing = [];
  public $widgets = [];

  public $pluginObjects = [];
  public $plugins = [];

  public $modelObjects = [];
  public $models = [];

  public $userLogged = FALSE;
  public $userProfile = NULL;

  public $db = NULL;
  public $twig = NULL;
  public $ui = NULL;
  public $console = NULL;
  public $locale = NULL;
  public $email = NULL;
  public $userNotifications = NULL;

  public $assetsUrlMap = [];

  public $actionNestingLevel = 0;
  public $actionStack = [];


  public function __construct($config = NULL, $mode = NULL) {

    global $___ADIOSObject;
    $___ADIOSObject = $this;

    if ($mode === NULL) {
      $mode = ADIOS_MODE_FULL;
    }

    if (is_array($config)) {
      $this->config = $config;
    }

    $this->gtp = $this->config['global_table_prefix'];
    $this->requestedAction = $_REQUEST['action'];
    
    if (empty($this->config['system_table_prefix'])) {
      $this->config['system_table_prefix'] = "adios";
    }

    // pouziva sa ako vseobecny prefix niektorych session premennych,
    // novy ADIOS ma zatial natvrdo hodnotu, lebo sa sessions riesia cez session name
    if (!defined('_ADIOS_ID')) {
      define(
        '_ADIOS_ID',
        $this->config['session_salt']."-".substr(md5($this->config['session_salt']), 0, 5)
      );
    }

    // ak requestuje nejaky Asset (css, js, image, font), tak ho vyplujem a skoncim
    $this->requestedURI = str_replace($this->config['rewrite_base'], "", $_SERVER['REQUEST_URI']);

    $this->assetsUrlMap["adios/assets/css/"] = __DIR__."/../Assets/Css/";
    $this->assetsUrlMap["adios/assets/js/"] = __DIR__."/../Assets/Js/";
    $this->assetsUrlMap["adios/assets/images/"] = __DIR__."/../Assets/Images/";
    $this->assetsUrlMap["adios/assets/webfonts/"] = __DIR__."/../Assets/Webfonts/";

    $this->renderAssets();

    //////////////////////////////////////////////////
    // inicializacia

    try {

      global $gtp;

      $gtp = $this->config['global_table_prefix'];

      // if (!ini_get('short_open_tag')) {
      //   throw new \Exception('FATAL ERROR: short_open_tag is disabled. Enable it in php.ini.');
      // }

      // nacitanie zakladnych ADIOS lib suborov
      include(dirname(__FILE__)."/Lib/basic_functions.php");

      if ($mode == ADIOS_MODE_FULL) {

        // inicializacia Twigu
        include(dirname(__FILE__)."/Lib/Twig.php");

        $eloquentCapsule = new \Illuminate\Database\Capsule\Manager;

        $eloquentCapsule->addConnection([
          "driver"    => "mysql",
          "host"      => $this->config['db_host'],
          "port"      => $this->config['db_port'],
          "database"  => $this->config['db_name'],
          "username"  => $this->config['db_login'],
          "password"  => $this->config['db_password'],
          "charset"   => 'utf8',
          "collation" => 'utf8_unicode_ci',
        ]);

        // Make this Capsule instance available globally.
        $eloquentCapsule->setAsGlobal();

        // Setup the Eloquent ORM.
        $eloquentCapsule->bootEloquent();

        // Image a file su specialne akcie v tom zmysle, ze nie je
        // potrebne mat nainicializovany cely ADIOS, aby zbehli
        // (ide najma o nepotrebne nacitavanie DB configu)
        // Spustaju sa tu, aby sa setrili zdroje.

        if (
          !empty($this->requestedAction)
          && in_array($this->requestedAction, ['Image', 'File'])
        ) {
          $this->finalizeConfig();
          include "{$this->requestedAction}.php";
          die();
        }
      
      }

      // inicializacia pluginov - aj pre FULL aj pre LITE mod

      $this->loadAllPlugins();

      $this->onPluginsLoaded();
    
      if ($mode == ADIOS_MODE_FULL) {

        // start session

        if ($this->config['set_session_time'] ?? TRUE) {
          ini_set('session.gc_maxlifetime', $this->config['session_maxlifetime'] ?? 60 * 60);
          ini_set('session.gc_probability', $this->config['session_probability'] ?? 1);
          ini_set('session.gc_divisor', $this->config['session_divisor'] ?? 1000);
        }

        ini_set('session.use_cookies', $this->config['session_use_cookies'] ?? TRUE);

        session_id();
        session_name(_ADIOS_ID);
        session_start();

        define('_SESSION_ID', session_id());

      }

      // inicializacia locale objektu
      $this->locale = new \ADIOS\Core\Locale($this);

      // inicializacia objektu notifikacii
      $this->userNotifications = new \ADIOS\Core\UserNotifications($this);

      // inicializacia debug konzoly
      $this->console = new \ADIOS\Core\Console($this);

      // inicializacia mailera
      // 2021-07-05 deprecated
      // $this->email = new \ADIOS\Core\Email($this);

      // inicializacia DB - aj pre FULL aj pre LITE mod

      $this->db = new DB($this, [
        'db_host' => $this->getConfig('db_host', ''),
        'db_port' => $this->getConfig('db_port', ''),
        'db_login' => $this->getConfig('db_login', ''),
        'db_password' => $this->getConfig('db_password', ''),
        'db_name' => $this->getConfig('db_name', ''),
        'db_codepage' => $this->getConfig('db_codepage', 'utf8'),
      ]);

      $this->loadConfigFromDB();

      if ($mode == ADIOS_MODE_FULL) {

        // timezone
        date_default_timezone_set($this->config['timezone']);

        // set language

        if (!empty($_SESSION[_ADIOS_ID]['language'])) {
          $this->config['language'] = $_SESSION[_ADIOS_ID]['language'];
        }

        if (!empty($_REQUEST['language'])) {
          $this->config['language'] = $_REQUEST['language'];
          $_SESSION[_ADIOS_ID]['language'] = $_REQUEST['language'];
          setcookie(_ADIOS_ID.'-language', $_REQUEST['language'], time() + (3600 * 365));
        } else if (
          $_SESSION[_ADIOS_ID]['userProfile']['id'] ?? 0 <= 0
          && !empty($_COOKIE[_ADIOS_ID.'-language'])
        ) {
          $this->config['language'] = $_COOKIE[_ADIOS_ID.'-language'];
          $_SESSION[_ADIOS_ID]['language'] = $_COOKIE[_ADIOS_ID.'-language'];
        }

        // user authentication
        if ((int) $_SESSION[_ADIOS_ID]['userProfile']['id'] > 0) {
          $this->userProfile = $_SESSION[_ADIOS_ID]['userProfile'];
          $this->userLogged = TRUE;
        } else if ($this->authUser(
          $_POST['login'],
          $_POST['password'],
          ((int) $_POST['keep_logged_in']) == 1
        )) {
          // ked uz som prihlaseny, redirectnem sa, aby nasledny F5 refresh
          // nevyzadoval form resubmission
          header("Location: {$this->config['url']}");
          exit();//"a {$this->config['url']} .");
        } else {
          $this->userProfile = [];
          $this->userLogged = FALSE;
        }

        // user specific config
        // TODO: toto treba prekontrolovat, velmi pravdepodobne to nefunguje
        // if (is_array($this->config['user'][$this->userProfile['id']])) {
        //   unset($this->config['user'][$this->userProfile['id']]['language']);
        //   $this->mergeConfig($this->config['user'][$this->userProfile['id']]);
        // }
      }

      // finalizacia konfiguracie - aj pre FULL aj pre LITE mode
      $this->finalizeConfig();

      // callback na konci konfiguracneho procesu - aj pre FULL aj pre LITE mode
      $this->onConfigLoaded();

      if ($mode == ADIOS_MODE_FULL) {

        // inicializacia widgetov

        foreach ($this->config['widgets'] as $w_name => $w_config) {
          $this->addWidget($w_name);
        }

        $this->onWidgetsLoaded();

        // inicializacia modelov

        $this->models[] = "Core/Models/Config";
        $this->models[] = "Core/Models/Translate";
        $this->models[] = "Core/Models/User";
        $this->models[] = "Core/Models/UserRole";
        $this->models[] = "Core/Models/Token";

        // vytvorim definiciu tables podla nacitanych modelov

        foreach ($this->models as $modelName) {
          $this->getModel($modelName);
        }

        // inicializacia twigu

        $twigLoader = new \Twig\Loader\ADIOSTwigLoader($this);
        $this->twig = new \Twig\Environment($twigLoader, array(
          'cache' => FALSE,
          'debug' => TRUE,
        ));
        $this->twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->twig->addFunction(new \Twig\TwigFunction('l', function ($str) {
          return l($str);
        }));
        $this->twig->addFunction(new \Twig\TwigFunction('adiosUI', function ($uid, $componentName, $componentParams) {
          global $___ADIOSObject;

          if (!is_array($componentParams)) {
            $componentParams = array();
          }
          return $___ADIOSObject->ui->create("{$componentName}#{$uid}", $componentParams)->render();
        }));
        $this->twig->addFunction(new \Twig\TwigFunction('adiosAction', function ($action, $params = []) {
          global $___ADIOSObject;
          return $___ADIOSObject->renderAction($action, $params);
        }));

        // inicializacia UI wrappera

        $this->ui = new UI($this, []);
      }

      $this->dispatchEventToPlugins("onADIOSAfterInit", ["adios" => $this]);

    } catch (\Exception $e) {
      exit("ADIOS INIT failed. ".$e->getMessage());
    }

    return $this;
  }

  public function isAjax() {
    return isset($_REQUEST['__IS_AJAX__']) && $_REQUEST['__IS_AJAX__'] == "1";
  }

  public function isNestedAction() {
    return ($this->actionNestingLevel > 2);
  }

  public function isWindow() {
    return isset($_REQUEST['__IS_WINDOW__']) && $_REQUEST['__IS_WINDOW__'] == "1";
  }

  public function setRouting($routing) {
    if (is_array($routing)) {
      $this->routing = $routing;
    }
  }

  public function addRouting($routing) {
    if (is_array($routing)) {
      $this->routing = array_merge($this->routing, $routing);
    }
  }

  public function addWidget($widgetName) {
    if (!isset($this->widgets[$widgetName])) {
      try {
        $widgetClassName = "\\ADIOS\\Widgets\\{$widgetName}";
        $this->widgets[$widgetName] = new $widgetClassName($this);
      } catch (\Exception $e) {
        exit("Failed to load widget {$widgetName}: ".$e->getMessage());
      }
    }
  }

  public function getModelClassName($modelName) {
    return "\\ADIOS\\".str_replace("/", "\\", $modelName);
  }

  public function getModel($modelName) {
    if (!isset($this->modelObjects[$modelName])) {
      try {
        $modelClassName = $this->getModelClassName($modelName);

        $this->modelObjects[$modelName] = new $modelClassName($this);

        // $this->db->addTable(
        //   $this->modelObjects[$modelName]->getFullTableSQLName(),
        //   $this->modelObjects[$modelName]->columns()
        // );
        // $this->addRouting($this->modelObjects[$modelName]->routing());

      } catch (\Exception $e) {
        throw new \ADIOS\Core\Exception("Can't find model '{$modelName}'. ".$e->getMessage());
      }
    }

    return $this->modelObjects[$modelName];
  }

  public function getPluginClassName($pluginName) {
    return "\\ADIOS\\Plugins\\".str_replace("/", "\\", $pluginName);
  }

  public function getPlugin($pluginName) {
    return $this->pluginObjects[$pluginName] ?? NULL;
  }

  public function getPlugins() {
    return $this->pluginObjects;
  }

  public function loadAllPlugins($subdir = "") {
    $dir = ADIOS_PLUGINS_DIR.(empty($subdir) ? "" : "/{$subdir}");

    foreach (scandir($dir) as $file) {
      if (in_array($file, [".", ".."])) continue;

      $fullPath = (empty($subdir) ? "" : "{$subdir}/").$file;

      if (
        is_dir("{$dir}/{$file}")
        && !is_file("{$dir}/{$file}/Main.php")
      ) {
        $this->loadAllPlugins($fullPath);
      } else {
        try {
          $tmpPluginClassName = $this->getPluginClassName($fullPath);

          if (class_exists($tmpPluginClassName)) {
            $this->plugins[] = $fullPath;
            $this->pluginObjects[$fullPath] = new $tmpPluginClassName($this);
          }
        } catch (\Exception $e) {
          exit("Failed to load plugin {$fullPath}: ".$e->getMessage());
        }
      }
    }
  }

  public function translate($string, $context = "", $toLanguage = "", $dictionary = []) {
    if ($toLanguage == "") {
      $toLanguage = $this->adios->config['language'] ?? "en";
    }

    if (
      !empty($context)
      && isset($dictionary["CONTEXT:{$context}"])
    ) {
      if (!isset($dictionary["CONTEXT:{$context}"][$toLanguage][$string])) {
        return $string;
      } else {
        return $dictionary["CONTEXT:{$context}"][$toLanguage][$string];
      }
    } else {
      if (!isset($dictionary[$toLanguage][$string])) {
        return $string;
      } else {
        return $dictionary[$toLanguage][$string];
      }
    }
  }

  public function renderAssets() {
    $cachingTime = 3600;
    $headerExpires = "Expires: " . gmdate("D, d M Y H:i:s", time() + $cachingTime) . " GMT";
    $headerCacheControl = "Cache-Control: max-age={$cachingTime}";

    if ($this->requestedURI == "adios/cache.css") {
      $cssCache = $this->renderCSSCache();

      header("Content-type: text/css");
      header("ETag: ".md5($cssCache));
      header($headerExpires);
      header("Pragma: cache");
      header($headerCacheControl);

      echo $cssCache;

      exit();

    } else if ($this->requestedURI == "adios/cache.js") {
      $jsCache = $this->renderJSCache();
      $cachingTime = 3600;

      header("Content-type: text/js");
      header("ETag: ".md5($jsCache));
      header($headerExpires);
      header("Pragma: cache");
      header($headerCacheControl);

      echo $jsCache;

      exit();
    } else {
      foreach ($this->assetsUrlMap as $urlPart => $mapping) {
        if (preg_match('/^'.str_replace("/", "\\/", $urlPart).'/', $this->requestedURI, $m)) {
          if ($mapping instanceof \Closure) {
            $mapping($this, $this->requestedURI);
          } else {
            $ext = strtolower(pathinfo($this->requestedURI, PATHINFO_EXTENSION));

            switch ($ext) {
              case "css":
              case "js":
                header("Content-type: text/{$ext}");
                header($headerExpires);
                header("Pragma: cache");
                header($headerCacheControl);
                echo file_get_contents($mapping.str_replace($urlPart, "", $this->requestedURI));
              break;
              case "eot":
              case "ttf":
              case "woff":
              case "woff2":
                header("Content-type: application/x-font-{$ext}");
                header($headerExpires);
                header("Pragma: cache");
                header($headerCacheControl);
                echo file_get_contents($mapping.str_replace($urlPart, "", $this->requestedURI));
              break;
              case "bmp":
              case "gif":
              case "jpg":
              case "jpeg":
              case "png":
              case "tiff":
              case "webp":
              case "svg":
              case "eot":
              case "ttf":
              case "woff":
              case "woff2":
                header("Content-type: image/{$ext}");
                header($headerExpires);
                header("Pragma: cache");
                header($headerCacheControl);
                echo file_get_contents($mapping.str_replace($urlPart, "", $this->requestedURI));
              break;
            }

            exit();
          }
        }
      }
    }
  }

  public function install() {
    $this->console->clear();

    $installationStart = microtime(TRUE);

    $this->db->start_transaction();

    echo "<h2>Installing models</h2>";

    foreach ($this->models as $modelName) {
      try {
        $model = $this->getModel($modelName);

        $start = microtime(TRUE);

        echo "<b>{$modelName}</b>";

        $model->install();
        echo " [".round((microtime(true) - $start) * 1000, 2)." msec]. ";
      } catch (\ADIOS\Core\ModelInstallationException $e) {
        echo ", <span style='color:orange'>Skipped. Reason: {$e->getMessage()}</span> ";
      } catch (\Exception $e) {
        echo ", <span style='color:red'>Failed. Reason: {$e->getMessage()}.</span> ";
      } catch (\Illuminate\Database\QueryException $e) {
        //
      } catch (\ADIOS\Core\DBException $e) {
        // Moze sa stat, ze vytvorenie tabulky zlyha napr. kvoli
        // "Cannot add or update a child row: a foreign key constraint fails".
        // V takom pripade budem instalaciu opakovat v dalsom kole
      }
    }

    echo "<h2>Creating indexes</h2>";

    foreach ($this->models as $modelName) {
      try {
        $model = $this->getModel($modelName);

        $start = microtime(TRUE);

        echo "<b>{$modelName}</b>";

        $model->installForeignKeys();
        echo " [".round((microtime(true) - $start) * 1000, 2)." msec]. ";
      } catch (\Exception $e) {
        echo ", <span style='color:red'>Failed. Reason: {$e->getMessage()}.</span> ";
      } catch (\Illuminate\Database\QueryException $e) {
        //
      } catch (\ADIOS\Core\DBException $e) {
        //
      }
    }

    echo "<h2>Installing widgets</h2>";

    foreach ($this->widgets as $widget) {
      try {
        echo "<b>{$widget->name}</b>";

        if ($widget->install()) {
          $this->widgetsInstalled[$widget->name] = TRUE;
          echo ". ";
        } else {
          echo ", <span style='color:orange'>skipped.</span> ";
        }
      } catch (\Exception $e) {
        echo ", <span style='color:red'>failed.</span> ";
      } catch (\ADIOS\Core\DBException $e) {
        // Moze sa stat, ze vytvorenie tabulky zlyha napr. kvoli
        // "Cannot add or update a child row: a foreign key constraint fails".
        // V takom pripade budem instalaciu opakovat v dalsom kole
      }

      $this->dispatchEventToPlugins("onWidgetAfterInstall", [
        "widget" => $widget,
      ]);
    }

    $this->db->commit();

    echo "<h2>Done</h2>";
    echo "Installation time: ".round((microtime(true) - $installationStart), 2)." s";

    if (count($this->console->getLogs()) > 0) {
      echo "<h2>Errors</h2>";
      echo "<div style='color:red'>".nl2br($this->console->getContents())."</div>";
    }
  }



  // funkcia render() zabezpecuje:
  //   - routing podla a) (ne)prihlaseny user, b) $this->requestedAction, c) $_REQUEST['__IS_AJAX__']
  //   - kontrolu requestu podla $_REQUEST['c']
  //   - vygenerovanie UID
  //   - renderovanie naroutovanej akcie

  public function render($params = []) {
    if (preg_match('/(\w+)\/Cron\/(\w+)/', $this->requestedURI, $m)) {
      $cronClassName = str_replace("/", "\\", "/ADIOS/Widgets/{$m[0]}");

      if (class_exists($cronClassName)) {
        (new $cronClassName($this))->run();
      } else {
        echo "Unknown cron '{$this->requestedURI}'.";
      }

      exit();
    }

    try {

      // cache vytvaram az v tomto momente, t.j. iba pri F5 refresh
      // aby sa pri kazdom AJAX requeste zbytocne nevytvarala
      // $this->rebuildCache();

      if (php_sapi_name() === 'cli') {
        $params = @json_decode($_SERVER['argv'][2] ?? "", TRUE);
        $params['action'] = $_SERVER['argv'][1] ?? "";
      } else {
        $params = $_REQUEST;
      }

      if (!empty($params['action'])) {
        // prejdem routovaciu tabulku, ak najdem prislusny zaznam, nastavim action a params
        
        foreach ($this->routing as $routePattern => $routeParams) {
          if (preg_match($routePattern, $params['action'], $m)) {
            $params['action'] = $routeParams['action'];

            if (is_array($routeParams['params'])) {
              foreach ($routeParams['params'] as $k1 => $v1) {
                foreach ($m as $k2 => $v2) {
                  $routeParams['params'] = str_replace('$'.$k2, $v2, $routeParams['params']);
                }
              }

              foreach ($routeParams['params'] as $k => $v) {
                $params[$k] = $v;
              }
            }
          }
        }
      }

      if (empty($this->action)) {
        if (empty($params['action'])) {
          $this->action = (php_sapi_name() === 'cli' ? "" : $this->config['default_action']);
        } else {
          $this->action = $params['action'];
        }
      }

      $this->dispatchEventToPlugins("onADIOSBeforeActionRender", ["adios" => $this]);

      if (empty($this->action)) {
        throw new \ADIOS\Core\Exception("No action specified.");
      }

      $actionClassName = "ADIOS\\Actions\\".str_replace("/", "\\", $this->action);

      if (php_sapi_name() === 'cli') {
        if (!$actionClassName::$cliSAPIEnabled) {
          throw new \ADIOS\Core\Exception("Action is not available for CLI interface.");
        }
      } else {
        if (!$actionClassName::$webSAPIEnabled) {
          throw new \ADIOS\Core\Exception("Action is not available for WEB interface.");
        }
      }

      // mam moznost upravit config (napr. na skrytie desktopu)
      $this->config = $actionClassName::overrideConfig($this->config);

      if ($params['__IS_AJAX__']) {
        // tak nic
      } else if (!$actionClassName::$hideDefaultDesktop) {
        // treba nacitat cely desktop, ak to nie je zakazane v akcii
        $this->desktopContentAction = $this->action;
        $this->desktopContentActionParams = $params;
        $this->action = "Desktop";
      }

      if (
        !$this->userLogged
        && $actionClassName::$requiresUserAuthentication
      ) {
        $this->action = "Login";
      }

      if (empty($this->action)) {
        $this->action = "Desktop";
      }

      // kontrola vstupov podla kontrolneho kodu "_"
      // vypnute, lebo JS btoa() pri niektorych znakoch nefunguje
      if (FALSE && $params['__IS_RENDERED_ON_DESKTOP__'] && count($params) > 0) {
        $tmp_params = $params;
        unset($tmp_params['__C__']);
        unset($tmp_params['action']);

        if (empty($params['__C__'])) {
          return "INPUT_VALIDATION_CODE_EMPTY";
        } else {
          $check_code = base64_encode(json_encode($tmp_params));
          if ($check_code != $params['__C__']) {
            return "INPUT_VALIDATION_FAILED";
          }
        }
      }

      // vygenerovanie UID tohto behu
      if (empty($this->uid)) {
        $uid = $this->getUid($params['id']);
      } else {
        $uid = $this->uid.'__'.$this->getUid($params['id']);
      }

      $this->setUid($uid);

      return $this->renderAction($this->action, $params);

    } catch (\ADIOS\Core\Exception $e) {
      exit("ADIOS RUN failed: ".$e->getMessage());
    }
  }

  public function getActionClassName($action) {
    return "ADIOS\\Actions\\".str_replace("/", "\\", $action);
  }

  public function actionExists($action) {
    return class_exists($this->getActionClassName($action));
  }

  // funkcia renderAction() zabezpecuje:
  //   - kontrolu pravomoci, ci moze logged user akciu spustit
  //   - vyrenderovanie akcie alebo, ak neexistuje, vyrenderovanie twig template

  public function renderAction($action, $params) {
    if (!is_array($params)) $params = [];

    $params['_REQUEST'] = $params;
    $params['_COOKIE'] = $_COOKIE;
    $this->action = $action;

    if (in_array($action, array_keys($this->config['widgets']))) {
      $action = "{$action}/Main";
    }

    $this->actionNestingLevel++;
    $this->actionStack[] = $action;

    $actionClassName = $this->getActionClassName($action);

    try {
      $this->checkPermissionsForAction($action, $params);

      if ($this->actionExists($action)) {
        $actionReturn = (new $actionClassName($this, $params))->render($params);

        if ($actionReturn === NULL) {
          // akcia nic nereturnovala, iba robila echo
          $actionHtml = "";
        } else if (is_string($actionReturn)) {
          $actionHtml = $actionReturn;
        } else {
          $actionHtml = $this->renderReturn($actionReturn);
        }
      } else {

        // ak sa nepodari najst classu, tak skusim aspon vyrenderovat template
        $tmpTemplateName = $actionClassName;
        $tmpTemplateName = str_replace("\\", "/", $tmpTemplateName);
        $tmpTemplateName = str_replace("/Actions/", "/Templates/", $tmpTemplateName);

        $tmp = new \ADIOS\Core\Action($this);
        $tmp->twigTemplate = $tmpTemplateName;
        $actionHtml = $tmp->render($params);
      }

    } catch (
      \Illuminate\Database\QueryException
      | \ADIOS\Core\DBException
      $e
    ) {
      $errorMessage = $e->getMessage();
      $errorHash = md5(date("YmdHis").$errorMessage);
      $this->console->log($errorHash, "{$errorMessage}\t{$this->db->last_query}\t{$this->db->db_error}");
      $actionHtml = $this->renderHtmlWarning("
        <div style='text-align:center;font-size:5em;color:red'>
          🥴
        </div>
        <div style='margin-top:1em;margin-bottom:1em;'>
          Oops! Something went wrong with the database.
          See logs for more information or contact the support.<br/>
        </div>
        <div style='color:red;margin-bottom:1em;white-space:pre;font-family:courier;font-size:0.8em;overflow:auto;'>{$errorMessage}</div>
        <div style='color:gray'>
          {$errorHash}
        </div>
      ");
    } catch (\ADIOS\Core\NotEnoughPermissionsException $e) {
      $actionHtml = $this->renderWarning($e->getMessage());
    } catch (\Exception $e) {
      $actionHtml = $this->renderHtmlWarning("
        <div style='text-align:center;font-size:5em;color:red'>
          🥴
        </div>
        <div style='margin-top:1em;margin-bottom:1em;'>
          Oops! Something went wrong.
          See logs for more information or contact the support.<br/>
        </div>
        <div style='color:red;margin-bottom:1em;white-space:pre;font-family:courier;font-size:0.8em;overflow:auto;'>".$e->getMessage()."</div>
        <div style='color:gray'>
          ".get_class($e)."
        </div>
      ");
    }

    return $actionHtml;
  }

  /**
   * This method is used to check permissions before rendering
   * an action. The method should be overriden.
   * 
   * @param string $action Name of the action to be rendered.
   * @param array $params Parameters of the action.
   * 
   * throws \ADIOS\Core\NotEnoughPermissionsException
   */
  public function checkPermissionsForAction($action, $params) {
    // to be overriden
  }

  public function renderReturn($return) {
    if ($this->isAjax()) {
      return json_encode([
        "result" => "SUCCESS",
        "content" => $return,
      ]);
    } else {
      return $return;
    }
  }

  public function renderWarning($warning, $isHtml = TRUE) {
    if ($this->isAjax()) {
      return json_encode([
        "result" => "WARNING",
        "content" => $warning,
      ]);
    } else {
      return "
        <div class='adios_warning shadow-lg p-3 mb-5' onclick='$(this).remove();'>
          ".($isHtml ? $warning : hsc($warning))."
        </div>
      ";
    }
  }

  public function renderHtmlWarning($warning) {
    return $this->renderWarning($warning, TRUE);
  }

  public function dispatchEventToPlugins($event, $eventData = []) {
    foreach ($this->pluginObjects as $plugin) {
      if (method_exists($plugin, $event)) {
        $eventData = $plugin->$event($eventData);
        if (!is_array($eventData) && $eventData !== FALSE) {
          throw new \ADIOS\Core\Exception("Plugin {$plugin->name}, event {$event}: No value returned. Either forward \$event or return FALSE.");
        }

        if ($eventData === FALSE) {
          break;
        }
      }
    }
    return $eventData;
  }

  public function hasPermissionForAction($action, $params) {
    return TRUE;
  }

  ////////////////////////////////////////////////
  // metody pre pracu s konfiguraciou

  public function getConfig($path, $default = NULL) {
    $retval = $this->config;
    foreach (explode('/', $path) as $key => $value) {
      if (isset($retval[$value])) {
        $retval = $retval[$value];
      } else {
        $retval = null;
      }
    }

    return ($retval === NULL ? $default : $retval);
  }

  public function setConfig($path, $value) {
    $path_array = explode('/', $path);

    $cfg = &$this->config;
    foreach ($path_array as $path_level => $path_slice) {
      if ($path_level == count($path_array) - 1) {
        $cfg[$path_slice] = $value;
      } else {
        if (empty($cfg[$path_slice])) {
          $cfg[$path_slice] = NULL;
        }
        $cfg = &$cfg[$path_slice];
      }
    }
  }

  // TODO: toto treba prekontrolovat, velmi pravdepodobne to nefunguje
  // public function mergeConfig($config_to_merge) {
  //   if (is_array($config_to_merge)) {
  //     foreach ($config_to_merge as $key => $value) {
  //       if (is_array($value)) {
  //         $this->config[$key] = $this->mergeConfig($config_original[$key], $value);
  //       } else {
  //         $this->config[$key] = $value;
  //       }
  //     }
  //   }

  //   return $this->config;
  // }

  public function saveConfig($config, $path = '') {
    if (is_array($config)) {
      foreach ($config as $key => $value) {
        $tmpPath = $path.$key;

        if (is_array($value)) {
          $this->saveConfig($value, $tmpPath.'/');
        } else if ($value === NULL) {
          $this->db->query("
            delete from `{$this->gtp}_{$this->config['system_table_prefix']}_config`
            where `path` like '".$this->db->escape($tmpPath)."%'
          ");
        } else {
          $this->db->query("
            insert into `{$this->gtp}_{$this->config['system_table_prefix']}_config` set
              `path` = '".$this->db->escape($tmpPath)."',
              `value` = '".$this->db->escape($value)."'
            on duplicate key update
              `path` = '".$this->db->escape($tmpPath)."',
              `value` = '".$this->db->escape($value)."'
          ");
        }
      }
    }
  }

  public function deleteConfig($path) {
    if (!empty($path)) {
      $this->db->query("
        delete from `{$this->gtp}_{$this->config['system_table_prefix']}_config`
        where `path` like '".$this->db->escape($path)."%'
      ");
    }
  }

  public function loadConfigFromDB() {
    try {
      $this->db->query("
        select
          *
        from `{$this->gtp}_{$this->config['system_table_prefix']}_config`
        order by id asc
      ");

      while ($row = $this->db->db_result->fetch_array(MYSQLI_ASSOC)) {
        $tmp = &$this->config;
        foreach (explode("/", $row['path']) as $tmp_path) {
          if (!is_array($tmp[$tmp_path])) {
            $tmp[$tmp_path] = [];
          }
          $tmp = &$tmp[$tmp_path];
        }
        $tmp = $row['value'];
      }
    } catch (\Exception $e) {
      // do nothing
    }
  }

  public function finalizeConfig() {
    // various default values
    $this->config['widgets'] = $this->config['widgets'] ?? [];
    $this->config['protocol'] = (strtoupper($_SERVER['HTTPS'] ?? "") == "ON" ? "https" : "http");
    $this->config['timezone'] = $this->config['timezone'] ?? 'Europe/Bratislava';
    $this->config['language'] = $this->config['language'] ?? (is_array($this->config['available_languages'])
      ? reset($this->config['available_languages'])
      : "sk"
    );

    $this->config['files_dir'] = $this->config['files_dir'] ?? "{$this->config['dir']}/upload";
    $this->config['files_url'] = $this->config['files_url'] ?? "{$this->config['url']}/upload";

    $this->config['upload_dir'] = $this->config['files_dir'];
    $this->config['upload_url'] = $this->config['files_url'];

    $this->config['files_dir'] = str_replace("\\", "/", $this->config['files_dir']);

  }

  public function onConfigLoaded() {
    // to be overriden
  }

  public function onWidgetsLoaded() {
    // to be overriden
  }

  public function onPluginsLoaded() {
    // to be overriden
  }

  public function onModelsLoaded() {
    // to be overriden
  }

  ////////////////////////////////////////////////



  public function getUid($uid = '') {
    if (empty($uid)) {
      $tmp = $this->action.'-'.time().rand(100000, 999999);
    } else {
      $tmp = $uid;
    }

    $tmp = str_replace('/', '-', $tmp);

    $uid = "";
    for ($i = 0; $i < strlen($tmp); $i++) {
      if ($tmp[$i] == "-") {
        $uid .= strtoupper($tmp[++$i]);
      } else {
        $uid .= $tmp[$i];
      }
    }

    $this->setUid($uid);

    return $uid;
  }

  public function checkUid($uid) {
    return !preg_match('/[^A-Za-z0-9\-_]/', $uid);
  }

  public function setUid($uid) {
    if (!$this->checkUid($uid)) {
      exit('Invalid UID');
    }

    $this->uid = $uid;
  }

  public function authCookieSerialize($login, $password) {
    return md5($login.".".$password).",".$login;
  }

  public function authCookieGetLogin() {
    list($tmpHash, $tmpLogin) = explode(",", $_COOKIE[_ADIOS_ID.'-user']);
    return $tmpLogin;
  }

  public function authUser($login, $password, $rememberLogin = FALSE) {
    $this->userProfile = null;
    $login = trim($login);

    if (empty($login) && !empty($_COOKIE[_ADIOS_ID.'-user'])) {
      $login = $this->authCookieGetLogin();
    }

    if (!empty($login)) {
      $this->db->query("
        select
          *
        from {$this->gtp}_{$this->config['system_table_prefix']}_users
        where
          (
            `login`= '".$this->db->escape($login)."'
            or `email`= '".$this->db->escape($login)."'
          )
          and `active` <> 0
      ");

      while ($data = $this->db->fetch_array()) {
        $passwordMatch = FALSE;

        if (!empty($password) && $data['password'] == $password) {
          // plain text
          $passwordMatch = TRUE;
        } else if (!empty($password) && password_verify($password, $data['password'])) {
          // plain text
          $passwordMatch = TRUE;
        } else if ($_COOKIE[_ADIOS_ID.'-user'] == $this->authCookieSerialize($data['login'], $data['password'])) {
          $passwordMatch = TRUE;
        }

        if ($passwordMatch) {
          $this->userProfile = $data;
          $this->userLogged = TRUE;

          $_SESSION[_ADIOS_ID]['userProfile'] = $this->userProfile;

          if ($rememberLogin) {
            setcookie(
              _ADIOS_ID.'-user',
              $this->authCookieSerialize($data['login'], $data['password']),
              time() + (3600 * 24 * 30)
            );
          }

          return TRUE;
        }
      }
    }

    return FALSE;
  }

  public function generate_rc_perms($perms) { }
  public function has_perms($perm) { return TRUE; }
  public function action_perms($action) { return TRUE; }
  public function db_perms($action) { return TRUE; }
  public function feature_perms($action) { return TRUE; }
  public function table_has_cols_perms($table_name, $operation) { return TRUE; }



  public function renderCSSCache() {
    $css = "";
    
    $cssFiles = [
      dirname(__FILE__)."/../Assets/Css/fontawesome-5.13.0.css",
      dirname(__FILE__)."/../Assets/Css/bootstrap.min.css",
      dirname(__FILE__)."/../Assets/Css/sb-admin-2.css",
      dirname(__FILE__)."/../Assets/Css/responsive.css",
      dirname(__FILE__)."/../Assets/Css/colors.css",
      dirname(__FILE__)."/../Assets/Css/desktop.css",
      dirname(__FILE__)."/../Assets/Css/jquery-ui.css",
      dirname(__FILE__)."/../Assets/Css/jquery-ui.structure.css",
      dirname(__FILE__)."/../Assets/Css/jquery-ui-fontawesome.css",
      dirname(__FILE__)."/../Assets/Css/jquery.window.css",
      dirname(__FILE__)."/../Assets/Css/adios_classes.css",
      dirname(__FILE__)."/../Assets/Css/quill-1.3.6.core.css",
      dirname(__FILE__)."/../Assets/Css/quill-1.3.6.snow.css",
    ];

    foreach (scandir(dirname(__FILE__).'/../Assets/Css/Ui') as $file) {
      if ('.css' == substr($file, -4)) {
        $cssFiles[] = dirname(__FILE__)."/../Assets/Css/Ui/{$file}";
      }
    }

    foreach (scandir(ADIOS_WIDGETS_DIR) as $widget) {
      if (!in_array($widget, [".", ".."]) && is_file(ADIOS_WIDGETS_DIR."/{$widget}/Main.css")) {
        $cssFiles[] = ADIOS_WIDGETS_DIR."/{$widget}/Main.css";
      }
    }

    foreach ($cssFiles as $file) {
      $css .= @file_get_contents($file)."\n";
    }

    return $css;

  }

  public function renderJSCache() {
    $js = "";

    $jsFiles = [
      "jquery-3.5.1.js",
      "jquery-ui.1.11.4.min.js",
      "jquery.scrollTo.min.js",
      "jquery.window.js",
      "jquery-ui-touch-punch.js",
      "md5.js",
      "base64.js",
      "cookie.js",
      "keyboard_shortcuts.js",
      "json.js",
      "moment.min.js",
      "chart.min.js",
      "desktop.js",
      "ajax_functions.js",
      "adios.js",
      "quill-1.3.6.min.js",
      "bootstrap.bundle.js",
      "jquery.easing.js",
      "sb-admin-2.js",
      "jsoneditor.js",
    ];
    foreach (scandir(dirname(__FILE__).'/../Assets/Js/Ui') as $file) {
      if ('.js' == substr($file, -3)) {
        $jsFiles[] = "Ui/{$file}";
      }
    }

    foreach ($jsFiles as $file) {
      $js .= @file_get_contents(dirname(__FILE__)."/../Assets/Js/{$file}")."\n";
    }

    return $js;

  }

}
