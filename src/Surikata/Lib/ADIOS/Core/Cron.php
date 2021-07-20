<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

/*
  * ...
  * 
  */

class Cron {
  protected $adios;

  function __construct(&$adios, $params = []) {
    $this->adios = &$adios;
    $this->params = $params;
  }

  public function run() {
    //
  }

}

