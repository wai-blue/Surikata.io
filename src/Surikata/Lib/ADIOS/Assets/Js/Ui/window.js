
  function ui_window_close(uid, quiet){
    if (typeof quiet == 'undefined') quiet = false;
    if ($.window.getWindow(uid)) $.window.getWindow(uid).close(quiet);
  };

  function ui_window_refresh(uid){
    var name = uid + '_refresh_window';

    if (typeof window[name] == 'function') {
      window[name]();
    } else {
      _adios_console_log('ajax window', 'refresh function not allowed: '+name);
    }
  };

  function ui_window_count_windows(){
    return $.window.getAll().length;
  };

  function ui_window_get_selected(){
    if (typeof $.window.getSelectedWindow() != 'undefined') return $.window.getSelectedWindow().getWindowId();
    else return 0;
  };

  function ui_window_set_tab_height(uid){
    setTimeout( function(){
      if ($('#'+uid+' .window_frame > .adios.ui.Form > .adios.ui.Tabs .tab_titles').length){
        fh = parseInt(Math.ceil($('#'+uid+' .window_frame').height()));
        th = parseInt(Math.ceil($('#'+uid+' .window_frame > .adios.ui.Form > .adios.ui.Tabs .tab_titles').outerHeight(true)));
        befh = ($('#' + uid + ' .window_frame > .adios.ui.Form > .adios.ui.Form.default_table_wrapper').length ? parseInt(Math.ceil($('#' + uid +' .window_frame > .adios.ui.Form > .adios.ui.Form.default_table_wrapper').outerHeight(true))) : 0);
        $('#' + uid +' .window_frame > .adios.ui.Form > .adios.ui.Tabs .tab_contents').css('height', fh - th - befh);
      }
      if ($('#'+uid+' .window_frame > .adios.ui.Tabs .tab_titles').length){
        fh = parseInt(Math.ceil($('#'+uid+' .window_frame').height()));
        th = parseInt(Math.ceil($('#'+uid+' .window_frame > .adios.ui.Tabs .tab_titles').outerHeight(true)));
        $('#'+uid+' .window_frame > .adios.ui.Tabs .tab_contents').css('height', fh - th);
      }

    }, 100);
  }
