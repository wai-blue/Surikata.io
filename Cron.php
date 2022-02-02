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

  if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
  }

  if (empty($argv[1])) {
    throw new Exception('Usage: php.exe Cron.php <CRON_ACTION>');
  }

  require(__DIR__."/Init.php");

  $web = new \MyEcommerceProject\Web($websiteRendererConfig);
  
  echo (
    new \MyEcommerceProject\AdminPanel(
      $adminPanelConfig + ['default_action' => ($argv[1] ?? "")],
      ADIOS_MODE_FULL,
      $web
    )
  )->render();

} catch (\Exception $e) {
  echo $e->getMessage();
}