<?php

try {

  require("../Init.php");

  $web = new \MyOnlineStore\Web($websiteRendererConfig);
  
  echo (
    new \MyOnlineStore\AdminPanel(
      $adminPanelConfig + ['default_action' => ($argv[1] ?? "")],
      ADIOS_MODE_FULL,
      $web
    )
  )->render();

} catch (\Exception $e) {
  echo $e->getMessage();
}