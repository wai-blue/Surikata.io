<?php

namespace Surikata\Plugins\WAI\Common {
  class Breadcrumb extends \Surikata\Core\Web\Plugin {

    public function getMenuBreadCrumbs($pageUrl, $allowNestedPage = false) {
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

      $breadCrumbs = 
        $webMenuItemModel->extractParentMenuItems(
          $pageUrl, 
          $allMenuItems
        )
      ;

      if ($allowNestedPage) {
        $allPages = $this->websiteRenderer->pages;

        foreach ($allPages as $page) {
          if ($page['url'] == $pageUrl) {
            $breadCrumbs[$pageUrl] = $page['name'];
          }
        }
      }

      return $breadCrumbs;
    }

    /**
     * Returns array with full breadcrumbs array gradually
     * Get sequence of pages from WebMenuItem
     * @return array BreadCrumbs
     * */
    public function getBreadCrumbsUrl() {
      $urlVariables = $this->websiteRenderer->urlVariables;

      $currentPage = 
        $this->websiteRenderer->currentPage
        ?? []
      ;

      // GET menu items
      $breadCrumbs = $this->getMenuBreadCrumbs($currentPage['url']);
      $breadCrumbs[$currentPage['url']] = $currentPage['name'];

      // GET Plugin BreadCrumbs if exists
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
          if ($panelValues->plugin !== NULL) {
            $plugin = 
              "\\Surikata\\Plugins\\" . 
              str_replace("/", "\\", $panelValues->plugin)
            ;

            $getPlugin = new $plugin($this->websiteRenderer);

            if (method_exists($getPlugin, 'getBreadCrumbs')) {
              if ($getPlugin->deleteCurrentPageBreadCrumb) array_shift($breadCrumbs);
              $pluginBreadcrumbs = $getPlugin->getBreadCrumbs($urlVariables);
              
              foreach ($pluginBreadcrumbs as $url => $item) {
                $breadCrumbs[$url] = $item;
              }
            }
          }
        }
      }

      return $breadCrumbs;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams['breadCrumbs'] = $this->getBreadCrumbsUrl();

      return $twigParams;
    }
    
  }
}

namespace ADIOS\Plugins\WAI\Common {
  class Breadcrumb extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "showHomePage" => [
          "title" => "Show home page link",
          "type"  => "boolean"
        ]
      ];
    }

  }
}