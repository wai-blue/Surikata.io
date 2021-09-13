<?php

require(__DIR__.'/../Init.php');

$adminPanel = new \MyEcommerceProject\AdminPanel(
  $adminPanelConfig,
  ADIOS_MODE_FULL,
  new \MyEcommerceProject\Web($websiteRendererConfig)
);

class SurikataTestCase extends \PHPUnit\Framework\TestCase {
  public function __construct(?string $name = null, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    global $___ADIOSObject;
    $this->adminPanel = $___ADIOSObject;
  }
}