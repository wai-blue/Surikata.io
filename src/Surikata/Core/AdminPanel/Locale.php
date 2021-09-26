<?php

namespace Surikata\Core\AdminPanel;

class Locale extends \ADIOS\Core\Locale {

  public function dateFormat() {
    return $this->adios->config["settings"]["miscellaneous"]["localeDateFormat"] ?: parent::dateFormat();
  }

  public function datetimeFormat() {
    return $this->adios->config["settings"]["miscellaneous"]["localeDatetimeFormat"] ?: parent::datetimeFormat();
  }

  public function timeFormat() {
    return $this->adios->config["settings"]["miscellaneous"]["localeTimeFormat"] ?: parent::timeFormat();
  }

  public function currencySymbol() {
    return $this->adios->config["settings"]["miscellaneous"]["localeCurrencySymbol"] ?: parent::currencySymbol();
  }

}