<?php

namespace Surikata\Plugins\WAI\Common {

  class Breadcrumb extends \Surikata\Core\Web\Plugin {

    public static $breadcrumbs = [];

    public function getMenuBreadcrumbs($pageUrl, $allowNestedPage = false) {
      $webMenuItemModel = 
        new \ADIOS\Widgets\Website\Models\WebMenuItem(
          $this->adminPanel
        )
      ;
      
      $navigationPlugin = 
        new \Surikata\Plugins\WAI\Common\Navigation(
          $this->websiteRenderer
        )
      ;

      $allMenuItems = $navigationPlugin->getMenuItems();

      $breadcrumbs = 
        $webMenuItemModel->extractParentMenuItems(
          $pageUrl, 
          $allMenuItems
        )
      ;

      if ($allowNestedPage) {
        $allPages = $this->websiteRenderer->pages;

        foreach ($allPages as $page) {
          if ($page['url'] == $pageUrl) {
            $breadcrumbs[$pageUrl] = $page['name'];
          }
        }
      }

      return $breadcrumbs;
    }

    /**
     * Returns array with full breadcrumbs array gradually
     * Get sequence of pages from WebMenuItem
     * @return array Breadcrumbs
     * */
    public function getBreadcrumbsUrl() {
      if (empty(self::$breadcrumbs)) {
        $urlVariables = $this->websiteRenderer->urlVariables;

        $currentPage = 
          $this->websiteRenderer->currentPage
          ?? []
        ;

        // GET menu items
        self::$breadcrumbs = $this->getMenuBreadcrumbs($currentPage['url']);
        self::$breadcrumbs[$currentPage['url']] = $currentPage['name'];

        $plugins = $this->adminPanel->websiteRenderer->getCurrentPagePlugins();

        foreach ($plugins as $pluginObject) {
          if (method_exists($pluginObject, 'getBreadcrumbs')) {
            if ($pluginObject->deleteCurrentPageBreadCrumb) array_shift(self::$breadcrumbs);
            $pluginBreadcrumbs = $pluginObject->getBreadcrumbs($urlVariables);

            foreach ($pluginBreadcrumbs as $url => $item) {
              self::$breadcrumbs[$url] = $item;
            }
          }
        }
      }

      return self::$breadcrumbs;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams['breadcrumbs'] = $this->getBreadcrumbsUrl();

      return $twigParams;
    }
    
  }
}

namespace ADIOS\Plugins\WAI\Common {
  class Breadcrumb extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Breadcrumbs"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "homePageUrl" => [
          "title" => "Home page link",
          "type"  => "varchar",
          "description" => "Home page link for redirect e.g.: home"
        ],
        "showHomePage" => [
          "title" => "Show home page link",
          "type"  => "boolean"
        ]
      ];
    }

  }
}