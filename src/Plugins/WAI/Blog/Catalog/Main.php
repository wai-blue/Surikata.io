<?php

namespace Surikata\Plugins\WAI\Blog {

  class Catalog extends \Surikata\Core\Web\Plugin {
    public static $blogCatalogInfo = NULL;

    public function getBreadcrumbs($urlVariables = []) {
      $webPageUrl = $this->websiteRenderer->currentPage['url'];

      foreach ($urlVariables as $key => $url) {
        if (
            !is_numeric($key)
            && ($key == "year" || $key == "month")
          ) { 
          $webPageUrl = $webPageUrl . "/" . $url;
          $breadcrumbs[$webPageUrl] = $url;
        }
      }

      return $breadcrumbs;
    }

    public function getBlogCatalogInfo($year = NULL, $month = NULL, $filter = NULL, $limit = NULL) {
      $domain = $this->websiteRenderer->currentPage['domain'];

      if (
        self::$blogCatalogInfo === NULL
        || $filter !== NULL
      ) {

        $blogDetailPlugin = new \Surikata\Plugins\WAI\Blog\Detail($this->websiteRenderer);

        self::$blogCatalogInfo = (new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel))
          ->getByDate($domain, $year, $month, $filter["filteredTags"], $limit)
        ;

        foreach (self::$blogCatalogInfo as $key => $blog) {
          self::$blogCatalogInfo[$key]["url"] = $blogDetailPlugin->getWebPageUrl($blog);
        }
      }

      return self::$blogCatalogInfo;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams['page'] = (int) ($this->websiteRenderer->urlVariables["page"] ?? 1);

      $filter = (new \Surikata\Plugins\WAI\Blog\Sidebar($this->websiteRenderer))
        ->getFilterInfo()
      ;

      $twigParams['blogs'] = $this->getBlogCatalogInfo(
        $this->websiteRenderer->urlVariables["year"],
        $this->websiteRenderer->urlVariables["month"],
        $filter
      );

      if ($twigParams["itemsPerPage"] == "") $twigParams["itemsPerPage"] = 6;

      $twigParams['pages'] = ceil(count($twigParams["blogs"]) / $twigParams['itemsPerPage']);
      $twigParams['fullDateUrl'] = $this->websiteRenderer->urlVariables["year"] . $this->websiteRenderer->urlVariables["month"];
      $twigParams['blogCatalogUrl'] = $this->getWebPageUrl();

      return $twigParams;
    }

  }

}

namespace ADIOS\Plugins\WAI\Blog {

  class Catalog extends \Surikata\Core\AdminPanel\Plugin {

    var $niceName = "Blogs";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      return [
        $webPageUrl . '/?(\d+)/?(\d+)?' => [
          1 => "year",
          2 => "month",
        ],
      ];
    }

    public function manifest() {
      return [
        "faIcon" => "fas fa-blog",
        "title" => "Blogs",
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "itemsPerPage" => [
          "title"   => "Blogs per page",
          "type"    => "int",
        ],
        "showAuthor" => [
          "title"   => "Display author name",
          "type"    => "boolean",
        ],
        //"allowComments" => [
        //  "title" => "Povoliť komentovanie blogu",
        //  "type"  => "boolean"
        //]
      ];
    }

    public function install(object $installer) {
      $blogCatalogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adios);
      $blogTagModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag($this->adios);
      $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($this->adios);

      copy(
        __DIR__."/Install/blog.png",
        "{$this->adios->config['files_dir']}/blog.png",
      );

      $this->adios->db->startTransaction();

      // Blogs tags
      $blogTagModel->insertRow([
        "id" => $installer->domainIdOffset + 1,
        "domain" => $installer->domainName,
        "name" => $installer->translate("Computers"),
        "description" => $installer->translate("All about computers"),
      ]);
      $blogTagModel->insertRow([
        "id" => $installer->domainIdOffset + 2,
        "domain" => $installer->domainName,
        "name" => $installer->translate("Business"),
        "description" => $installer->translate("All about business"),
      ]);
      $blogTagModel->insertRow([
        "id" => $installer->domainIdOffset + 3,
        "domain" => $installer->domainName,
        "name" => $installer->translate("Fashion"),
        "description" => $installer->translate("All about fashion"),
      ]);

      // Blogs
      for ($i = 1; $i <= 20; $i++) {
        $idBlog = $blogCatalogModel->insertRow([
          "id" => $installer->domainIdOffset + $i,
          "domain" => $installer->domainName,
          "name" => "Blog [{$installer->domainName}] #{$i}",
          "perex" => file_get_contents(__DIR__."/Install/perex.html"),
          "content" => file_get_contents(__DIR__."/Install/blog.html"),
          "image" => "blog.png",
          "created_at" => date("Y-m-d"),
          "id_user" => 1,
        ]);

        $blogTagAssignmentModel->insertRow([
          "id_tag" => $installer->domainIdOffset + rand(1, 3),
          "id_blog" => $idBlog,
        ]);
      }

      $this->adios->db->commit();
    }

  }

}

