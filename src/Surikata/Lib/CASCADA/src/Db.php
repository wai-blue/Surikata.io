<?php

namespace CASCADA;

class DB {
  var $config = [];

  function __construct($config) {
    $this->config = is_array($config) ? $config : array();
  }

  function connect() {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($this->config["connection"]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
  }
}