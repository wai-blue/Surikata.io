<?php

namespace Surikata\Plugins\WAI\Misc {

  class ElasticSearch extends \Surikata\Core\Web\Plugin {

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      // Tu vykonat REST volanie na Elastic server
      // a vratit search results.
      // Pouzit $pluginSettings["elasticServerEndpointURL"].

      return $twigParams;
    }

  }

}

namespace ADIOS\Plugins\WAI\Misc {

  class ElasticSearch extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "elasticServerEndpointURL" => [
          "title"   => "Adresa Elastic servera",
          "type"    => "varchar",
        ],
      ];
    }

    public function onModelAfterSave($event) {
      $model = $event['model'];
      $data = $event['data'];

      // Tu na zaklade $model->name vykonat volanie
      // na Elastic server a vlozit potrebne hodnoty
      // do elastic indexu.
      // Pouzit $this->adios->config["plugins"]["WAI/Misc/ElasticSearch"]["elasticServerEndpointURL"].

      return $event; // forward event unchanged
    }

  }

}

