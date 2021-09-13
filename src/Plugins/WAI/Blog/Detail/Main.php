<?php

namespace Surikata\Plugins\WAI\Blog {

  class Detail extends \Surikata\Core\Web\Plugin {

    var $defaultBlogDetailUrl = "blog/{% idBlog %}/{% blogName %}";
    var $deleteCurrentPageBreadCrumb = true;

    public function getBreadCrumbs($urlVariables = []) {
      $currentBlog = $this->getCurrentBlog();

      $breadCrumb = 
        new \Surikata\Plugins\WAI\Common\Breadcrumb(
          $this->websiteRenderer
        )
      ;

      $breadCrumbs = $breadCrumb->getMenuBreadCrumbs(
        $currentBlog['blogCatalogUrl'], 
        true
      );

      $currentBlogYear = date("Y", strtotime($currentBlog['created_at']));
      $currentBlogMonth = date("m", strtotime($currentBlog['created_at']));

      $breadCrumbs[$currentBlogYear] = $currentBlogYear;
      $breadCrumbs[$currentBlogYear . "/" . $currentBlogMonth] = $currentBlogMonth;

      $breadCrumbs[$this->getWebPageUrl($currentBlog)] = $currentBlog['name'];

      return $breadCrumbs;
    }

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = []) {
      $url = $pluginSettings["urlPattern"] ?? "";

      if (empty($url)) {
        $url = $this->defaultBlogDetailUrl;
      }

      $url = str_replace("{% idBlog %}", \ADIOS\Core\HelperFunctions::str2url($urlVariables['id']), $url);
      $url = str_replace("{% blogName %}", \ADIOS\Core\HelperFunctions::str2url($urlVariables['name']), $url);

      return $url;
    }

    public function getCurrentBlog() {
      $userModel = new \ADIOS\Core\Models\User($this->adminPanel);
      $sidebar = new \Surikata\Plugins\WAI\Blog\Sidebar($this->websiteRenderer);

      $blog = (new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel))
        ->getById((int) $this->websiteRenderer->urlVariables['idBlog']);
      ;

      $blog['blogCatalogUrl'] = (new \Surikata\Plugins\WAI\Blog\Catalog($this->websiteRenderer))
        ->getWebPageUrl()
      ;
      $blog["user"] = $userModel->getById($blog["id_user"]);
      $sidebar->addTagUrl($blog["blog_tags"]);

      return $blog;
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

