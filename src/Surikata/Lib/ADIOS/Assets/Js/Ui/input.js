
function ui_input_datetime_change(uid) {

  var hour = parseInt($('#' + uid + '_time_hour_picker').val());
  if (hour < 0 || hour > 23 || isNaN(hour)) hour = 0;
  if (hour.toString().length == 1) hour = '0' + hour;
  var min = parseInt($('#' + uid + '_time_minute_picker').val());
  if (min < 0 || min > 59 || isNaN(min)) min = 0;
  if (min.toString().length == 1) min = '0' + min;
  var sec = parseInt($('#' + uid + '_time_second_picker').val());
  if (sec < 0 || sec > 59 || isNaN(sec)) sec = 0;
  if (sec.toString().length == 1) sec = '0' + sec;
  var tmp = $('#' + uid + '').val();
  if (tmp == '') tmp = 'dd.mm.yyyy';
  tmp = tmp.split(' ');
  tmp = tmp[0];
  tmp = tmp + ' ' + hour + ':' + min + ':' + sec;
  $('#' + uid + '').val(tmp);
  $('#' + uid).trigger('onchange');
};

function ui_input_time_change(uid) {

  var hour = parseInt($('#' + uid + '_time_hour_picker').val());
  if (hour < 0 || hour > 23 || isNaN(hour)) hour = 0;
  if (hour.toString().length == 1) hour = '0' + hour;
  var min = parseInt($('#' + uid + '_time_minute_picker').val());
  if (min < 0 || min > 59 || isNaN(min)) min = 0;
  if (min.toString().length == 1) min = '0' + min;
  var sec = parseInt($('#' + uid + '_time_second_picker').val());
  if (sec < 0 || sec > 59 || isNaN(sec)) sec = 0;
  if (sec.toString().length == 1) sec = '0' + sec;
  var tmp = hour + ':' + min + ':' + sec;
  $('#' + uid + '').val(tmp);
  $('#' + uid).trigger('onchange');
};

function ui_input_parse_time(uid, val) {
  val = val.split(' ');
  val = val[1];
  if (typeof val != 'undefined') {
    val = val.split(':');
    $('#' + uid + '_time_hour_picker').val(val[0]);
    $('#' + uid + '_time_minute_picker').val(val[1]);
    $('#' + uid + '_time_second_picker').val(val[2]);
  };
};

function ui_input_activate_drop(uid) {
  $('#' + uid + '_image_form').on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
    e.preventDefault();
    e.stopPropagation();
  })
    .on('dragover dragenter', function () {
      $('#' + uid + '_image').css('opacity', 0.4);
      $('#' + uid + '_file').css('opacity', 0.4);
    })
    .on('dragleave dragend drop', function () {
      $('#' + uid + '_image').css('opacity', 1);
      $('#' + uid + '_file').css('opacity', 1);
    })
    .on('drop', function (e) {
      droppedFiles = e.originalEvent.dataTransfer.files;
      $('#' + uid + '_file_input').prop('files', droppedFiles);
      return false;
    });
}

function ui_input_image_open(uid) {
  window.open($('#' + uid).attr('data-src-real-base') + '/' + $('#' + uid).val());
};

function ui_input_image_remove(uid) {
  $('#' + uid).val('');
  $('#' + uid + '_image').attr('src', $('#' + uid).attr('data-default-src'));
  $('#' + uid + '_file_input_delete_button').hide();
  $('#' + uid + '_file_input_open_button').hide();
  $('#' + uid).trigger('onchange');
};

function ui_input_upload_image(uid) {
  var formData = new FormData();
  formData.append('upload', $('#' + uid + '_file_input')[0].files[0]);

  if ($('#' + uid + '_file_input').val() != '') {
    $('#' + uid + '_info_div').css('display', 'inline-block');
    $('#' + uid + '_operations').hide();
    $('#' + uid + '_image').css('opacity', 0.4);
    $.ajax({
      url: $('#' + uid).attr('data-upload-url'),
      type: 'post',
      data: formData,
      enctype: 'multipart/form-data',
      processData: false,  // tell jQuery not to process the data
      contentType: false   // tell jQuery not to set contentType
    }).done(function (data) {

      $('#' + uid + '_file_input').val('');

      var res = jQuery.parseJSON(data);
      if (res.uploaded == 1) {
        $('#' + uid).val(res.file_path);
        $('#' + uid + '_image').attr('src', $('#' + uid).attr('data-src-base') + encodeURI(res.file_path)).one('load', function () {
          $('#' + uid + '_info_div').hide();
          $('#' + uid + '_operations').show();
          $('#' + uid + '_file_input_delete_button').show();
          $('#' + uid + '_file_input_open_button').show();
          $('#' + uid + '_image').css('opacity', 1);
          $('#' + uid).trigger('onchange');
        });
      } else {
        $('#' + uid + '_info_div').hide();
        $('#' + uid + '_operations').show();
        $('#' + uid + '_image').css('opacity', 1);
        $('#' + uid).val('');
        _alert(res.error.message);
      };
    });
  };
};

function ui_input_image_set_value(uid, value, name) {
  $('#' + uid + '_image').css('opacity', 0.3);
  $('#' + uid).val(value);
  $('#' + uid + '_image').attr('src', $('#' + uid).attr('data-src-base') + value).one('load', function () {
    $('#' + uid + '_operations').show();
    $('#' + uid + '_file_input_delete_button').show();
    $('#' + uid + '_file_input_open_button').show();
    $('#' + uid + '_image').css('opacity', 1);
    $('#' + uid).trigger('onchange');
  });
};


function ui_input_file_open(uid) {
  window.open($('#' + uid).attr('data-src-real-base') + '/' + $('#' + uid).val());
};

function ui_input_file_remove(uid) {
  $('#' + uid).val('');
  $('#' + uid + '_file').html($('#' + uid).attr('data-default-txt'));
  $('#' + uid + '_file_input_delete_button').hide();
  $('#' + uid + '_file_input_open_button').hide();
  $('#' + uid).trigger('onchange');
};

function ui_input_upload_file(uid) {
  let fileInput = $('#' + uid + '_file_input');
  let formData = new FormData();
  formData.append('upload', fileInput[0].files[0]);

  if ($('#' + uid + '_file_input').val() != '') {
    $('#' + uid + '_info_div').css('display', 'inline-block');
    $('#' + uid + '_operations').hide();
    $('#' + uid + '_file').css('opacity', 0.4);

    $.ajax({
      url: _APP_URL + '/UI/FileBrowser/Upload',
      type: 'post',
      data: formData,
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false
    }).done(function (data) {

      $('#' + uid + '_file_input').val('');

      var res = jQuery.parseJSON(data);

      if (res.uploaded == 1) {
        $('#' + uid).val(res.fileName);
        $('#' + uid + '_file').html((res.fileName.length > 75 ? res.fileName.substr(0, 75) + '...' : res.fileName));
        $('#' + uid + '_info_div').hide();
        $('#' + uid + '_operations').show();
        $('#' + uid + '_file_input_delete_button').show();
        $('#' + uid + '_file_input_open_button').show();
        $('#' + uid + '_file').css('opacity', 1);
        $('#' + uid).trigger('onchange');
      } else {
        $('#' + uid + '_info_div').hide();
        $('#' + uid + '_operations').show();
        $('#' + uid + '_file').css('opacity', 1);
        $('#' + uid).val('');

        _alert(res.error);
      };
    });
  };
};

function ui_input_file_set_value(uid, value, name) {
  $('#' + uid).val(value);
  $('#' + uid + '_file').html((name.length > 75 ? name.substr(0, 75) + '...' : name));
  $('#' + uid + '_operations').show();
  $('#' + uid + '_file_input_delete_button').show();
  $('#' + uid + '_file_input_open_button').show();
  $('#' + uid).trigger('onchange');
};



function ui_input_lookup_set_next_item(uid) {
  var items = $('#' + uid + '_result_div').find('a.item');
  var current_active_item = $('#' + uid + '_result_div').find('a.item.active');
  var last_item = items.last();

  if (current_active_item.length == 0 || current_active_item.attr('data-id') == last_item.attr('data-id')) {
    items.removeClass('active');
    items.first().addClass('active');
  } else {
    items.removeClass('active');
    current_active_item.next().addClass('active');
  };

  //$('body').scrollTo($('#'+uid+'_result_div a.item.active').eq(0), 10, { axis: 'y', offset: {top: -50, left: 0} });
};

function ui_input_lookup_set_previous_item(uid) {
  var items = $('#' + uid + '_result_div').find('a.item');
  var current_active_item = $('#' + uid + '_result_div').find('a.item.active');
  var first_item = items.first();

  if (current_active_item.length == 0 || current_active_item.attr('data-id') == first_item.attr('data-id')) {
    items.removeClass('active');
    items.last().addClass('active');

  } else {
    items.removeClass('active');
    current_active_item.prev().addClass('active');
  };

  //$('body').scrollTo($('#'+uid+'_result_div a.item.active').eq(0), 10, { axis: 'y', offset: {top: -50, left: 0} });
};

function ui_input_lookup_use_current_item(uid) {
  var current_active_item = $('#' + uid + '_result_div').find('a.item.active');

  if (current_active_item.length != 0) {
    ui_input_lookup_set_value(uid, current_active_item.attr('data-id'), current_active_item.html());
  };

  setTimeout(function () { ui_input_lookup_hide_results(uid); }, 250);
};

function ui_input_lookup_onkeydown(event, uid) {
  var search_timeout = 600;
  if (event.keyCode == 8) search_timeout = 1000;
  if (event.keyCode == 40) { // down
    ui_input_lookup_set_next_item(uid);
  } else if (event.keyCode == 38) { // up
    ui_input_lookup_set_previous_item(uid);
  } else if (event.keyCode == 13) {
    ui_input_lookup_use_current_item(uid);
  } else if (event.keyCode == 27) {
    ui_input_lookup_hide_results(uid);
    setTimeout(function () { $('#' + uid + '_autocomplete_input').focus(); }, 100); // patch na globalnu 'defocus' akciu pre Esc klavesu
  } else {
    $('#' + uid + '_result_div_inner').html('<div class=\"loading\">Searching ...</div>').show();
    $('#' + uid + '_result_div').show();
    
    clearTimeout(document.adios_autocomplete_timeout);
    document.adios_autocomplete_timeout = setTimeout(function () {

      if ($('#' + uid + '_autocomplete_input').is(':focus')) {

        if (
          $('#' + uid + '_autocomplete_input').val() != $('#' + uid + '_autocomplete_input').attr('data-value')
          || event.type == 'focus'
        ) {
          $('#' + uid + '_autocomplete_input').attr('data-value', $('#' + uid + '_autocomplete_input').val());
          ui_input_lookup_set_pos(uid);
          ui_input_lookup_hide_results(uid);

          $('.' + uid + '.autocomplete').not('#' + uid + '_result_div').remove();

          var requested_value = goval('' + uid + '_autocomplete_input');

          $('#' + uid + '_result_div_inner').html('<div class=\"loading\">Searching ...</div>');
          ui_input_lookup_show_results(uid);

          let params = '';
          let form_data = ui_form_get_values($('#' + uid).attr('data-form-uid'));

          params += '&model=' + encodeURIComponent($('#' + uid).attr('data-model'));
          params += '&initiating_model=' + encodeURIComponent($('#' + uid).attr('data-initiating-model'))
          params += '&initiating_column=' + encodeURIComponent($('#' + uid).attr('data-initiating-column'))
          params += '&form_data=' + encodeURIComponent(JSON.stringify(form_data))
          params += '&value=' + encodeURIComponent(requested_value);

          var used_values = {};
          if ($('#' + uid).attr('data-table-input') == 1) {
            $('#' + uid + '_table_autocomplete_items div').each(function () {
              used_values[$(this).attr('data-id')] = 1;
            });
          };

          _ajax_read('UI/Input/Autocomplete', params, function (result) {
            var tmp_html = '';
            var cnt = 0;
            var tmp_id = 0;
            var tmp_text = '';

            for (var i in result) {
              tmp_id = result[i][0];
              tmp_text = result[i][1];

              if (!used_values[tmp_id]) {
                tmp_html += '<a class="item" data-id="' + tmp_id + '" href="javascript:void(0);" onclick="_lkp_block_onblur = true; ui_input_lookup_set_value(\'' + uid + '\', ' + tmp_id + ', \'' + encodeURIComponent(tmp_text.replace('\'', '\\\'')) + '\'); ui_input_lookup_hide_results(\'' + uid + '\');">' + tmp_text + '</a>';
                cnt++;
              };
            };

            if (cnt == 1 && !used_values[tmp_id]) {
              ui_input_lookup_set_value(uid, tmp_id, tmp_text);
              ui_input_lookup_hide_results(uid);
            } else {
              if (tmp_html != '') {
                $('#' + uid + '_result_div_inner').html(tmp_html);
                ui_input_lookup_set_next_item(uid);
              } else {
                $('#' + uid + '_result_div_inner').html('<a class="item" style="color:#bbbbbb;">No records found</a>');
              };
            };


          });

        }
      } else {
        // nic
      };
    }, search_timeout);
  };
};

function ui_input_lookup_set_pos(uid) {

  // pos = $('#'+uid+'_autocomplete_input').offset();

  // $('#'+uid+'_result_div').css('left', pos.left - $(window).scrollLeft());
  // $('#'+uid+'_result_div').css('top', (pos.top+parseInt($('#'+uid+'_autocomplete_input').outerHeight()-$(window).scrollTop(), 10) + 1));
  // $('#'+uid+'_result_div').css('width', (parseInt($('#'+uid+'_autocomplete_input').outerWidth(), 10)));

};

function ui_input_lookup_show_results(uid) {
  $(document).on('click.' + uid + '_docclick', function () { ui_input_lookup_hide_results(uid); });
  $('#' + uid + '_result_div').show();
  $(document).on('scroll.input_lookup_results_shift', function () { ui_input_lookup_set_pos(uid); });
};

function ui_input_lookup_hide_results(uid) {
  $(document).off('click.' + uid + '_docclick');
  $('#' + uid + '_result_div_inner').html('');
  $('#' + uid + '_result_div').hide();
  $(document).off('scroll.input_lookup_results_shift');
};

function ui_input_lookup_set_value(uid, id, text) {

  // ak input uz neexistuje, nerobi sa nic>
  if (!($('#' + uid).length > 0)) return '';

  if (text == '' || typeof text == 'undefined') text = '';
  // text sa nacitava vzdy kvoli tomu, ze v html je po oprave htmlspecialchars hodnota v pripade pouzitych tagov
  if (id > 0) text = ui_input_lookup_get_text_input(id, uid);



  if (id > 0) {
    $('#' + uid + '_clear_button').show();
    $('#' + uid + '_detail_button').show();
    $('#' + uid + '_add_button').hide();
  } else {
    $('#' + uid + '_clear_button').hide();
    $('#' + uid + '_detail_button').hide();
    $('#' + uid + '_add_button').show();
  };

  if ($('#' + uid).attr('data-table-input') == 1) {

    $('#' + uid + '_autocomplete_input').val('');
    $('#' + uid + '_autocomplete_input').attr('data-value', '');
    $('#' + uid + '_add_button').show();

    if (id > 0) {
      if ($('#' + uid + '_controls_template').length > 0) tmptpl = $('#' + uid + '_controls_template').html().replace(/{%ID%}/g, id);
      else tmptpl = '';
      $('#' + uid + '_table_autocomplete_items').append("<div data-id='" + id + "' >" + (text).replace(/</g, "&lt;").replace(/>/g, "&gt;") + tmptpl + "</div>");
      ui_input_table_collect_boxes(uid);
      $('#' + uid + '_autocomplete_input').focus();
    };

    $('#' + uid).trigger('onchange');

  } else {
    $('#' + uid + '_autocomplete_input').val(text);
    $('#' + uid + '_autocomplete_input').attr('data-value', $('#' + uid + '_autocomplete_input').val());
    var tmp = $('#' + uid).val();
    $('#' + uid).val(id);

    if (id != tmp) $('#' + uid).trigger('onchange');

  };
};

function ui_input_lookup_clear(uid) {
  ui_input_lookup_set_value(uid, 0, '');
};

function ui_input_lookup_get_text_input(id, uid) {
  return _ajax_sread('UI/Input/AutocompleteGetItemText',
    '&model=' + encodeURIComponent($('#' + uid).attr('data-model'))
    + '&initiating_model=' + encodeURIComponent($('#' + uid).attr('data-initiating-model'))
    + '&initiating_column=' + encodeURIComponent($('#' + uid).attr('data-initiating-column'))
    + '&value=' + id
  );
};

function ui_input_file_download(uid, text) {

  _prompt(text, { title: 'Download' }, function (file_url) {

    if (file_url) {

      $('#' + uid + '_info_div').css('display', 'inline-block');
      $('#' + uid + '_operations').hide();
      $('#' + uid + '_file').css('opacity', 0.4);

      $.ajax({
        url: $('#' + uid).attr('data-upload-url'),
        type: 'post',
        data: { download_file: file_url }
        //processData: false,  // tell jQuery not to process the data
        //contentType: false   // tell jQuery not to set contentType
      }).done(function (data) {
        var res = jQuery.parseJSON(data);
        //window.console.log(res);
        if (res.uploaded == 1) {
          $('#' + uid).val(res.file_path);
          $('#' + uid + '_file').html((res.fileName.length > 75 ? res.fileName.substr(0, 75) + '...' : res.fileName));
          $('#' + uid + '_info_div').hide();
          $('#' + uid + '_operations').show();
          $('#' + uid + '_file_input_delete_button').show();
          $('#' + uid + '_file_input_open_button').show();
          $('#' + uid + '_file').css('opacity', 1);
          $('#' + uid).trigger('onchange');
        } else {
          $('#' + uid + '_info_div').hide();
          $('#' + uid + '_operations').show();
          $('#' + uid + '_file').css('opacity', 1);
          $('#' + uid).val('');
          _alert(res.error.message);
        };
      });
    };
  });

};

function ui_input_image_download(uid, text) {

  _prompt(text, { title: 'Download' }, function (file_url) {

    if (file_url) {

      $('#' + uid + '_info_div').css('display', 'inline-block');
      $('#' + uid + '_operations').hide();
      $('#' + uid + '_file').css('opacity', 0.4);

      $.ajax({
        url: $('#' + uid).attr('data-upload-url'),
        type: 'post',
        data: { download_file: file_url }
        //processData: false,  // tell jQuery not to process the data
        //contentType: false   // tell jQuery not to set contentType
      }).done(function (data) {
        var res = jQuery.parseJSON(data);
        if (res.uploaded == 1) {
          $('#' + uid).val(res.file_path);
          $('#' + uid + '_image').attr('src', $('#' + uid).attr('data-src-base') + encodeURI(res.file_path)).one('load', function () {
            $('#' + uid + '_info_div').hide();
            $('#' + uid + '_operations').show();
            $('#' + uid + '_file_input_delete_button').show();
            $('#' + uid + '_file_input_open_button').show();
            $('#' + uid + '_image').css('opacity', 1);
            $('#' + uid).trigger('onchange');
          });
        } else {
          $('#' + uid + '_info_div').hide();
          $('#' + uid + '_operations').show();
          $('#' + uid + '_image').css('opacity', 1);
          $('#' + uid).val('');
          _alert(res.error.message);
        };
      });
    };

  });

};

function ui_input_lookup_detail(id, uid) {
  window_render(
    'UI/Form',
    {
      model: $('#' + uid).attr('data-model'),
      id: id,
      // edit_form_type: 'lookup_form'
    },
    function (res) {
      ui_input_lookup_set_value(uid, id, '');
    }
  );
};

function ui_input_lookup_search(inputUid) {
  let form_data = ui_form_get_values($('#' + inputUid).attr('data-form-uid'));
  console.log(form_data);

  window_render(
    'UI/Input/LookupSearch',
    {
      model: $('#' + inputUid).attr('data-model'),
      inputUid: inputUid,
      model: $('#' + inputUid).attr('data-model'),
      initiating_model: $('#' + inputUid).attr('data-initiating-model'),
      initiating_column: $('#' + inputUid).attr('data-initiating-column'),
      form_data: JSON.stringify(form_data),
    }
  );
};

function ui_input_lookup_add(uid) {
  window_render('UI/Form', { table: $('#' + uid).attr('data-table'), id: -1, onaftersave: "ui_input_lookup_set_value('" + uid + "', data.inserted_id, '');" });
};

function ui_input_table_collect_checkboxes(uid) {
  var ids = '';
  $('#' + uid + '_lookup_checkbox_list input').each(function () {
    if ($(this).is(':checked')) {
      ids += $(this).attr('data-id') + ',';
    };
  });
  var tmp = $('#' + uid).val();
  $('#' + uid).val(ids);
  if (ids != tmp) $('#' + uid).trigger('onchange');
};

function ui_input_table_collect_boxes(uid) {
  var ids = '';
  $('#' + uid + '_table_autocomplete_items div').each(function () {
    ids += $(this).attr('data-id') + ',';
  });
  $('#' + uid).val(ids);
  setTimeout(function () { $('#' + uid).trigger('onchange'); }, 0);
};

function ui_input_table_remove_item(uid, id) {
  if (!$('#' + uid + '_table_autocomplete_items').hasClass('readonly')) {
    $('#' + uid + '_table_autocomplete_items div').each(function () {
      if ($(this).attr('data-id') == id) $(this).remove();
    });
    ui_input_table_collect_boxes(uid);
    $('#' + uid).trigger('onchange');
  };
};

function ui_input_ftp_browser(uid, type) {
  window_render('UI/Input/ftp_browser', $('#' + uid).attr('data-upload-params') + '&type=' + type + '&input_uid=' + uid, function (res) { });
};

function ui_input_table_add_value(uid, text) {
  if (text != '') {
    var add_index = parseInt($('#' + uid).attr('data-add-index')) - 1;
    $('#' + uid).attr('data-add-index', add_index);
    add_index = add_index + '|||' + encodeURIComponent(text);
    $('#' + uid + '_table_autocomplete_items').append("<div data-id='" + add_index + "' onclick='ui_input_table_remove_item(\"" + uid + "\", \"" + add_index + "\"); ' >" + (text).replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</div>");
    ui_input_table_collect_boxes(uid);
    $('#' + uid + '_autocomplete_input').focus();
  }
};

function ui_input_table_detail(id, uid) {
  window_render('UI/Form', { table: $('#' + uid).attr('data-table'), id: id, edit_form_type: 'lookup_form' }, function (res) { ui_input_table_remove_item(uid, id); ui_input_lookup_set_value(uid, id, ''); });
};

// function ui_input_check_float_number(input, decimals){
//   if(!input.attr('data-title')) input.attr('data-title', input.attr('title'));
//   input.val(input.val().replace(/\s/g,''));
//   if (isNaN(parseFloat(input.val().replace(',', '.')))){
//     if (input.val() != ''){
//       input.addClass('invalid_value');
//       input.attr('title', error);
//     }else{
//       input.removeClass('invalid_value');
//       input.attr('title', input.attr('data-title'));
//     };
//   }else{
//     input.removeClass('invalid_value');
//     input.val(parseFloat(input.val().replace(',', '.')).toFixed(decimals));
//     input.attr('title', input.attr('data-title'));
//   };
// }

// function ui_input_check_number(input, min, max, error){

//   if(!input.attr('data-title')) input.attr('data-title', input.attr('title'));
//   input.val(input.val().replace(/\s/g,''));
//   if (isNaN(parseInt(input.val().replace(',', '.')))){
//     if (input.val() != ''){
//       input.addClass('invalid_value');
//       input.attr('title', error);
//     }else{
//       input.removeClass('invalid_value');
//       input.attr('title', input.attr('data-title'));
//     };
//   }else{
//     input.removeClass('invalid_value');
//     input.val(parseInt(input.val().replace(',', '.')));
//     input.attr('title', input.attr('data-title'));

//     if (min != ''){
//       if (input.val() < min){
//         input.addClass('invalid_value');
//         input.attr('title', 'min '+min);
//       }
//     }

//     if (max != ''){
//       if (input.val() > max){
//         input.addClass('invalid_value');
//         input.attr('title', 'max '+max);
//       }
//     }
//   }
// }

function ui_input_table_select_all(uid) {
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').prop('checked', true);
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').first().trigger('change');
};

function ui_input_table_deselect_all(uid) {
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').prop('checked', false);
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').first().trigger('change');
};

function ui_input_table_invert_selection(uid) {
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').each(
    function () {
      if ($(this).prop('checked')) $(this).prop('checked', false);
      else $(this).prop('checked', true);
    },
  );
  $('#' + uid + '_lookup_checkbox_list input[type=checkbox]').first().trigger('change');
};
