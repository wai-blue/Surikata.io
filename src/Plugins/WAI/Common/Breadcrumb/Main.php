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
      
      $navigationPluginSettings = 
        $this->websiteRenderer->getCurrentPagePluginSettings(
          "WAI/Common/Navigation"
        )
      ;

      $allMenuItems = 
        $webMenuItemModel->getByIdMenu(
          $navigationPluginSettings['menuId']
        )
      ; 

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

        // GET Plugin Breadcrumbs if exists
        $contentStructure = 
          json_decode(
            $this->websiteRenderer->currentPage["content_structure"]
          )
        ;

        foreach ($contentStructure->panels as $panelName => $panelValues) {
          if (
            $panelName != "navigation" 
            && $panelName != "header" 
            && $panelName != "footer"
          ) {
            if ($panelValues->plugin != "") {
              $plugin = 
                "\\Surikata\\Plugins\\" . 
                str_replace("/", "\\", $panelValues->plugin)
              ;

              $getPlugin = new $plugin($this->websiteRenderer);

              if (method_exists($getPlugin, 'getBreadcrumbs')) {
                if ($getPlugin->deleteCurrentPageBreadCrumb) array_shift(self::$breadcrumbs);
                $pluginBreadcrumbs = $getPlugin->getBreadcrumbs($urlVariables);

                foreach ($pluginBreadcrumbs as $url => $item) {
                  self::$breadcrumbs[$url] = $item;
                }
              }
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