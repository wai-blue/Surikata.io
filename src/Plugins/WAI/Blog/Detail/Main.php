<?php

namespace Surikata\Plugins\WAI\Blog {

  class Detail extends \Surikata\Core\Web\Plugin {

    var $defaultBlogDetailUrl = "blog/{% idBlog %}/{% blogName %}";
    var $deleteCurrentPageBreadCrumb = true;

    public static array $currentBlog = [];

    public function getPluginMetaTags() {
      $blogDetail = $this->getCurrentBlog();

      return [
        "title" => $blogDetail["name"],
        "description" => $blogDetail["perex"],
        "image" => $blogDetail["image"]
      ];
    }

    public function getBreadcrumbs($urlVariables = []) {
      $currentBlog = $this->getCurrentBlog();

      $breadcrumbModel = 
        new \Surikata\Plugins\WAI\Common\Breadcrumb(
          $this->websiteRenderer
        )
      ;

      $breadcrumbs = $breadcrumbModel->getMenuBreadcrumbs(
        $currentBlog['blogCatalogUrl'], 
        true
      );

      $currentBlogYear = date("Y", strtotime($currentBlog['created_at']));
      $currentBlogMonth = date("m", strtotime($currentBlog['created_at']));

      $breadcrumbs[$currentBlogYear] = $currentBlogYear;
      $breadcrumbs[$currentBlogYear . "/" . $currentBlogMonth] = $currentBlogMonth;

      $breadcrumbs[$this->getWebPageUrl($currentBlog)] = $currentBlog['name'];

      return $breadcrumbs;
    }

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {
      $url = $pluginSettings["urlPattern"] ?? "";

      if (empty($url)) {
        $url = $this->defaultBlogDetailUrl;
      }

      $url = str_replace("{% idBlog %}", \ADIOS\Core\HelperFunctions::str2url($urlVariables['id']), $url);
      $url = str_replace("{% blogName %}", \ADIOS\Core\HelperFunctions::str2url($urlVariables['name']), $url);

      return $url;
    }

    public function getCurrentBlog() {
      if (empty(self::$currentBlog)) {
        $userModel = new \ADIOS\Core\Models\User($this->adminPanel);
        $sidebar = new \Surikata\Plugins\WAI\Blog\Sidebar($this->websiteRenderer);

        self::$currentBlog = (new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel))
          ->getById((int) $this->websiteRenderer->urlVariables['idBlog']);
        ;

        self::$currentBlog['blogCatalogUrl'] = (new \Surikata\Plugins\WAI\Blog\Catalog($this->websiteRenderer))
          ->getWebPageUrl()
        ;
        self::$currentBlog["user"] = $userModel->getById(self::$currentBlog["id_user"]);
        $sidebar->addTagUrl(self::$currentBlog["blog_tags"]);
      }

      return self::$currentBlog;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["blog"] = $this->getCurrentBlog();

      return $twigParams;
    }

  }

}

namespace ADIOS\Plugins\WAI\Blog {

  class Detail extends \Surikata\Core\AdminPanel\Plugin {

    var $defaultBlogDetailUrl = "blog/{% idBlog %}/{% blogName %}";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["urlPattern"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultBlogDetailUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "idBlog" => '(\d+)',
          "blogName" => '(.+)',
        ]
      );
      
      return $siteMap;
    }

    public function getSettingsForWebsite() {
      return [
        "urlPattern" => [
          "title" => "Blog detail page URL",
          "type" => "varchar",
          "description" => "
            Relative URL for blog detail page.<br/>
            Default value: {$this->defaultBlogDetailUrl}
          ",
        ],
        "showAuthor" => [
          "title"   => "Display author name",
          "type"    => "boolean",
        ],
      ];
    }

  }

}

