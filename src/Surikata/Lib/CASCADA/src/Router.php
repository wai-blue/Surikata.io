<?php

namespace CASCADA;

class Router {
  var $routingTable = [];
  var $cascada;

  function __construct($routingTable = NULL) {
    if ($routingTable !== NULL) {
      $this->setRoutingTable($routingTable);
    }
  }

  function setRoutingTable($routingTable) {
    $this->routingTable = $routingTable;
    return $this;
  }

  function getCurrentPageRoutes() {
    $routes = [];

    foreach ($this->routingTable as $route => $params) {
      if (
        ($route == "*"
          || $route == $this->cascada->pageUrl
          || preg_match("/^".str_replace("/", "\\/", trim($route, "/"))."$/", $this->cascada->pageUrl)
        )
      ) {
        $routes[$route] = $params;
      }
    }

    return $routes;
  }

  function getCurrentPageControllers() {
    $controllers = [];
    $routes = $this->getCurrentPageRoutes();
    
    foreach ($routes as $route => $params) {
      if (!empty($params["controllers"])) {
        foreach ($params["controllers"] as $controller) {
          $controllers[] = $controller;
        }
      }
    }

    return $controllers;
  }

  function getCurrentPageUrlVariables() {
    $urlVariables = [];
    $routes = $this->getCurrentPageRoutes();

    foreach ($routes as $route => $params) {
      if (isset($params['urlVariables']) && is_array($params['urlVariables'])) {
        if (preg_match("/^".str_replace("/", "\\/", trim($route, "/"))."$/", $this->cascada->pageUrl, $m)) {
          $tmpUrlVariables = $params['urlVariables'];
          foreach ($tmpUrlVariables as $varIndex => $varName) {
            if (isset($m[$varIndex])) {
              $urlVariables[$varName] = $m[$varIndex];
              unset($params['urlVariables'][$varIndex]);
            }
          }

          $urlVariables = array_merge($urlVariables, $params['urlVariables']);
        }
      }
    }

    return $urlVariables;
  }

  function getCurrentPageTemplateVariables() {
    $templateVariables = [];
    $routes = $this->getCurrentPageRoutes();

    foreach ($routes as $route => $params) {
      if (
        !empty($params["templateVariables"])
        && is_array($params["templateVariables"])
      ) {
        $templateVariables = array_merge(
          $templateVariables,
          $params["templateVariables"]
        );
      }
    }

    return $templateVariables;
  }

  function getCurrentPageTemplate() {
    $template = "";
    $routes = $this->getCurrentPageRoutes();

    foreach ($routes as $route => $params) {
      if (!empty($params["template"])) {
        $template = $params["template"];
      }
    }

    if ($template == "") $template = $this->cascada->pageUrl;

    return $template;
  }

  function getNotFoundTemplate() {
    return $this->routingTable["NotFoundTemplate"] ?? "";
  }

  function performRedirects() {
    $routes = $this->getCurrentPageRoutes();

    foreach ($routes as $route) {
      if (!empty($route['redirect'])) {
        switch ($route['redirect'][1]) {
          case 301:
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: {$route['redirect'][0]}");
            exit();
          break;
        }
      }
    }
  }

}