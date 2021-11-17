<?php

namespace ADIOS\Actions\Overview;

class Welcome extends \ADIOS\Core\Widget\Action {
  public function preRender() {
    $this->adios->checkFoldersPermissions();
    
    return [
      "license" => [
        "validFrom" => "2021-06-24",
      ],
      "version" => "1.4.5.22",
      "stats" => [
        "productCount" => 999,
      ]
    ];
  }
}