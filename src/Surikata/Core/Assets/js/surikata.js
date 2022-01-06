class SurikataClass {
  serializeForm(selector) {
    let formData = new FormData(document.querySelector(selector));
    let data = {};

    for (var key of formData.keys()) {
      data[key] = formData.get(key);
    }

    return data;
  }

  renderPlugin(pluginName, data, success) {
    data.__renderOnlyPlugin = pluginName;
    $.get('', data, success);
  }

  renderPluginJSON(pluginName, requestData, success, fail, error) {
    requestData.__renderOnlyPlugin = pluginName;
    requestData.__output = 'json';

    $.getJSON(
      '',
      requestData
    ).done(function (data) {
      if (data.status == 'FAIL') {
        if (typeof fail == 'function') {
          fail(data);
        } else {
          let msg = 'Error: ' + data.exception;
          if (data.error != '') {
            msg += '\nDescription: ' + data.error;
          }
          
          alert(msg);
        }
      } else if (typeof success == 'function') {
        success(data);
      }
    }).fail(function (data) {
      if (typeof error == 'function') {
        error(data);
      } else {
        console.log('renderPluginJSON Error', pluginName, requestData, data);
      }
    });
  }

  getDictionary() {
    return __srkt_dict__; // tato premenna je renderovana cez dictionary.js, cez assetsUrlMap
  }

  translate(original, context) {
    let dictionary = this.getDictionary();
    let translated = null;

    if (typeof dictionary == 'undefined') {
      return original + ' [TRANSLATION WARNING: Dictonary is missing.]';
    }

    if (context == '' || typeof context == 'undefined') {
      return original + ' [TRANSLATION WARNING: Context not specified.]';
    }

    if (typeof dictionary[context] != 'undefined') {
      translated = dictionary[context][original];
    }

    if (translated == "" || translated == null) {
      return original;
    }

    return translated;
  }
}

var Surikata = new SurikataClass();
