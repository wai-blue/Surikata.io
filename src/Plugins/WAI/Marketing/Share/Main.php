<?php

namespace Surikata\Plugins\WAI\Marketing {
  class Share extends \Surikata\Core\Web\Plugin {

    public function facebookShareButton() {
      return "
        <div id='fb-root'></div>
        <div 
          class='fb-share-button' 
          data-layout='button'
          data-size='small'
        >
          <a 
            target='_blank' 
            class='fb-xfbml-parse-ignore'>
          </a>
        </div>
      ";
    }

    public function twitterShareButton() {
      return "
        <a 
          class='twitter-share-button'
          href='https://twitter.com/intent/tweet'
        >Tweet</a>
      ";
    }

    public function getPluginMetaTags() {
      $plugins = $this->adminPanel->websiteRenderer->getCurrentPagePlugins();
      $metaTags = [];

      foreach ($plugins as $pluginObject) {
        if (method_exists($pluginObject, "getPluginMetaTags")) {
          $metaTags = $pluginObject->getPluginMetaTags();
          if (!empty($metaTags["image"])) {
            $metaTags["image"] = "{$this->adminPanel->config['files_url']}/{$metaTags['image']}";
          }
        }
      }

      return $metaTags;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["metaTags"] = $this->getPluginMetaTags();

      return $twigParams;
    }

  }
}

namespace ADIOS\Plugins\WAI\Marketing {

  class Share extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "Social media share",
        "logo" => "social-media.jpeg",
        "description" => "",
      ];
    }

  }
}