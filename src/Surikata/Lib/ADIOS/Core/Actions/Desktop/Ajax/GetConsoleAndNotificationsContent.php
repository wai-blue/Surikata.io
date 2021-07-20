<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\Desktop\Ajax;

class GetConsoleAndNotificationsContent extends \ADIOS\Core\Action {
  public function render() {
    if (_count($_SESSION[_ADIOS_ID]['adios_notifications'])) {
      foreach ($_SESSION[_ADIOS_ID]['adios_notifications'] as $key => $val) {
        $_SESSION[_ADIOS_ID]['adios_read_notifications'][] = array_merge($val, ['read' => 1]);
        $notif[] = $val;
        unset($_SESSION[_ADIOS_ID]['adios_notifications'][$key]);
      }
    }

    return [
      'console' => $this->adios->console->getLogs(),
      'notifications' => $notif,
    ];
  }
}