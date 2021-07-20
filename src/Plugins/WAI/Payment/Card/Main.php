<?php

namespace Surikata\Plugins\WAI\Payment {

  class Card extends \Surikata\Core\Web\Plugin\Payment {

    public function getPaymentMeta() {
      return [
       "name" => "Platba kartou",
       "description" => "Visa, Master, Diners, ...",
      ];
    }

    public function getTwigParams($pluginSettings) {
      return [];
    }
  }

}

namespace ADIOS\Plugins {

  class Card extends \Surikata\Core\AdminPanel\Plugin {
    // sem pojdu nastavovacky ako napr. merchandId a podobne
  }

}
