<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class UI {
    public function __construct(&$adios, $params) {
      $this->adios = $adios;
    }

    public function create($component_name, $params = null) {
      list($component_class, $uid) = explode('#', $component_name);

      $params['component_class'] = $component_class;
      if (!empty($uid)) {
        $params['uid'] = $uid;
      }

      $class_name = "\\ADIOS\\Core\\UI\\{$component_class}";
      return new $class_name($this->adios, $params);
    }

    public function render($component_name, $params = null) {
      return $this->create($component_name, $params)->render();
    }

    /* WRAPPER FUNKCIE */

    public function Title($params) {
      return $this->create('Title', $params);
    }

    public function Form($params) {
      return $this->create('Form', $params);
    }

    public function Input($params) {
      return $this->create('Input', $params);
    }

    public function Tabs($params) {
      return $this->create('Tabs', $params);
    }

    public function Table($params) {
      return $this->create('Table', $params);
    }

    public function Tree($params) {
      return $this->create('Tree', $params);
    }

    public function FileBrowser($params) {
      return $this->create('FileBrowser', $params);
    }

    public function SettingsPanel($params) {
      return $this->create('SettingsPanel', $params);
    }

    public function Window($params) {
      return $this->create('Window', $params);
    }

    public function Button($params = []) {
      return $this->create('Button', $params);
    }

}
