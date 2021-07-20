<?php

try {

  require("../Init.php");

  $web = new \MyOnlineStore\Web($websiteRendererConfig);
  
  echo (
    new \MyOnlineStore\AdminPanel(
      $adminPanelConfig,
      ADIOS_MODE_FULL,
      $web
    )
  )->render();

} catch (\Exception $e) {
  echo $e->getMessage();
}