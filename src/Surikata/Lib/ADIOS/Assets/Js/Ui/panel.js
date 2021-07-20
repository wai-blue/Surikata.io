
  function ui_panel_hide_click(uid){

    var tmp_state = 'Y';
    if ($('#'+uid+' .panel_content').is(':hidden')){
      tmp_state = 'Y';
    }else{
      tmp_state = 'N';
    };

    $('#'+uid+' .panel_content').slideToggle();
    $('#'+uid+'_hide_button').toggle();
    $('#'+uid+'_unhide_button').toggle();

  };
