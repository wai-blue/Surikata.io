<?php

try {

  require_once("Init.php");

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