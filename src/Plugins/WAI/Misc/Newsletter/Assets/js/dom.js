class PluginWAIMiscNewsletterDOMClass extends PluginWAIMiscNewsletterAPIClass {
  subscribeNewsletter() {
    super.subscribeNewsletter(function(data) {
      Swal.fire({
        width: '40rem',
        title: '<strong>' + Surikata.translate('Your email is subscribe', 'WAI/Misc/Newsletter') + '</strong>',
        text: '',
        icon: 'success',
        confirmButtonText: 'OK',
        customClass: {
          confirmButton: 'swal-confirm-button',
        },
        timer: 10000,
      });
      
      $("#newsletterEmail").val("");
    }, function(data) {
      var textError = "Unknown error";
     
      if (data.exception.includes("AlreadyRegisteredForNewsletter")) {
        var textError = "Email is already registered";
      }
      if (data.exception.includes("EmailIsInvalid")) {
        var textError = "Email is invalid";
      }
      Swal.fire({
        width: '40rem',
        title: '<strong>' + Surikata.translate(textError, 'WAI/Misc/Newsletter') + '</strong>',
        text: '',
        icon: 'error',
        confirmButtonText: 'OK',
        customClass: {
          confirmButton: 'swal-confirm-button',
        },
        timer: 10000,
      });
    }, function(data) {
      Swal.fire({
        width: '40rem',
        title: '<strong>' + data.responseText + '</strong>',
        text: '',
        icon: 'error',
        confirmButtonText: 'OK',
        customClass: {
          confirmButton: 'swal-confirm-button',
        },
        timer: 10000,
      });
    })
  }

  validateEmail() {
    $('#newsletter').removeClass("error-input");

    if ($('#newsletterEmail').val().length > 0) {
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      var test = regex.test($('#newsletterEmail').val());
    
      if (!test) {
        $('#newsletter').addClass("error-input");
      }
    }
  }
}
