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

  require(__DIR__."/../Init.php");

  $web = new \MyEcommerceProject\Web($websiteRendererConfig);
  
  echo (
    new \MyEcommerceProject\AdminPanel(
      $adminPanelConfig,
      \ADIOS\Core\Loader::ADIOS_MODE_FULL,
      $web
    )
  )->render();

} catch (\Exception $e) {
  echo $e->getMessage();
}