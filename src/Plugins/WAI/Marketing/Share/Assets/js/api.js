class PluginWAIMarketingShareAPIClass {

  constructor() {
    this.initFacebookApi();
    this.initTwitterApi();
  }

  initFacebookApi() {
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v12.0';
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  }

  initTwitterApi() {
    window.twttr = (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0],
        t = window.twttr || {};
      if (d.getElementById(id)) return t;
      js = d.createElement(s);
      js.id = id;
      js.src = 'https://platform.twitter.com/widgets.js';
      fjs.parentNode.insertBefore(js, fjs);

      t._e = [];
      t.ready = function(f) {
        t._e.push(f);
      };
  
      return t;
    }(document, 'script', 'twitter-wjs'));
  }

  setFacebookMetaTags(params) {
    $('meta[property=\'og:title\']').attr('content', params['title']);
    $('meta[property=\'og:description\']').attr('content', params['description']);
    $('meta[property=\'og:image\']').attr('content', params['image']);
  }

  setTwitterMetaTags(params) {
    $('meta[name=\'twitter:title\']').attr('content', params['title']);
    $('meta[name=\'twitter:description\']').attr('content', params['description']);
    $('meta[name=\'twitter:image\']').attr('content', params['image']);
  }

}
