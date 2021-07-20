<?php

namespace ADIOS\Actions;

class Logout extends \ADIOS\Core\Action {
  public function preRender() {
    unset($_SESSION[_ADIOS_ID]['userProfile']);

    setcookie(_ADIOS_ID.'-user', '', 0);
    setcookie(_ADIOS_ID.'-language', '', 0);

    header("Location: {$this->adios->config['url']}");
    exit();

  }
}
