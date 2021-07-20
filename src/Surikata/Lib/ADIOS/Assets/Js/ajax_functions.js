var _ajax_debug = false;
var _ajax_custom_params = {};
var _ajax_auto_tabindex = 0;
_adios_shortcut_listener_enabled = 0;

function _ajax_update_dynamic_scripts(selector) {

  setTimeout(function(){
    if ($("button[data-adios-keyboard-shortcut]").length > 0) _adios_shortcut_listener_enabled = 1;
    else  _adios_shortcut_listener_enabled = 0;
  }, 300);

  $("<div>" + selector + "</div>").each(function() {
    var d = $(this).get(0); // document.getElementById(el_id);
    if (d == null) return;

    // najdem vsetky script tagy a vlozim do head
    var s = d.getElementsByTagName('script');
    var newScript;

    for (var x = 0; x < s.length; x++) {
      try {
        newScript = document.createElement('script');
        newScript.type = 'text/javascript';
        if (s[x].src != '') {

          xmlHttpObject = false;
          if (window.XMLHttpRequest) { // Mozilla, Safari,...
            xmlHttpObject = new XMLHttpRequest();
            if (xmlHttpObject.overrideMimeType) {
              xmlHttpObject.overrideMimeType('text/html; charset=windows-1250');
            }
          } else if (window.ActiveXObject) { // IE
            try {
              xmlHttpObject = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
              try {
                 xmlHttpObject = new ActiveXObject('Microsoft.XMLHTTP');
              } catch (e) {}
            }
          };

          xmlHttpObject.open('GET', s[x].src, false);
          xmlHttpObject.send(null);
          newScript.text = xmlHttpObject.responseText;
        } else {
          newScript.text = s[x].text;
        };

        document.body.appendChild (newScript);

        // ked script vlozim do headru, vymazem ho z elementu
      } catch (ex) { };
    };
    /* */
  });

};

function _ajax_set_custom_param(param_name, param_value) {
  _ajax_custom_params[param_name] = param_value;
}

function _ajax_params(params, clean_for_history) {
  let tmp = {};
  let params_obj = {};

  if (typeof params == 'string') {
    tmp = parseStr(params);
  } else {
    tmp = params || {};
  }

  for (var i in tmp) {
    if (i != 'action' && i != '__C__') {
      if (!clean_for_history || (i != '__IS_AJAX__' && i != '__IS_WINDOW__')) {
        params_obj[i] = (typeof tmp[i] === 'object' ? JSON.stringify(tmp[i]) : tmp[i].toString());
      }
    }
  }

  // console.log('_ajax_params', params, clean_for_history, params_obj);

  return params_obj;
}

function _action_url(action, params, clean_for_history) {
  if (typeof params == 'undefined') params = '';

  var params_obj = _ajax_params(params, clean_for_history);
  var params_str = '';
  for (var i in params_obj) {
    params_str += (params_str == '' ? '' : '&') + i + '=' + encodeURIComponent(params_obj[i]);
  };

  // btoa() pri niektorych znakoch nefunguje
  // let url = action + (params_str == '' ? '' : '?' + params_str + '&__C__=' + btoa(JSON.stringify(params_obj)));
  let url = action + (params_str == '' ? '' : '?' + params_str);

  return url;
}

function _ajax_action_url(action, params) {
  return _action_url(action, params) + '&__IS_AJAX__=1';
}

function _ajax_load(action, params, onsuccess){
  if (typeof params == 'undefined') params = new Object;
  if (typeof onsuccess == 'undefined') onsuccess = function(){};
  params.adios_ajax_json_call = 1;

  if (onsuccess == "synchronous"){
    res = _ajax_sread(action, params);
    res = _ajax_check_json_format(res);
  }else{
    _ajax_read(action, params, function(res){
      res = _ajax_check_json_format(res);
      onsuccess(res);
    });
    res = 1;
  }

  return res;

}

// kontrola json, odstranenie znakov pre a za {}
function _ajax_check_json_format(res){
  var before_json = '';
  var after_json = '';
  var first_bracket = res.indexOf('{');
  if (first_bracket > 0){
    before_json = res.substring(0,first_bracket);
    res = res.substring(first_bracket);
    _adios_console_log('AJAX_LOAD JSON ERROR (before json)', before_json);
  }
  var last_bracket = res.lastIndexOf('}');
  if ((last_bracket + 1) < res.length){
    after_json = res.substring(last_bracket + 1);
    res = res.substring(0, last_bracket + 1);
    _adios_console_log('AJAX_LOAD JSON ERROR (after json)', after_json);
  }

  try {
    res = JSON.parse(res);
  } catch ( err ) {
    if (_DEVEL_MODE){
      if (before_json == "" && after_json == "") _adios_console_log("AJAX JSON PARSE ERROR", res);
    }
    res = {};
    res.result = '';
    res.error = true;
    res.error_code = 1007;
    res.error_message = 'ERROR PARSING JSON';
  }


  return res;
}

// fcia kontroluje _ajax_load json result, v pripade chyby ju vypise, ale je to mozne vypnut
function _ajax_check_result(res, use_alert = true){
  if (res.error != false){
    if (use_alert) _alert(res.error_message);
    return false;
  }else{
    return true;
  }
}

function _ajax_read(action, params, onsuccess, onreadystatechange) {
  $.ajax({
    'type': 'GET',
    'url': _APP_URL + '/' + _ajax_action_url(action, params),
    // 'data': data,
    'success': function(res) {
      try {
        var resJson = JSON.parse(res);
      } catch (ex) {
        var resJson = null;
      }

      if (resJson === null || typeof resJson != 'object') {
        if (typeof onsuccess == 'function') {
          onsuccess(res);
        }
      } else {
        switch (resJson.result) {
          case 'WARNING':
            _alert(resJson.content);
          break;
          case 'SUCCESS':
          default:
            if (typeof onsuccess == 'function') {
              onsuccess(resJson.content);
            }
          break;
        }
      }

      if (action != 'Desktop/Ajax/GetConsoleAndNotificationsContent') {
        desktop_console_update();
      }
    },
    'xhr': function() {
      var newxhr = $.ajaxSettings.xhr();

      if (typeof onreadystatechange == 'function') {
        newxhr.onreadystatechange = onreadystatechange;
      };

      return newxhr;
    },
    'error': function (e) {
      if (typeof onresult == 'function') {
        onresult(null);
      }

      if (action != 'Desktop/Ajax/GetConsoleAndNotificationsContent'){
        if (e.status == 0) _alert('Failed to connect to server.');
        else _alert('Server error: ' + e.status);
        desktop_console_update();
      };
    }
  });
};

function _ajax_read_json(action, params, onsuccess) {
  $.ajax({
    'type': 'GET',
    'url': _APP_URL + '/' + _ajax_action_url(action, params),
    'dataType': 'json',
    'success': onsuccess,
    'complete': function() { desktop_console_update(); }
  });
};

var _ajax_sread_ret_val = {};
var _ajax_sread_use_async = false;

function _ajax_sread(action, params, options) {
  if (typeof options == 'undefined') options = new Object;

  if (_ajax_sread_use_async) {
    alert('ADIOS _ajax_sread_use_async call error');
    return 'ADIOS _ajax_sread_use_async call error';
  }

  try {
    var ret_val = trim(
      $.ajax({
        type: 'GET',
        async: false,
        url: _APP_URL + '/' + _ajax_action_url(action, params),
        success: options.success,
        complete: function() { desktop_console_update(); }
      }).responseText
    );
  } catch (ex) {
    window.console.log(ex);
    ret_val = '';
  };

  return ret_val;
};


////////////////////////////////////////////////////////////////////////
// _ajax_supdate

function _ajax_update(action, params, selector, options) {
  if (typeof options == 'undefined') options = new Object;
  options.async = true;
  options.append = false;
  _ajax_supdate(action, params, selector, options);
};

function _ajax_append(action, params, selector, options) {
  if (typeof options == 'undefined') options = new Object;
  options.async = true;
  options.append = true;
  _ajax_supdate(action, params, selector, options);
};

function _ajax_supdate(action, params, selector, options) {
  if (typeof options == 'undefined') options = new Object;
  if (typeof options.user_message == 'undefined') options.user_message = '';
  if (typeof options.progress_bar == 'undefined') options.progress_bar = true;
  if (typeof options.fade_in == 'undefined') options.fade_in = true;
  if (typeof options.identify_elements_by_class == 'undefined') options.identify_elements_by_class = false;

  if (options.identify_elements_by_class) {
    selector = '.' + selector;
  } else {
    if (selector.indexOf('.') == -1 && selector.indexOf('#') == -1) {
      selector = '#' + selector;
    } else {
      // v tomto pripade sa selector povazuje za jQuery selector
    };
  };

  // if (selector == '#adios_main_content'){
  //   // pocitadlo desktop zobrazeni - aby sa dalo spravit, ze sa na desktop nageneruje len posledna kliknuta akcia / ak niektora trva dlhsie ako predosla
  //   document.adios_desktop_update_counter = document.adios_desktop_update_counter + 1 || 0;
  //   var adios_desktop_update_counter = document.adios_desktop_update_counter;
  // }

  try {
    var tmp_min_height = $(selector).css('minHeight');

    var sel_opacity = $(selector).css('opacity');
    // $(selector).animate({'opacity': 0.3}, 100);
    adios_loading_start();

    setTimeout(function() {
      if (options.async) {
        _ajax_read(action, params, function(data) {

          // if (selector != '#adios_main_content' || document.adios_desktop_update_counter == adios_desktop_update_counter){

            $(selector).css('minHeight', tmp_min_height).css('opacity', 1);
            if (options.append) {
              if (options.log_html_to_console){
                var tmp = $.parseHTML(data);
  //              $('#adios_console_content').append(tmp);
                if (typeof tmp[0] == 'object'){
                  adios_console_log('AJAX WINDOW', $('<div></div>').append(tmp).html());
                };
              }else{
                $(selector).append($.parseHTML(data));
              }
            } else {
              // tieto 2 riadky zabezpecia, aby ak sa updatuje main box a zmizne scrollbar, tak sa zascrolluje hore
              $(selector).html('');
              useless_variable = $(document).height() - $(window).height();
              $(selector).html($.parseHTML(data));
            };

            _ajax_update_dynamic_scripts(data);
            $(selector).stop().css('opacity', 1);
            if (typeof options.success == 'function') setTimeout(options.success, 100);

          // }

          adios_loading_stop();

        });
      } else {
        _ajax_sread(action, params, { success: function(data) {

          // if (selector != '#adios_main_content' || document.adios_desktop_update_counter == adios_desktop_update_counter){

            $(selector).css('minHeight', tmp_min_height).css('opacity', 1);
            if (options.append) {
              $(selector).append($.parseHTML(data));
            } else {
              // tieto 2 riadky zabezpecia, aby ak sa updatuje main box a zmizne scrollbar, tak sa zascrolluje hore
              $(selector).html('');
              useless_variable = $(document).height() - $(window).height();
              $(selector).html($.parseHTML(data));
            };

            _ajax_update_dynamic_scripts(data);
            $(selector).stop().css('opacity', 1);
            if (typeof options.success == 'function') setTimeout(options.success, 100);
          // }

          adios_loading_stop();
        }});
      };
    }, 10);
  } catch (ex) {
    try {
      $(selector).css('opacity', 1);
      window.console.log(ex);
    } catch (ex2) { };
  };
};

function _file_download(action, params, options) {
  params.action = action;
  if (typeof options == 'undefined') options = new Object;
  query = '?adios_force_download_header=1&'
  $.each(params, function(i,n) {
    query += encodeURIComponent(i)+'='+encodeURIComponent(n)+'&';
    });
  if (options.new_window) {
    window.open(query,'_blank');
  } else {
    window.location = query;
  }
};

function _ajax_multiupload(options){

  if (typeof options.callback == 'undefined') options.callback = function(data){};
  if (typeof options.count_callback == 'undefined') options.count_callback = function(data){};
  if (typeof options.type == 'undefined') options.type = 'image';
  if (typeof options.subdir == 'undefined') options.subdir = 'multi_upload';
  if (typeof options.rename_file == 'undefined') options.rename_file = 1;
  if (typeof options.allowed_extensions == 'undefined') options.allowed_extensions = '';

  if (! $('#adios_common_file_upload_input').length > 0){
    var file_input = "<div style='height:0px;overflow:hidden;'><input type='file' id='adios_common_file_upload_input' multiple='multiple' /></div>";
    $('body').append(file_input);
    $('#adios_common_file_upload_input').focus();
    $('#adios_common_file_upload_input').on('focusin', function(){
      setTimeout(function(){
        $('#adios_common_file_upload_input').off('change');
        $('#adios_common_file_upload_input').off('focusin');
        $('#adios_common_file_upload_input').parent().remove();
        }, 200);
    });
    $('#adios_common_file_upload_input').on('change', function(){


      if ($(this).val() != ''){

        var files = $('#adios_common_file_upload_input')[0].files;

        var total = files.length;
        var item_cnt = 0;
        var formData;
        var result_array = [];

        options.count_callback(0, total);

        for (var item in files) {
          if (typeof files[item] == 'object'){
            formData = new FormData();
            formData.append('upload', files[item]);

            $.ajax({
              url: _APP_URL + '/UI/FileBrowser/Upload?__IS_AJAX__=1&output=json&type=' + options.type + '&rename_file=' + options.rename_file + '&allowed_extensions=' + options.allowed_extensions + '&subdir=' + options.subdir,
              type: 'post',
              data: formData,
              enctype: 'multipart/form-data',
              processData: false,  // tell jQuery not to process the data
              contentType: false   // tell jQuery not to set contentType
            }).done(function( data ) {



              var res = jQuery.parseJSON(data);

              if (res.uploaded == 1){
                result_array.push(res);
              }else{
                _alert(res.error.message);
              };

              item_cnt++;

              options.count_callback(item_cnt, total);
              if (item_cnt == total){
                options.callback(result_array);
              };
            });

          };
        };

      };
    });

    $('#adios_common_file_upload_input').trigger('click');

  }else{
    console.log('Upload input already initiated');
  };

};

document.adios_loading_count = 0;

function adios_loading_start() {
  document.adios_loading_count ++;
  clearTimeout(document.adios_loading_stop_timeout);
  if (!document.adios_loading_started) {
    document.adios_loading_started = 1;
    $('.adios_menu').css('opacity', '0.87');
    $('#adios_loader_line').css('width', '10%');
    setTimeout(function(){ $('#adios_loader_line').css('transition', '10s').css('width', '90%'); }, 100);
    document.body.style.cursor = "progress !important";
  };
};

function adios_loading_stop() {
  document.adios_loading_count --;
  if (document.adios_loading_count == 0){
    document.adios_loading_stop_timeout = setTimeout(function(){
      document.adios_loading_started = 0;
      $('.adios_menu').css('opacity', '1');

      $('#adios_loader_line').css('transition', '0.1s').css('width', '100%');
      setTimeout(function(){
        $('#adios_loader_line')
          .hide()
          .css('transition', '0s')
          .css('width', '0%');
      }, 200);
      setTimeout(function() {
        $('#adios_loader_line')
          .css('transition', '0.1s')
          .show()
        ;
      }, 220);
      document.body.style.cursor = "inherit";
    }, 50);
  };
};

