
  function ui_form_save(uid, params, btn) {
    if (typeof params === 'undefined') { params = {}; }

    var data = {};
    data.id = $('#'+uid).attr('data-id');
    data.table = $('#'+uid).attr('data-table');
    data.model = $('#' + uid).attr('data-model');
    data.values = ui_form_get_values(uid);

    var allowed = true;

    let tmpBtnText = $(btn).find('.text').text();
    $(btn).find('.text').text('Saving...');
    setTimeout(function() {
      $(btn).find('.text').text(tmpBtnText);
    }, 300);

    $('.' + uid + '_button').attr('disabled', 'disabled');

    if (typeof window[uid + '_onbeforesave'] == 'function') {
      var c_res = window[uid + '_onbeforesave'](uid, data, {});
      data = c_res['data'];
      allowed = c_res['allowed'];
    };

    $('#' + uid + ' .save_error_info').hide();
    $('#' + uid + ' .subrow').removeClass('save_error');
    $('#' + uid + ' .subrow.has_pattern').each(function () {
      let tmp_input = $(this).find('.form_input input');
      let tmp_select = $(this).find('.form_input select');
      let tmp_textarea = $(this).find('.form_input textarea');

      if (
        (tmp_input.length != 0 && !tmp_input.get(0).checkValidity())
        || (tmp_select.length != 0 && !tmp_select.get(0).checkValidity())
        || (tmp_textarea.length != 0 && !tmp_textarea.get(0).checkValidity())
      ) {
        $('#' + uid + ' .save_error_info').fadeIn();
        $(this).addClass('save_error');
        allowed = false;
      }
    });
    $('#' + uid + ' .subrow.required').each(function() {
      let tmp_input = $(this).find('.form_input input[data-is-adios-input="1"]');
      let tmp_select = $(this).find('.form_input select[data-is-adios-input="1"]');
      let tmp_textarea = $(this).find('.form_input textarea[data-is-adios-input="1"]');

      if (
        (tmp_input.length != 0 && tmp_input.val() == '')
        || (tmp_select.length != 0 && (tmp_select.val() == '' || tmp_select.val() == 0))
        || (tmp_textarea.length != 0 && tmp_textarea.val() == '')
      ) {
        $('#' + uid + ' .save_error_info').fadeIn();
        setTimeout(function() {
          $('#' + uid + ' .save_error_info').fadeOut();
        }, 1000);
        $(this).addClass('save_error');
        allowed = false;
      }
    });

    if (allowed) {
      var action = $('#'+uid).attr('data-save-action');

      _ajax_read(action, data, function(_saved_id) {
        $('.'+uid+'_button').removeAttr('disabled');
        if (isNaN(_saved_id)) _alert(_saved_id); else {

          if (data.id < 0) data.inserted_id = _saved_id;
          else data.inserted_id = 0;

          if (typeof window[uid + '_onaftersave'] == 'function') {
            window[uid + '_onaftersave'](uid, data, {});
          }

          var close_form = (data.id < 0);
          // if (typeof params.do_not_close === 'undefined'){
          //   if (!($('#'+uid).attr('data-do-not-close'))){
          //     close_form = true;
          //   }
          // }else if(!params.do_not_close){
          //   close_form = true;
          // }

          if (close_form) {
            $('#' + uid).attr('data-id', _saved_id);
            ui_form_close(uid);
          }

          if (typeof params.aftersave_callback === 'function') {
            params.aftersave_callback(uid, data);
          }else if(typeof window[params.aftersave_callback] === 'function'){
            window[params.aftersave_callback](uid, data);
          }
        };
      });
    }else{
      $('.'+uid+'_button').removeAttr('disabled');
    };

  };

  function ui_form_delete(uid) {

    var data = {};
    data.model = $('#'+uid).attr('data-model');
    data.id = $('#' + uid).attr('data-id');

    var action = $('#'+uid).attr('data-delete-action');

    $('.' + uid + '_button').attr('disabled', 'disabled');

    _ajax_read(action, data, function(_saved_id){
      $('.'+uid+'_button').removeAttr('disabled');

      if (isNaN(_saved_id)) {
        _alert(_saved_id);
      } else {

        var func_name = uid+'_onafterdelete';
        if (typeof window[func_name] == 'function') {
          window[func_name](uid, data, {});
        }

        ui_form_close(uid);

      }
    });

  };

  function ui_form_copy(uid, params){
    if(typeof params === 'undefined'){ params = {}; }

    var data = {};
    $('.'+uid+'_button').attr('disabled', 'disabled');
    data.id = $('#'+uid).attr('data-id');
    data.table = $('#'+uid).attr('data-table');
    var allowed = true;

    var func_name = uid+'_onbeforecopy';
    if (typeof window[func_name] == 'function') {
      var c_res = window[func_name](uid, data, {});
      data = c_res['data'];
      allowed = c_res['allowed'];
    };

    if (allowed){
      var action = $('#'+uid).attr('data-copy-action');
      var window_el = $('#'+uid).attr('data-window-uid');
      _ajax_read(action, data, function(_saved_id){
        $('.'+uid+'_button').removeAttr('disabled');
        if (isNaN(_saved_id)) _alert(_saved_id); else {

          var func_name = uid+'_onaftercopy';
          data.inserted_id = _saved_id;
          if (typeof window[func_name] == 'function') {
            window[func_name](uid, data, {});
          }

          if ($('#'+uid).attr('data-form-type') == 'desktop'){
            desktop_render('UI/Form', {form_type: 'desktop', table: data.table, id: _saved_id});
          }else{
            window_render('UI/Form', {table: data.table, id: _saved_id});
          };

          if(typeof params.aftercopy_callback === 'function'){
            params.aftercopy_callback(uid, data);
          }else if(typeof window[params.aftercopy_callback] === 'function'){
            window[params.aftercopy_callback](uid, data);
          }
        }
      });
    }else{
      $('.'+uid+'_button').removeAttr('disabled');
    };

  };

  function ui_form_close(uid) {

    let is_ajax = $('#' + uid).attr('data-is-ajax') == '1';

    var data = {};
    data.id = $('#' + uid).attr('data-id');
    data.table = $('#' + uid).attr('data-table');
    data.model = $('#' + uid).attr('data-model');
    data.values = ui_form_get_values(uid);

    var allowed = true;

    if (typeof window[uid + '_onbeforeclose'] == 'function') {
      var c_res = window[uid + '_onbeforeclose'](uid, data, {});
      data = c_res['data'];
      allowed = c_res['allowed'];
    }

    if (allowed) {
      if (is_ajax) {
        $('.' + uid + '_button').attr('disabled', 'disabled');

        window_close(
          $('#' + uid).attr('data-window-uid'),
          {'uid': uid, 'data': data}
        );

        ui_table_refresh_by_tag(data.table);

        if (typeof window[uid + '_onafterclose'] == 'function') {
          window[uid + '_onafterclose'](uid, data, {});
        }

        $('.' + uid + '_button').removeAttr('disabled');
      } else {
        window.location.href = _APP_URL;
      }
    }
  }

  function ui_form_get_values(uid, input_id_prefix) {
    if (typeof input_id_prefix == 'undefined') input_id_prefix = uid+'_';

    var div = document.getElementById(uid);

    var result = new Object;
    var re1 = new RegExp(/&/g);
    var re2 = new RegExp(/"/g);
    var re3 = new RegExp(/\+/g);

    var inputs = div.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) if (inputs[i].id != '' && $(inputs[i]).attr('adios-do-not-serialize') != '1') {
      var _value = inputs[i].value;
      if (inputs[i].type == 'checkbox') _value = inputs[i].checked ? '1' : '0';

      if (inputs[i].id.indexOf(input_id_prefix) == -1) input_id = inputs[i].id;
      else input_id = inputs[i].id.substring(input_id_prefix.length, inputs[i].id.length);
      if (typeof _value != 'undefined') result[input_id] = _value;
    };

    var inputs = div.getElementsByTagName('select');
    for (var i = 0; i < inputs.length; i++) if (inputs[i].id != '' && $(inputs[i]).attr('adios-do-not-serialize') != '1') {
      if (inputs[i].id.indexOf(input_id_prefix) == -1) input_id = inputs[i].id;
      else input_id = inputs[i].id.substring(input_id_prefix.length, inputs[i].id.length);

      if (typeof inputs[i].value != 'undefined') {
        var val = null;
        if (inputs[i].multiple) {
          val = [];
          for (var j = inputs[i].options.length-1; j >= 0; j--) {
            if (inputs[i].options[j].selected) {
              val.push(inputs[i].options[j].value);
            };
          };
        } else {
          val = inputs[i].value;
        };
        result[input_id] = val;
      };
    };

    var inputs = div.getElementsByTagName('textarea');
    for (var i = 0; i < inputs.length; i++) if (inputs[i].id != '' && $(inputs[i]).attr('adios-do-not-serialize') != '1') {
      if (inputs[i].id.indexOf(input_id_prefix) == -1) input_id = inputs[i].id;
      else input_id = inputs[i].id.substring(input_id_prefix.length, inputs[i].id.length);
      if (typeof inputs[i].value != 'undefined') result[input_id] = inputs[i].value;
    };

    return result;

  }

  function ui_form_type_desktop_close(uid, data){
    var func_name = uid+'_ondesktopclose';
    if (typeof window[func_name] == 'function') {
      var c_res = window[func_name](uid, data, {});
    }else{
      // desktop_main_box_history_go_back();
    };
  };

  // refresh_table = true;