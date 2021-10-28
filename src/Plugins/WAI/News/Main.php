<?php

namespace Surikata\Plugins\WAI {

  class News extends \Surikata\Core\Web\Plugin {

    public function getWebPageUrlFormatted($data, $pluginSettings = []) {
      if (!empty($data['id']) && is_numeric($data['id'])) {
        // URL pre detail novinky
        return "news/{$data['id']}/".\ADIOS\Core\HelperFunctions::str2url($data['title']);
      } else {
        // URL pre zoznam noviniek
        return NULL; // ak vyreturnujem NULL, tak Surikata pouzije URLku webstranky
      }
    }

    public function getNews($domain) {
      $newsModel = $this->adminPanel
        ->getModel("Plugins/WAI/News/Models/News");
      $newsQuery = $newsModel->getQuery();
      $newsQuery->select("*");
      $newsQuery->where('domain', '=', $domain);
      $newsQuery->orderBy("show_from", "DESC");

      $news = $newsModel->fetchRows($newsQuery);
      return $news;
    }

    public function getOneNew($id) {
      $newsModel = $this->adminPanel
        ->getModel("Plugins/WAI/News/Models/News");
      $newsQuery = $newsModel->getQuery();
      $newsQuery->select("*");
      $newsQuery->where('id', '=', $id);

      $new = $newsModel->fetchRows($newsQuery);
      return empty($new) ? null : $new[$id];
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["news"] = $this->getNews($this->websiteRenderer->domain["urlPrefix"]);

      foreach ($twigParams["news"] as $key => $news) {
        $twigParams["news"][$key]["url"] = $this->getWebPageUrl($news, $pluginSettings);
      }
      $idNews = (int) substr($this->websiteRenderer->urlVariables["idNews"], 1);
      $twigParams['id_detail'] = $idNews;
      if ($idNews > 0) {
        $twigParams['detail'] = $this->getOneNew($idNews);
      }

      $twigParams['newsUrl'] = $this->getWebPageUrl();

      return $twigParams;
    }

  }

}

namespace ADIOS\Plugins\WAI {

  class News extends \Surikata\Core\AdminPanel\Plugin {

    var $niceName = "News";

    public function manifest() {
      return [
        "title" => "News",
        "faIcon" => "fas fa-rss",
        "description" => "Short news for your homepage.",
      ];
    }

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {
      return [
        $webPageUrl . '(/\d+)?/?(.*?)' => [
          1 => "idNews",
          2 => "newTitle",
        ],
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "contentType" => [
          "type" => "varchar",
          "title" => "Content type",
          "enum_values" => [
            "listOrDetail" => "List / Detail",
            "sidebar" => "Sidebar",
          ]
        ]
      ];
    }

    public function install(object $installer) {
      $newsModel = new \ADIOS\Plugins\WAI\News\Models\News($this->adios);

      $newsModel->insertRow([
        "title" => $installer->translate("Welcome to our online store"),
        "perex" => $installer->translate("We built our online store using Surikata.io."),
        "content" => $installer->translate("We built our online store using Surikata.io."),
        "domain" => $installer->domainName,
      ]);

    }

  }

}

