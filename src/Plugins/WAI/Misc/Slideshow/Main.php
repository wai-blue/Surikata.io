<?php

namespace Surikata\Plugins\WAI\Misc {

  class Slideshow extends \Surikata\Core\Web\Plugin {

    public function getTwigParams($pluginSettings) {
      $domain = $this->websiteRenderer->currentPage['domain'];

      $twigParams = $pluginSettings;
      
      $twigParams["slideShowSlides"] = (new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\HomepageSlideshow($this->adminPanel))
        ->where('domain', $domain)
        ->get()
        ->toArray()
      ;
      
      return $twigParams;
    }
  }

}

namespace ADIOS\Plugins\WAI\Misc {
  class Slideshow extends \Surikata\Core\AdminPanel\Plugin {

    var $niceName = "Slideshow";

    public function manifest() {
      return [
        "title" => "Slideshow",
        "faIcon" => "fas fa-images",
        "description" => "Slideshow for your homepage",
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "speed" => [
          "title" => "Speed",
          "type" => "varchar",
        ]
      ];
    }

    public function install(object $installer) {
      $slideshowModel = new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\HomepageSlideshow($this->adios);

      copy(__DIR__."/Install/1.jpg", "{$this->adios->config['files_dir']}/slide-1.jpg");
      copy(__DIR__."/Install/2.jpg", "{$this->adios->config['files_dir']}/slide-2.jpg");
      copy(__DIR__."/Install/3.jpg", "{$this->adios->config['files_dir']}/slide-3.jpg");

      $slideshowModel->insertRow([
        "domain" => $installer->domainName,
        "heading" => $installer->translate("Welcome"),
        "description" => $installer->translate("Your best online store"),
        "image" => "slide-1.jpg",
        "button_url" => "produkty",
        "button_text" => $installer->translate("Start shopping"),
      ]);
      $slideshowModel->insertRow([
        "domain" => $installer->domainName,
        "heading" => $installer->translate("Discounts"),
        "description" => $installer->translate("We have something special for your"),
        "image" => "slide-2.jpg",
        "button_url" => $installer->translate("discounts"),
        "button_text" => $installer->translate("Show discounts"),
      ]);
      $slideshowModel->insertRow([
        "domain" => $installer->domainName,
        "heading" => $installer->translate("Check our luxury products"),
        "description" => $installer->translate("We sell only most-rated and reliable products"),
        "image" => "slide-3.jpg",
      ]);

    }

  }
}