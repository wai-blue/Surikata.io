class PluginWAIMiscNewsletterAPIClass {

  subscribeNewsletter(success, fail, error) {
    var emailForNewsletter = $("#newsletterEmail").val();

    Surikata.renderPluginJSON(
      'WAI/Misc/Newsletter',
      {
        'action': 'subscribe',
        'email': emailForNewsletter
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      },
      function (data) {
        if (typeof fail == 'function') {
          fail(data);
        }
      },
      function (data) {
        if (typeof fail == 'function') {
          error(data);
        }
      }
    )
  }

}
