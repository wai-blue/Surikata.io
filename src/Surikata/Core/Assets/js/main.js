function Surikata() { }

Surikata.prototype.serializeForm = function (selector) {
  let formData = new FormData(document.querySelector(selector));
  let data = {};

  for (var key of formData.keys()) {
    data[key] = formData.get(key);
  }

  return data;
}

Surikata.prototype.renderPlugin = function (pluginName, data, success) {
  data.__renderOnlyPlugin = pluginName;
  $.get('', data, success);
}

Surikata.prototype.renderPluginJSON = function (pluginName, requestData, success, fail, error) {
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

var Surikata = new Surikata();