
  function ui_button_dropdown_show(uid){
    $('#'+uid+'_dropdown').fadeIn();
    $('#'+uid+'_dropdown').css('min-width', $('#'+uid).outerWidth());
    $('#'+uid).attr('data-dropdown-hide-timeout', 0);
    $(document).on('scroll.button_dropdown_scroll', function () {
      $('.adios.ui.button_dropdown').hide();
    });

    var left = 0;
    var top = 0;

    if ($('#'+uid+'_dropdown').hasClass('top_left')){
      left = $('#'+uid).offset().left-$(window).scrollLeft()+$('#'+uid).outerWidth()-$('#'+uid+'_dropdown').outerWidth();
      top = $('#'+uid).offset().top-$('#'+uid+'_dropdown').outerHeight()-$(window).scrollTop();
    }else if ($('#'+uid+'_dropdown').hasClass('top_right')){
      left = $('#'+uid).offset().left-$(window).scrollLeft();
      top = $('#'+uid).offset().top-$('#'+uid+'_dropdown').outerHeight()-$(window).scrollTop();
    }else if ($('#'+uid+'_dropdown').hasClass('bottom_left')){
      left = $('#'+uid).offset().left-$(window).scrollLeft()+$('#'+uid).outerWidth()-$('#'+uid+'_dropdown').outerWidth();
      top = $('#'+uid).offset().top+$('#'+uid).outerHeight()-$(window).scrollTop();
    }else{
      left = $('#'+uid).offset().left-$(window).scrollLeft();
      top = $('#'+uid).offset().top+$('#'+uid).outerHeight()-$(window).scrollTop();
    }
    $('#'+uid+'_dropdown').css('left', left);
    $('#'+uid+'_dropdown').css('top', top);

    $('#'+uid).addClass('active');


  };

  function ui_button_dropdown_hide(uid){
    $('#'+uid).attr('data-dropdown-hide-timeout', 1);
    setTimeout(function(){
      if ($('#'+uid).attr('data-dropdown-hide-timeout') == 1){
        $('#'+uid+'_dropdown').fadeOut(100);
        $(document).off('scroll.button_dropdown_scroll');
        $('#'+uid).removeClass('active');
      };
      $('#'+uid).attr('data-dropdown-hide-timeout', 0);
    }, 100);
  };