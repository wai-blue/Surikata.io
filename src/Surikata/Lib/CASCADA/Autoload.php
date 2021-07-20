<?php

  // my own autoload
  spl_autoload_register(function($className) {
    global $___CASCADAObject;

    $className = str_replace("\\", "/", $className);

    $rootNamespace = substr($className, 0, strpos($className, "/"));
    $restNamespace = substr($className, strpos($className, "/") + 1);

    switch ($rootNamespace) {
      case "Cascada": 
        include(dirname(__FILE__)."/src/{$restNamespace}.php");
      break;
      case "WEB":
        if (!is_object($___CASCADAObject)) return;
        if (empty($___CASCADAObject->rootDir) || !@include("{$___CASCADAObject->rootDir}/{$restNamespace}.php")) {
          include("{$___CASCADAObject->themeDir}/{$restNamespace}.php");
        }
      break;
    }
    
  });
