<?php

namespace Surikata\Plugins\WAI\Common {

  // REVIEW: pouziva sa BreadCrumb aj Breadcrumb. Zjednotit na Breadcrumb.
  class Breadcrumb extends \Surikata\Core\Web\Plugin {

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
      $urlVariables = $this->websiteRenderer->urlVariables;

      $currentPage = 
        $this->websiteRenderer->currentPage
        ?? []
      ;

      // GET menu items
      $breadcrumbs = $this->getMenuBreadcrumbs($currentPage['url']);
      $breadcrumbs[$currentPage['url']] = $currentPage['name'];

      // GET Plugin Breadcrumbs if exists
      // REVIEW: vid Widgets/Website/Actions/ContentStructure/PluginSettings:
      //     foreach ($this->adios->getPlugins() as $pluginName => $plugin) {
      // ... Myslim, ze kod nizsie sa da optimalizovat
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

            if (method_exists($getPlugin, 'getBreadcrumbs')) {
              if ($getPlugin->deleteCurrentPageBreadCrumb) array_shift($breadcrumbs);
              $pluginBreadcrumbs = $getPlugin->getBreadcrumbs($urlVariables);
              
              foreach ($pluginBreadcrumbs as $url => $item) {
                $breadcrumbs[$url] = $item;
              }
            }
          }
        }
      }

      return $breadcrumbs;
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
        "showHomePage" => [
          "title" => "Show home page link",
          "type"  => "boolean"
        ]
      ];
    }

  }
}