<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Email {
  var $adios = NULL;

  var $subject = "";
  var $bodyHtml = "";
  var $bodyText = "";
  var $to = "";
  var $cc = "";
  var $bcc = "";

  public function __construct($adios) {
    $this->adios = &$adios;
  }

  public function send() {
    throw new \ADIOS\Core\Exception("Mailer library is not configured.");
  }

}
