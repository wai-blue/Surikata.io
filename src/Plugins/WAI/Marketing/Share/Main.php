<?php

namespace Surikata\Plugins\WAI\Marketing {
  class Share extends \Surikata\Core\Web\Plugin {

    public function facebookMetaTags() {
      return "
        <meta property='og:title' content='' />
        <meta property='og:description' content='' />
        <meta property='og:image' content='' />
      ";
    }

    public function twitterMetaTags() {
      return "
        <meta name='twitter:title' content=''/>
        <meta name='twitter:description' content=''/>
        <meta name='twitter:image' content=''/>
      ";
    }

    public function facebookShareButton(array $params = []) {
      $shareUrl = $params['shareUrl'] ?? "";

      return "
        <script>
          setTimeout(() => {
            ThemeAbeloMarketingShare.setFacebookMetaTags(".json_encode($params).");
          }, 2000)
        </script>
  
        <div id='fb-root'></div>
        <div 
          class='fb-share-button' 
          data-href='{$shareUrl}' 
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

    public function twitterShareButton(array $params = []) {
      return "
        <script>
          setTimeout(() => {
            ThemeAbeloMarketingShare.setTwitterMetaTags(".json_encode($params).");
          }, 2000)
        </script>
        
        <a 
          class='twitter-share-button'
          href='https://twitter.com/intent/tweet'
        >Tweet</a>
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