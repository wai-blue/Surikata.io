<?php

try {

  require_once("Init.php");

  // Request URI sanitization (a.k.a redirects)
  // Implementing redirects here is web-server independent

  if ($_SERVER['REQUEST_URI'] !== REWRITE_BASE) {
    if (substr($_SERVER['REQUEST_URI'], -1) == "/") {
      header("Location: /".trim($_SERVER['REQUEST_URI'], "/"), TRUE, 302);
      exit();
    }
  }

  //

  $adminPanel = new \MyOnlineStore\AdminPanel(
    $adminPanelConfig,
    ADIOS_MODE_LITE
  );
  
  echo
    (new \MyOnlineStore\Web(
      $websiteRendererConfig,
      $adminPanel
    ))->render()
  ;


} catch (\Exception $e) {
  echo $e->getMessage();
}