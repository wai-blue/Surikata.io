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

  $arguments = getopt(
    "l",
    ["list"],
    $restIndex
  );

  $actionToRun = $argv[$restIndex] ?? "";
  $actionToRunRequired = TRUE;

  if (isset($arguments["l"]) || isset($arguments["list"])) {
    $actionToRunRequired = FALSE;
  }

  if (empty($actionToRun) && $actionToRunRequired) {
    $usage = "";
    $usage .= "Surikata.io Cron runner.\r\n";
    $usage .= "\r\n";
    $usage .= "Usage: php.exe Cron.php [options] <CronActionToRun>\r\n";
    $usage .= "\r\n";
    $usage .= "Options:\r\n";
    $usage .= "  -l, --list     List all avaliable cron actions. If this option is present, CronActionToRun is ignored.\r\n";
    $usage .= "\r\n";

    throw new Exception($usage);
  }

  require(__DIR__."/Init.php");

  $web = new \MyEcommerceProject\Web($websiteRendererConfig);
  $adminPanel = new \MyEcommerceProject\AdminPanel(
    $adminPanelConfig + ['default_action' => $actionToRun],
    ADIOS_MODE_FULL,
    $web
  );

  if (isset($arguments["l"]) || isset($arguments["list"])) {
    var_dump($adminPanel->actions);
    exit;
  }

  if (!empty($actionToRun)) {
    echo $adminPanel->render();
  }

} catch (\Exception $e) {
  echo $e->getMessage();
}