<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Locale {
  public function __construct(&$adios) {
    $this->adios = $adios;
  }

  public function dateFormat() {
    return $this->adios->config["locale"]["date"]["format"] ?? "d.m.Y";
  }

  public function currencySymbol() {
    return $this->adios->config["locale"]["currency"]["symbol"] ?? "€";
  }

  public function getAll(string $keyBy = "") {
    return [
      "dateFormat" => $this->dateFormat(),
      "currencySymbol" => $this->currencySymbol(),
    ];
  }

}