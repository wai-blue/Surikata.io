<?php

namespace Surikata\Plugins\WAI\Marketing {
  class Share extends \Surikata\Core\Web\Plugin {

    public function facebookShareButton(array $params = []) {
      $shareUrl = $params['shareUrl'] ?? "";
      $shareTitle = $params['title'] ?? "";
      $shareImage = $params['image'] ?? "";
      $shareDescription = $params['description'] ?? "";

      return "
        <div id='fb-root'></div>
        <script async defer crossorigin='anonymous' 
          src='https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v12.0' nonce='oCXllsPr'
        ></script>
        <div 
          class='fb-share-button' 
          data-href='{$shareUrl}' 
          data-layout='button' 
          data-size='small'
        >
          <a 
            target='_blank' 
            class='fb-xfbml-parse-ignore'>Zdieľať
          </a>
        </div>
      ";
    }

    public function facebookMetaTags() {
      return "
        <meta property='og:title'         content='' />
        <meta property='og:description'   content='' />
        <meta property='og:image'         content='' />
      ";
    }
  }
}

namespace ADIOS\Plugins\WAI\Marketing {

  class Share extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "Social media share",
        "logo" => "",
        "description" => "",
      ];
    }

  }
}