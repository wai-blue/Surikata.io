<?php

namespace CASCADA;

class Loader {
  var $controllers = [];
  var $router = NULL;

  var $config = [];
  var $rootDir = "";
  var $rewriteBase = "";
  var $pageUrl = "";
  var $rootUrl = "";
  var $relativeUrl = "";
  var $language = "";
  var $themeDir = "";
  var $template = "";
  var $theme = "";
  var $twig = NULL;
  var $twigTemplatesSubDir = "";
  var $twigCacheDir = FALSE;
  var $JSONResult = NULL;
  var $urlVariables = [];
  var $assetsUrlMap = [];

  private $canContinueWithRendering = TRUE;

  function __construct($config) {

    $this->setGlobal();

    $this->config = $config;
    $this->rootDir = $config["rootDir"];
    $this->rewriteBase = $config["rewriteBase"];
    $this->twigCacheDir = $config['twigCacheDir'] ?? FALSE;
    $this->relativeUrl = $config['relativeUrl'] ?? "";
    $this->themeDir = $config['themeDir'] ?? "";
    $this->twigTemplatesSubDir = $config["twigTemplatesSubDir"] ?? "Templates";

    // extract pageUrl
    if ($this->rewriteBase == "/") {
      $this->pageUrl = $_SERVER['REQUEST_URI'];
    } elseif (strlen($this->rewriteBase) > strlen($_SERVER['REQUEST_URI'])) {
      $this->pageUrl = "";
    } else {
      $this->pageUrl = str_replace(rtrim($this->rewriteBase, "/"), "", $_SERVER['REQUEST_URI']);
    }

    if (strpos($_SERVER['REQUEST_URI'], "?") !== FALSE) {
      $this->pageUrl = 
        substr($this->pageUrl, 0,
          strpos($this->pageUrl, "?")
        )
      ;
    }

    $this->pageUrl = trim($this->pageUrl, "/");

    // calculate rootUrl
    $this->rootUrl = trim("./".str_repeat("../", substr_count($this->pageUrl, "/")), "/");

    if ($this->themeDir != "") {
      $this->assetsUrlMap["theme/assets/"] = "{$this->themeDir}/Assets/";
      $this->initTwig();
    }

    // connect, if connection info provided
    if (!empty($config['connection'])) {
      $capsule = new \Illuminate\Database\Capsule\Manager;
      $capsule->addConnection($config["connection"]);
      $capsule->setAsGlobal();
      $capsule->bootEloquent();
    }
  }

  public function setGlobal() {
    global $___CASCADAObject;
    $___CASCADAObject = $this;
    return $this;
  }

  public function initTwig() {
    if ($this->themeDir == "") {
      $this->themeDir = "{$this->rootDir}/theme";
    }

    // initialize twig
    $twigParams = [];
    if (is_string($this->twigCacheDir)) {
      $twigParams['cache'] = $this->twigCacheDir;
    } else {
      $twigParams['cache'] = FALSE;
    }
    
    $twigParams['debug'] = $this->config['twigDebugEnabled'] ?? FALSE;

    $twigLoader = new \Twig\Loader\FilesystemLoader($this->themeDir);
    $this->twig = new \Twig\Environment($twigLoader, $twigParams);
    $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    $this->twig->addExtension(new \Twig\Extension\StringLoaderExtension());

    // set default twig params
    $this->setTwigParams([
      "rootUrl" => $this->rootUrl,
      "pageUrl" => $this->pageUrl,
      "rewriteBase" => $this->rewriteBase,
      "urlVariables" => $this->urlVariables ?? [],
      "template" => $this->template ?? "",
      "cascadaInitJS" => "
          <script>
              Cascada = {
                  'rootUrl': '{$this->rootUrl}',
              }
          </script>
      ",
      "_GET" => $_GET,
      "_POST" => $_POST,
    ]);

    return $this;

  }

  function setRouter($router) {
    $this->router = $router;
    $this->router->cascada = &$this;

    // perform redirects, if any
    $this->router->performRedirects();

    return $this;
  }

  // function rebuildHTAccess($htaccessFilename = "") {
  //   $this->router->rebuildHTAccess($htaccessFilename);
  //   return $this;
  // }

  function addController($controller) {
    $this->controllers[] = $controller;
    return $this;
  }

  function addControllers($controllers) {
    if (is_array($controllers)) {
      foreach ($controllers as $controller) {
        $this->controllers[] = $controller;
      }
    }
    
    return $this;
  }

  function addControllersByName($controllerNames) {
    if (is_array($controllerNames)) {
      foreach ($controllerNames as $controllerName) {
        $this->controllers[] = new $controllerName($this);
      }
    }

    return $this;
  }

  function setTwigParam($key, $value) {
    $this->twigParams[$key] = $value;

    return $this;
  }

  function setTwigParams($params) {
    if (is_array($params)) {
      foreach ($params as $key => $value) {
        $this->setTwigParam($key, $value);
      }
    }

    return $this;
  }

  function setJSONResult($result) {
    $this->JSONResult = $result;
  }

  function redirectTo($pageUrl, $redirectType = NULL) {
    if ($redirectType == 301) {
      header("HTTP/1.1 301 Moved Permanently"); 
    }

    header("Location: {$this->rewriteBase}{$pageUrl}");

    exit();
  }

  public function cancelRendering() {
    $this->canContinueWithRendering = FALSE;
  }

  public function render() {
    // get template name
    $this->template = $this->router->getCurrentPageTemplate();

    // check if CSS, JS or Image should be rendered
    foreach ($this->assetsUrlMap as $urlPart => $mapping) {
      if (preg_match('/^'.str_replace("/", "\\/", $urlPart).'/', $this->template, $m)) {
        if ($mapping instanceof \Closure) {
          $sourceFile = $mapping($this, $this->template);
        } else {
          $sourceFile = $mapping.str_replace($urlPart, "", $this->template);
        }

        $ext = strtolower(pathinfo($this->template, PATHINFO_EXTENSION));

        $cachingTime = 3600;
        $headerExpires = "Expires: ".gmdate("D, d M Y H:i:s", time() + $cachingTime) . " GMT";
        $headerCacheControl = "Cache-Control: max-age={$cachingTime}";

        switch ($ext) {
          case "css":
          case "js":
            header("Content-type: text/{$ext}");
            header($headerExpires);
            header("Pragma: cache");
            header($headerCacheControl);
            echo file_get_contents($sourceFile);
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
            echo file_get_contents($sourceFile);
          break;
        }

        exit();

      }
    }

    // validate template name
    if (
      strpos($this->template, "..\\") !== FALSE
      || strpos($this->template, "../") !== FALSE
    ) {
      throw new \Exception("CASCADA: Invalid template name {$this->template}.");
    }

    // ak nie su ziadne kontrolery, pokusim sa ich pridat sam
    if (empty($this->controllers)) {
      $this->addControllers($this->router->getCurrentPageControllers());
    }

    // vyparsujem url variables a doplnim ich o _GET hodnoty
    $this->urlVariables = $this->router->getCurrentPageUrlVariables();
    foreach ($_GET as $key => $value) {
      $this->urlVariables[$key] = $value;
    }

    // overridnem twigParams tym, co je nastavene v siteMap
    $this->twigParams = array_merge(
      $this->twigParams,
      $this->router->getCurrentPageTemplateVariables()
    );

    // assume that the preRender does not block further rendering
    $this->canContinueWithRendering = TRUE;

    // pre render
    foreach ($this->controllers as $controller) {
      $controller->preRender();

      $render = $controller->render();
      if (is_string($render)) {
        $this->outputHtml = $render;
        $this->canContinueWithRendering = FALSE;
      }

      if (!$this->canContinueWithRendering) {
        break;
      }
    }

    // render
    if ($this->canContinueWithRendering) {
      if ($this->JSONResult === NULL) {
        $templateFile = "{$this->twigTemplatesSubDir}/{$this->template}.twig";
        if (is_file("{$this->themeDir}/{$templateFile}")) {
          $this->outputHtml = $this->twig->render(
            $templateFile,
            $this->twigParams
          );
        } else {
          // vyskusam este 404 not found template
          $templateFile = "{$this->twigTemplatesSubDir}/{$this->router->getNotFoundTemplate()}.twig";
          if (is_file("{$this->themeDir}/{$templateFile}")) {
            $this->outputHtml = $this->twig->render(
              $templateFile,
              $this->twigParams
            );
          } else {
            throw new \Exception("CASCADA: Template {$this->template} not found.");
          }
        }
      } else {
        $this->outputHtml = @json_encode($this->JSONResult);
      }

      // post render
      foreach ($this->controllers as $controller) {
        $controller->postRender();
      }
    }

    // return
    return $this->outputHtml;
  }




  /// MISCELANEOUS HELPER FUNCTIONS
  function rmspecialchars($string) {
    $from = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '{', '}', '[', ']', ':', '|', ';', "'", '\\', ',', '.', '/', '<', '>', '?'];
    foreach ($from as $char) {
      $string = str_replace($char, '', $string);
    }

    return $string;
  }

  function rmdiacritic($string) {
    $from = ['ŕ', 'ě', 'š', 'č', 'ř', 'š', 'ž', 'ť', 'ď', 'ľ', 'ĺ', 'ý', 'á', 'í', 'ä', 'é', 'ú', 'ü', 'ö', 'ô', 'ó', 'ň', 'Ě', 'Š', 'Č', 'Ř', 'Š', 'Ť', 'Ď', 'Ľ', 'Ĺ', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ü', 'Ó', 'Ó', 'Ň'];
    $to = ['r', 'e', 's', 'c', 'r', 's', 'z', 't', 'd', 'l', 'l', 'y', 'a', 'i', 'a', 'e', 'u', 'u', 'o', 'o', 'o', 'n', 'E', 'S', 'C', 'R', 'S', 'T', 'D', 'L', 'L', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U', 'O', 'O', 'N'];

    return str_replace($from, $to, $string);
  }

  function str2url($string, $replace_slashes = true) {
    if ($replace_slashes) {
      $string = str_replace('/', '-', $string);
    }

    $string = preg_replace('/ |^(a-z0-9)/', '-', strtolower($this->rmspecialchars($this->rmdiacritic($string))));

    $string = preg_replace('/[^(\x20-\x7F)]*/', '', $string);
    $string = preg_replace('/[^(\-a-z0-9)]*/', '', $string);
    $string = trim($string, '-');

    while (strpos($string, '--')) {
      $string = str_replace('--', '-', $string);
    }

    return $string;
  }

}

