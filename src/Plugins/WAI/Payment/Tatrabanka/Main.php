<?php

namespace Surikata\Plugins\WAI\Payment {

  class Tatrabanka extends \Surikata\Core\Web\Plugin\Payment {

    public function getPaymentMeta() {
      return [
       "name" => "Tatrabanka",
       "description" => "Najlepsi sposob platby cez internetbanking",
      ];
    }

    public function getTwigParams($pluginSettings) {
      // tu sa budu kontrolovat _GET premenne (resp. cascada->urlVariables)
      // princip:
      //   1. najprv sa vygeneruje stranka s info, ze "budete presmerovani na platobnu branu"
      //      Stranku vygeneruje TWIG template na zaklade TwigParams, ktore budu
      //      vyreturnovane tu. TwigParams pre zobrazenie tejto info-stranky sa budu
      //      generovat ak napr. _GET.performRedirect == 1
      //   2. ked navstevnik vykona platbu, Tatrabanka ho vrati naspat do obchodu
      //      s nejakym _GET parametrom. Ak tento bude nastaveny, tak sa vygeneruju
      //      TwigParams, ktore zobrazia info o uspechu platby. Ak bude platba uspesna
      //      spusti sa $this->adios->widgets->Orders->confirmOrderPayment().
      //      Tato funkcia zabezpeci odoslanie potrebnych emailov.
      return [];
    }
  }

}

namespace ADIOS\Plugins {

  class Tatrabanka extends \Surikata\Core\AdminPanel\Plugin {
    // sem pojdu nastavovacky ako napr. merchandId a podobne
  }

}

