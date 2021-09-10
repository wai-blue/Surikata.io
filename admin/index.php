<?php

try {

  require("../Init.php");

  $web = new \MyEcommerceProject\Web($websiteRendererConfig);
  
  echo (
    new \MyEcommerceProject\AdminPanel(
      $adminPanelConfig,
      ADIOS_MODE_FULL,
      $web
    )
  )->render();

} catch (\Exception $e) {
  echo $e->getMessage();
}