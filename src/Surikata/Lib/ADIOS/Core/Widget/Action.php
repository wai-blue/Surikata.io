<?php

namespace ADIOS\Core\Widget;

class Action extends \ADIOS\Core\Action {
  public $widget = NULL;

  function __construct(&$adios, $params = []) {
    parent::__construct($adios, $params);

    $widgetName = str_replace("ADIOS\\Actions\\", "", get_class($this));
    $widgetName = substr( $widgetName, 0, strpos($widgetName, "\\"));

    $this->widget = $this->adios->widgets[$widgetName] ?? NULL;
  }
}

