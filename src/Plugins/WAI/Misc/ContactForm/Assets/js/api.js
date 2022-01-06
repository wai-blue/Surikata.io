class PluginWAIMiscContactFormAPIClass {

  validateInputs(element) {
    element = $(element);
    var value = element.val();
    var test = false;

    if (value.length > 0) {
      switch (element.attr('name')) {
        case "email":
          var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
          test = regex.test(value);
          break;
        case "name":
        case "contact_message":
          test = value.length > 2;
          break;
        case "phone_number":
          test = value.length > 9;
          break;
        default:
          test = true;
          break;
      }
      if (!test) {
        element.addClass("error-input");
      } else {
        element.removeClass("error-input");
      }
    }

    return test;
  }

  sendContactForm(formId, button) {
    var form = $(formId);
    button = $(button);
    var data = {};

    var _this = this;
    form.find('input, select, textarea').each(function () {
      var element = $(this);

      if (!_this.validateInputs(element)) {
        return false;
      }
      if (element.prop('disabled') !== true && element.attr("name") !== undefined && element.attr("name") !== null) {
        if (element.val() !== undefined && element.val() instanceof Array && element.val().length > 0) {
          element.val().forEach(function (value, index) {
            data[element.attr("name")] = value;
          })
        } else {
          data[element.attr("name")] = element.val();
        }
      }
    });
    data['__renderOnlyPlugin'] = 'WAI/Misc/ContactForm';
    data['__output'] = 'json';
    let urlToSend = window.location.href;

    $.getJSON(urlToSend,
      data
    ).done(function (response) {
      try {
        //response = $.parseJSON(response);
        if (response["status"] === "success") {
          Swal.fire({
            title: '',
            text: button.attr("data-message-success"),
            icon: 'success',
            confirmButtonText: 'OK'
          });
          clearInputs(formId);
        }
        else {
          Swal.fire({
            title: '',
            text: button.attr("data-message-inputs"),
            icon: 'error',
            confirmButtonText: button.attr("data-error-button")
          });
        }
        console.log(response);
      } catch (e) {
        console.log(response);
        return;
      }
      console.log(response);
    });

    return false;
  }

}