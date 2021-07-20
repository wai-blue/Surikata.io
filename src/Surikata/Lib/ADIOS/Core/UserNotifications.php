<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class UserNotifications {
  const FORMAT_HTML = 1;
  const FORMAT_STRING = 2;

  public $notifications = [];

  public function __construct(&$adios) {
    $this->adios = $adios;
  }

  public function add($type, $notification) {
    $this->notifications[] = [$type, $notification];
    return TRUE;
  }

  public function addHtml($notification) {
    return $this->add(self::FORMAT_HTML, $notification);
  }

  public function addString($notification) {
    return $this->add(self::FORMAT_STRING, $notification);
  }

  public function get() {
    return $this->notifications;
  }

  public function getAsHtml() {
    $notificationsAsHtml = [];

    foreach ($this->notifications as $notification) {
      switch ($notification[0]) {
        case self::FORMAT_HTML:
          $notificationsAsHtml[] = $notification[1];
        break;
        case self::FORMAT_STRING:
        default:
          $notificationsAsHtml[] = hsc(nl2br($notification[1]));
        break;
      }
    }

    return $notificationsAsHtml;
  }

}