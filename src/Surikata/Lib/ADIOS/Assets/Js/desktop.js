  var ADIOS_windows = {};
  var desktop_main_box_history = [];
  var desktop_keyboard_shortcuts = [];

  var adios_menu_hidden = 0;

  function window_render(action, params, onclose, no_history) {
    if (typeof params == 'undefined') params = {};
    if (typeof options == 'undefined') options = {};

    params.__IS_WINDOW__ = '1';

    setTimeout(function() {
      _ajax_read(action, params, function(html) {
        if (typeof options.onafter == 'function') options.onafter(action, params);

        $('#adios_main_content').append(html);

        console.log();

        if ($('#adios_main_content .adios_window').length == 1) {
          desktop_main_box_history_push(action, params, $('#adios_main_content').html(), options);
        }

        let last_win = $('#adios_main_content .adios_window').last();
        window_id = last_win.attr('id');
        ADIOS_windows[window_id] = {
          'action': action,
          'params': params,
          'onclose': onclose,
        };

      });

    }, 0);
  };

  function window_refresh(window_id) {
    let win = $('#' + window_id);

    win.remove();
    window_render(
      ADIOS_windows[window_id]['action'],
      ADIOS_windows[window_id]['params'],
      ADIOS_windows[window_id]['onclick'],
    );
  }

  function window_close(window_id, oncloseParams) {
    if (!ADIOS_windows[window_id]) {
      // okno bolo otvarane cez URL
      window.location.href = _APP_URL;
    } else {
      if ($('#adios_main_content .adios_window').length == 1) {
        window.history.back();
      }

      $('#'+window_id).remove();

      if (typeof ADIOS_windows[window_id]['onclose'] == 'function') {
        ADIOS_windows[window_id]['onclose'](oncloseParams);
      }

    }

  }

  function desktop_update(action, params, options){
    desktop_render(action, params, options);
  };

  function desktop_render(action, params, options) {
    if (typeof params == 'undefined') params = {};
    if (typeof options == 'undefined') options = {};

    $('#adios_main_content').css('opacity', 0.5);

    window.location.href = _APP_URL + '/' + _action_url(action, params, true);

  }

  function desktop_register_shortcut(shortcut, title, callback, options) {
    // The valid Keys are...

    // All alpha/numeric keys - abc...xyz,01..89
    // Special Characters - Every special character on a standard keyboard can be accessed.
    // Special Keys...
    // Tab
    // Space
    // Return
    // Enter
    // Backspace
    // Scroll_lock
    // Caps_lock
    // Num_lock
    // Pause
    // Insert
    // Home
    // Delete
    // End
    // Page_up
    // Page_down
    // Left
    // Up
    // Right
    // Down
    // F1
    // F2
    // F3
    // F4
    // F5
    // F6
    // F7
    // F8
    // F9
    // F10
    // F11
    // F12
    // These keys are case insensitive - so don't worry about using the correct case.

    if (typeof options == 'undefined') options = {};
    if (typeof options.target == 'undefined') options.target = document;
    if (typeof options.type == 'undefined') options.type = 'keydown';
    if (typeof options.propagate == 'undefined') options.propagate = false;
    if (typeof options.disable_in_input == 'undefined') options.disable_in_input = true;

    _keyboard_shortcut.add(shortcut, function() {
      var notification_obj = $('#w_swi_desktop_toolbar .settings-menu .user-info');
      var tmp_html = notification_obj.html();
      notification_obj.html('<span style="font-size:14px">' + title + '</span>');

      setTimeout(function() {
        callback();
        notification_obj.html(tmp_html);
      }, 100);
    }, options);

    desktop_keyboard_shortcuts.push({'shortcut': shortcut, 'title': title, 'callback': callback, 'options': options});
  };

  function _desktop_register_shortcut(shortcut, title, callback, options) {
    desktop_register_shortcut(shortcut, title, callback, options);
  };

  function desktop_unregister_shortcut(shortcut) {
    var tmp = [];
    for (var i in desktop_keyboard_shortcuts) {
      if (desktop_keyboard_shortcuts[i].shortcut == shortcut) {
        _keyboard_shortcut.remove(shortcut);
      } else {
        tmp.push(desktop_keyboard_shortcuts[i]);
      };
    };
    desktop_keyboard_shortcuts = tmp;
  };

  function _desktop_unregister_shortcut(shortcut) {
    desktop_unregister_shortcut(shortcut);
  };


  function desktop_main_box_history_push(action, params, html, options) {
    if (typeof options != 'object') options = {};

    window.history.pushState(
      {
        "html": html,
        "pageTitle": document.title,
      },
      "",
      _APP_URL + '/' + _action_url(action, params, true)
    );

  };

  window.onpopstate = function (e) {
    if (e.state) {
      document.title = e.state.pageTitle;
    }
  };


  function desktop_notify_widget(widget, params){
    if (typeof params == 'undefined') params = {};
    $('.app-icon.'+widget+' button').on('fade-cycle', function() {
      $(this).fadeOut('slow', function() {
        $(this).fadeIn('slow', function() {
          $(this).trigger('fade-cycle');
        });
      });
    });
    if (!params['do_not_stop_onclick']){
      $('.app-icon.'+widget).click(function(){
        $('.app-icon.'+widget+' button').off('fade-cycle');
      });
    };
    $('.app-icon.'+widget+' button').trigger('fade-cycle');
  };

  function _desktop_notify_widget(widget, params){
    desktop_notify_widget(widget, params);
  };

  function desktop_show_settings(params) {
    if ($('#adios_shortcuts_items_adios_settings').length > 0){
      if (typeof params == 'undefined') params = '';
      desktop_update('administrator/users/table', params);
    };
  };

  function desktop_default_user_profile_edit(id){
    window_render('desktop/simple_profile');
  };

  function desktop_show_console(){
    $('#adios_console_content').show();
    $('#adios_console').fadeIn();
    $('#adios_console').css('margin-right', 'auto');
    $('#adios_console').removeClass('minimized');
  };

  function desktop_toggle_console(){
    if ($('#adios_console_content').is(':visible') && $('.adios_notifications').position().left > 0){
      $('#adios_console').addClass('minimized');
      $('#adios_console').css('margin-right', ($('body').width()-$('.adios_notifications').position().left));
      Cookies.set('adios_console_state', 'min', 1);
    }else{
      $('#adios_console').css('margin-right', 'auto');
      $('#adios_console').removeClass('minimized');
      Cookies.set('adios_console_state', 'max', 1);
    };
    $('#adios_console_content').toggle();
  };

  function desktop_hide_console(onafterhide){
    $('#adios_console').fadeOut(function() {
      if (typeof onafterhide == 'function') {
        onafterhide();
      }
    });
  };

  function desktop_clear_console(){
    desktop_hide_console(function() {
      $('#adios_console_content .adios_console_item').remove();
      _ajax_read('Desktop/Ajax/ClearConsole', '');
    });
  };

  function adios_console_log(header, message){
    _adios_console_log(header, message);
  };

  function _adios_console_log(header, message){
    if (!($('#adios_console').is(':visible'))){
      $('#adios_console').fadeIn();
      $('#adios_console_content').slideDown();
      // if (Cookies.get('adios_console_state') == 'min') desktop_toggle_console();
    };
    $('#adios_console_content').prepend("<div class='adios_console_item'><b>"+header+"</b><br/>"+message+"</div>");
    document.desktop_console_counter++;
    $('#adios_console').addClass('highlight');
    setTimeout(function(){$('#adios_console').removeClass('highlight');}, 1000);

  };

  function desktop_console_update() {
    if (_DEVEL_MODE) {
      _ajax_read('Desktop/Ajax/GetConsoleAndNotificationsContent', {}, function(res) {
        if (res['console']) {
          for (var i in res['console']) {
            var tmp_header = res['console'][i].header;
            var tmp_message = res['console'][i].message;
            _adios_console_log(tmp_header, tmp_message);
          }
        }

        if (res['notifications']) {
          for (var i in res['notifications']) {
            tmp['notifications'][i]['from_session'] = 1;
            desktop_notification(res['notifications'][i]);
          }
        }
      });
    }
  };

  function desktop_get_read_notifications(){
    _ajax_read('Desktop/Ajax/get_read_notifications', {}, function(res) {
      try {
        var notifications = JSON.parse(res);

        for (var i in notifications) {
          notifications[i]['from_session'] = 1;
          desktop_notification(notifications[i]);
        };

      } catch(ex) { window.console.log('adios notifications write error: '+res); };
    });
  };

  function desktop_update_scale(scale){
    $('.adios_zoom_image').hide();
    $('#adios_zoom_image_'+scale).show();
    document.querySelector("meta[name=viewport]").setAttribute(
          'content',
          'width=device-width, initial-scale='+(scale/100)+', maximum-scale='+(scale/100)+', user-scalable=0');
  };


  function _alert(text, params) {

    if (typeof params == 'undefined') params = {};

    if (params.title == '' || typeof params.title == 'undefined') params.title = 'Warning';
    if (params.modal == '' || typeof params.modal == 'undefined') params.modal = true;
    if (params.resizable == '' || typeof params.resizable == 'undefined') params.resizable = false;
    if (params.modal == '' || typeof params.modal == 'undefined') params.modal = true;
    if (params.width == '' || typeof params.width == 'undefined') params.width = 450;
    if (params.confirm_button_text == '' || typeof params.confirm_button_text == 'undefined') params.confirm_button_text = 'OK, I understand';
    if (params.cancel_button_text == '' || typeof params.cancel_button_text == 'undefined') params.cancel_button_text = 'Cancel';
      
    if (params.width > $(window).width()) params.width = $(window).width() - 20;
    if (params.buttons == '' || typeof params.buttons == 'undefined') {
      params.buttons = [
        {
          'text': params.confirm_button_text,
          'fa_icon': 'fas fa-check',
          'class': 'btn-primary ' + params.confirm_button_class,
          'onclick': function() {
            if (typeof params.onConfirm == 'function') params.onConfirm();
            $(this).closest('.adios.ui.window').remove();
          }
        }
      ];

      if (typeof params.onConfirm == 'function') {
        params.buttons.push({
          'text': params.cancel_button_text,
          'fa_icon': 'fas fa-times',
          'class': 'btn-secondary',
          'onclick': function () {
            $(this).closest('.adios.ui.window').remove();
          }
        });
      }
    }

    let buttons_html = '';
    for (let i in params.buttons) {
      let button = params.buttons[i];
      buttons_html += '<button type="button" class="btn ' + button.class + '" btn-index="' + i + '">';
      buttons_html += '<i class="' + button.fa_icon + ' mr-1"></i> ' + button.text;
      buttons_html += '</button>';
    }

    let html = '<div class="adios ui window modal">';
    html += '  <div class="modal-dialog shadow" role="document">';
    html += '      <div class="modal-content border-left-primary ' + params.content_class + '">';
    html += '          <div class="modal-header">';
    html += '              <h5 class="modal-title">' + params.title + '</h5>';
    html += '              <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
    html += '                  <span aria-hidden="true">&times;</span>';
    html += '              </button>';
    html += '          </div>';
    html += '          <div class="modal-body">';
    html += '              <p>' + text + '</p>';
    html += '          </div>';
    html += '          <div class="modal-footer">';
    html += buttons_html;
    html += '          </div>';
    html += '      </div>';
    html += '  </div>';
    html += '</div>';

    var window_div = $(html)
      .prependTo('body')
    ;

    $(window_div).find("button.close").bind('click', function() {
      $(this).closest('.adios.ui.window').remove();
    });

    $(window_div).find(".btn-primary").focus();

    for (let i in params.buttons) {
      $(window_div).find("button[btn-index='" + i + "']").bind('click', params.buttons[i].onclick)
    }
};

  function _confirm(text, params, callback) {
    params.title = 'Confirmation';
    params.onConfirm = callback;
    _alert(text, params);
  }

  function _prompt(text, params, callback) {
    params.title = 'Prompt';
    params.onConfirm = callback;

    if (params.use_textarea) text += "<br/><textarea style='width:95%;height:70px;' id='desktop_confirm_prompt_input'></textarea><br/>";
    else text += "<br/><input type='text' style='width:95%;' id='desktop_confirm_prompt_input' /><br/><br/>";

    params.buttons = [
        {
            'text': 'OK',
            'class': 'btn-primary',
            'onclick': function() {
                if (typeof params.onConfirm == 'function') params.onConfirm($('#desktop_confirm_prompt_input').val());
                $(this).closest('.adios.ui.window').remove();
            },
        },
        {
            'text': 'Zrušiť',
            'class': 'btn-secondary',
            'onclick': function () {
                $(this).closest('.adios.ui.window').remove();
            },
        }
    ];

    _alert(text, params);
  };



  
  function desktop_notification(data){
    //parametre: type, header, text, onclick, icon, seconds, audio, id, mandatory
    $('.adios_notifications #notifications_count').fadeIn();

    var clickable = 'clickable';
    var seconds = 5;
    var image = '';
    var el_id = '';
    var read = '';
    var mandatory = '';


    if (typeof data.onclick == 'undefined') data.onclick = '';
    if (typeof data.header == 'undefined') data.header = 'Header';
    if (typeof data.text == 'undefined') data.text = 'Text';
    if (typeof data.type == 'undefined') data.type = 'info';
    var d = new Date();
    if (typeof data.time == 'undefined') data.time = (d.getHours() < 10 ? '0'+d.getHours() : d.getHours())+':'+(d.getMinutes() < 10 ? '0'+d.getMinutes() : d.getMinutes());
    if (typeof data.audio == 'undefined') data.audio = $('.adios_notifications').attr('data-audio');;
    if (typeof data.from_session == 'undefined') data.from_session = false;
    if (typeof data.uid == 'undefined') data.uid = '';
    if (typeof data.read == 'undefined') data.read = '';
    if (typeof data.mandatory == 'undefined') data.mandatory = false;

    if (data.onclick != ''){
      clickable = 'clickable';
    };

    if (data.type == 'info'){
      image = $('.adios_notifications').attr('data-image-info');
      seconds = 3;
    }else if (data.type == 'warning'){
      seconds = 5;
      image = $('.adios_notifications').attr('data-image-warning');
    }else if (data.type == 'error'){
      seconds = 10;
      image = $('.adios_notifications').attr('data-image-error');
    }else{
      seconds = 2;
      image = $('.adios_notifications').attr('data-image-log');
    };

    if (typeof data.seconds == 'undefined')  data.seconds = seconds;
    if (typeof data.image == 'undefined')  data.image = image;

    if (data.read){
      read = 'read';
    };

    if (data.mandatory){
      mandatory = 'mandatory';
    };


    if (!data.from_session){
      _ajax_read('Desktop/Ajax/save_notification', data, function(res){
      });
    };

    if (data.uid != ''){
      el_id = 'adios_notification_id_'+data.uid;
      $('.'+el_id).remove();
    };

    var html = "<div class='notification_item "+data.type+" "+read+" "+mandatory+" "+clickable+" "+el_id+"' data-n-type=\""+data.type+"\" onclick=\""+data.onclick+" desktop_deactivate_notification(this); \" >"+
                 "<div class='n_header'>"+data.header+"</div>"+
                 "<div class='n_body'>"+data.text+"</div>"+
                 "<div class='n_time'>"+data.time+" "+(data.mandatory ? "<img src='"+$('.adios_notifications').attr('data-image-mandatory')+"' title='"+$('.adios_notifications').attr('data-image-mandatory-title')+"' />" : "")+"</div>"+
                 "<div class='n_icon'>"+
                  "<img src='"+data.image+"' class='menu_icon'>"+
                "</div>"+
              "</div>";



    if (!data.read){
      $('.adios_notifications #notifications_content').prepend(html);
      $('.adios_notifications #notifications_preview').prepend(html);
      $('.adios_notifications #notifications_preview .notification_item:first-child').hide();
      $('.adios_notifications #notifications_preview .notification_item:first-child').slideDown('fast', function(){ $(this).delay(data.seconds*1000).slideUp('slow', function(){ $(this).remove();}); });
    }else{
      $('.adios_notifications #notifications_content .end_identifier').before(html);
    };

    desktop_notifications_check_priority();

    if (!document.notification_sound_played && data.audio != ''){
      var audio = new Audio(data.audio) ;
      audio.play();
      document.notification_sound_played = 1;
      setTimeout(function(){ document.notification_sound_played = 0; }, 2000);
    };

    $('#notifications_count .count').html($('.adios_notifications #notifications_content .notification_item:not(.read)').length);


  }

  function _desktop_notification(data){
    desktop_notification(data);
  };

  function desktop_deactivate_notification(obj){

    $(obj).addClass('read');
    desktop_notifications_check_priority();
    $('#notifications_count .count').html($('.adios_notifications #notifications_content .notification_item:not(.read)').length);
    //$('#notifications_content').fadeOut();
  };

  function desktop_deactivate_notifications(){
    if(!document.notification_deactivate_blocked && $('#notifications_content').is(':visible')){
      desktop_notifications_check_priority();
      $('.adios_notifications .notification_item:not(.mandatory)').addClass('read');
      $('.adios_notifications .notification_item.mandatory:not(.read)').find('.n_time img').animate(
        {opacity: 1}, 200, function(){
          $(this).animate({opacity:0.3}, 200, function(){
            $(this).animate({opacity: 1}, 200, function(){
              $(this).animate({opacity:0.4}, 200);
            });
          });
        });

      $('.adios_notifications #notifications_preview .notification_item').hide();
      $('#notifications_count .count').html($('.adios_notifications #notifications_content .notification_item:not(.read)').length);
    };
  }

  function desktop_show_notifications(){
    document.notification_deactivate_blocked = 1;

    clearTimeout(document.show_notifications_timeout);
    $('#notifications_content').stop(true, true);
    $('#notifications_content').fadeIn();
    $('.adios_notifications .notifications_show_all').hide();
    // cely tento timeout je spraveny len kvoli mobilnym zariadeniam - aby sa pri kliknuti a prvom zobrazeni hned nedeaktivovali vsetky
    setTimeout(function(){ document.notification_deactivate_blocked = 0; }, 200);
    if ($('.adios_notifications #notifications_content .notification_item').length > 3){
      $('.adios_notifications #notifications_content .notification_item.read').hide();
      $('.adios_notifications #notifications_content .notification_item.read:nth-child(1)').show();
      $('.adios_notifications #notifications_content .notification_item.read:nth-child(2)').show();
      $('.adios_notifications #notifications_content .notification_item.read:nth-child(3)').show();
      if ($('.adios_notifications #notifications_content .notification_item:hidden').length > 0){
        $('.adios_notifications .notifications_show_all').show();
      };
    };

  }

  function desktop_hide_notifications(){
    $('.adios_notifications #notifications_preview .notification_item').hide();
    document.show_notifications_timeout = setTimeout(function(){ $('#notifications_content').stop(true, true); $('#notifications_content').fadeOut(); }, 200);
  };

  function desktop_show_all_notifications(){
    $('.adios_notifications #notifications_content .notification_item').show();
    $('.adios_notifications .notifications_show_all').hide();
  };

  function desktop_notifications_check_priority(){
    var not_class = 'log';
    $('.adios_notifications #notifications_content .notification_item').each(function(){
      if (!$(this).hasClass('read')){
        var type = $(this).attr('data-n-type');
        if (not_class == 'log'){
          not_class = type;
        }else if (not_class == 'info'){
          if (type != 'log') not_class = type;
        }else if (not_class == 'warning'){
          if (type == 'error') not_class = type;
        }else{
          if (type == 'error' || type == 'warning') not_class = type;
        };
      };
    });
      $('#notifications_count').removeClass();
      $('#notifications_count').addClass(not_class);

  };

  function desktop_delete_notification_history(){
    $('.adios_notifications #notifications_content .notification_item').remove();
    desktop_notifications_check_priority();
    $('#notifications_count .count').html($('.adios_notifications #notifications_content .notification_item:not(.read)').length);
    $('.adios_notifications #notifications_count').fadeOut();
    _ajax_read('Desktop/Ajax/clear_notifications', {});
  }


  $(document).ready(function(){
    //setInterval(function(){ desktop_console_update();}, 5000);
  });


  document.desktop_console_counter = 0;
  document.notification_deactivate_blocked = 0;

  desktop_register_shortcut('F10', 'Settings', function() { desktop_show_settings(); });


// vypnutie drag droup na plochu adios

$(document).ready(function(){
  var doc = document.documentElement;
  doc.ondragover = function () { this.className = 'hover'; return false; };
  doc.ondragend = function () { this.className = ''; return false; };
  doc.ondrop = function (event) {
    event.preventDefault && event.preventDefault();
    return false;
  };
});
