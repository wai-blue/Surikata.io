<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Input {
  public $name;
  public $adios;

  /**
   * languageDictionary
   *
   * @internal
   * @var array
   */
  public $languageDictionary = [];

  function __construct(&$adios, $uid, $params) {
    $this->adios = &$adios;
    $this->gtp = $this->adios->gtp;
    $this->uid = (empty($uid) ? $this->adios->getUid() : $uid);
    $this->params = $params;
    $this->value = $this->params['value'];
  }

  /**
   * translate
   *
   * @internal
   * @param  mixed $string
   * @param  mixed $context
   * @param  mixed $toLanguage
   * @return void
   */
  public function translate($string, $context = "", $toLanguage = "") {
    return $this->adios->translate($string, $context, $toLanguage, $this->languageDictionary);
  }

  public function render() {
    return "";
  }

  public function formatValueToHtml() {
    return $this->value;
  }

  public function formatValueToCsv() {
    return $this->value;
  }
}
