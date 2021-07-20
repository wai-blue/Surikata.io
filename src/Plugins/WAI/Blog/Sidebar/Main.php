<?php

namespace Surikata\Plugins\WAI\Blog {

  class Sidebar extends \Surikata\Core\Web\Plugin {
    var $filterInfo = NULL;

    public function urlToId($allTags, $tags) {
      foreach ($allTags as $key => $tag) {
        if (in_array($tag['url'], $tags)) {
          array_push($this->filterInfo['filteredTags'], $tag['id']);
        }
      }
    }

    public function addTagUrl(&$allTags) {
      foreach ($allTags as $key => $tag) {
        $allTags[$key]['url'] = \ADIOS\Core\HelperFunctions::str2url($tag['name']);
      }
    }

    public function getFilterInfo() {
      $tags = $this->websiteRenderer->urlVariables["tags"] ?? [];

      $blogsTagsModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag($this->adminPanel);
      $allTags = $blogsTagsModel->getTags();
      $this->addTagUrl($allTags);

      $this->filterInfo = [
        "allTags" => $allTags,
        "filteredTags" => []
      ];

      if (!empty($tags)) {
        if (is_string($tags)) {
          $tags = explode(" ", $tags);
          $this->urlToId($allTags, $tags);
        } else if (is_array($tags)) {
          $this->urlToId($allTags, $tags);
        }
      }

      return $this->filterInfo;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $blogCatalogPlugin = new \Surikata\Plugins\WAI\Blog\Catalog($this->websiteRenderer);
      $blogDetailPlugin = new \Surikata\Plugins\WAI\Blog\Detail($this->websiteRenderer);

      $twigParams["blogs"] =$blogCatalogPlugin->getBlogCatalogInfo();

      foreach ($twigParams["blogs"] as $key => $blog) {
        $twigParams["blogs"][$key]["url"] = $blogDetailPlugin->getWebPageUrl($blog);
        $twigParams["blogs"][$key]["created_at"] = date("Y-m-d", strtotime($twigParams["blogs"][$key]["created_at"]));
        $twigParams["archive"][date("m.Y", strtotime($blog["created_at"]))]["count"] += 1;
        $twigParams["archive"][date("m.Y", strtotime($blog["created_at"]))]["year"] = date("Y", strtotime($blog["created_at"]));
        $twigParams["archive"][date("m.Y", strtotime($blog["created_at"]))]["month"] = date("m", strtotime($blog["created_at"]));
      }

      $twigParams['blogCatalogUrl'] = $blogCatalogPlugin->getWebPageUrl();
      $twigParams["filterInfo"] = $this->getFilterInfo();

      // If the BlogDetail is open tag filter cannot be used
      $twigParams["blogDetailIsOpen"] = $this->websiteRenderer->urlVariables['idBlog'] ?? "";

      return $twigParams;
    }

  }

}

namespace ADIOS\Plugins\WAI\Blog {

  class Sidebar extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "showRecent" => [
          "title" => "Zobraziť posledné blogy",
          "type"  => "boolean"
        ],
        "showArchive" => [
          "title" => "Zobraziť archív blogov",
          "type"  => "boolean"
        ],
        "showAdvertising" => [
          "title" => "Reklamný blok",
          "type"  => "boolean"
        ],
      ];
    }

  }

}

