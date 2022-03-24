<?php

/*
 * DO NOT CHANGE THIS FILE UNLESS YOU WANT TO CONTRIBUTE TO
 * Surikata.io's CORE.
 *
 * IF YOU WANT TO BUILD YOUR ECOMMERCE PROJECT, START IN
 * THE prop/ FOLDER.
 *
 * Author: https://www.wai.blue
 */

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

  $adminPanel = new \MyEcommerceProject\AdminPanel(
    $adminPanelConfig,
    \ADIOS\Core\Loader::ADIOS_MODE_LITE,
  );
  
  echo
    (new \MyEcommerceProject\Web(
      $websiteRendererConfig,
      $adminPanel
    ))->render()
  ;


} catch (\Exception $e) {
  echo $e->getMessage();
}