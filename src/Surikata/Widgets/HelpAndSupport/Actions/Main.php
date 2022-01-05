<?php

namespace ADIOS\Actions\HelpAndSupport;

class Main extends \ADIOS\Core\Widget\Action {
  public function preRender() {
    return [
      "userGuideUrl" => $this->widget->getUserGuideUrl(),
    ];
  }
}