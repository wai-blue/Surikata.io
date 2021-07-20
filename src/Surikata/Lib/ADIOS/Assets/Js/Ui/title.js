
function ui_title_show_menu_left(el){

    if ($(el).parent().parent().find('.right_menu button').hasClass('active')){
      $(el).parent().parent().find('.right_menu button').trigger('click');
    }

    $(el).parent().parent().find('.title_left_panel').fadeToggle();
    $(el).toggleClass('active');
  }

function ui_title_show_menu_right(el){

    if ($(el).parent().parent().find('.left_menu button').hasClass('active')){
      $(el).parent().parent().find('.left_menu button').trigger('click');
    }
    $(el).parent().parent().find('.title_right_panel').fadeToggle();
    $(el).toggleClass('active');

  }

function ui_title_check_wrap(){

  $('.title').each(function(){

      $(this).removeClass('wrapped_left');
      $(this).removeClass('wrapped_right');

      // zrusi display z inline css ak bolo kliknute tlacitko pre zobrazenie a skrytie menu (ma zmysel len pri resizovani okna a klikani na zobrazenie menu)
      $(this).find('.title_left_panel').css('display', '');
      $(this).find('.title_right_panel').css('display', '');
      $(this).find('.left_menu').find('button').removeClass('active');
      $(this).find('.right_menu').find('button').removeClass('active');

      var center_width = $(this).find('.title_center').width();
      var left_width = $(this).find('.title_left_panel').width();
      var right_width = $(this).find('.title_right_panel').width();
      var back_width = ($(this).find('.back_button').length ? $(this).find('.back_button').width() : 0);
    var all_width = $(this).find('.title_wrapper').width();

      if (((center_width/2)+back_width+left_width+30) > all_width/2 && center_width > 0){
        $(this).addClass('wrapped_left');
      }else{
        $(this).removeClass('wrapped_left');
      };

      if (((center_width/2)+right_width+30) > all_width/2 && center_width > 0){
        $(this).addClass('wrapped_right');
      }else{
        $(this).removeClass('wrapped_right');
      };

      if (center_width == 0){
        if (right_width + left_width + back_width > all_width){
          $(this).addClass('wrapped_right');
          if (40 + left_width + back_width > all_width){
            $(this).addClass('wrapped_left');
          }else{
            $(this).removeClass('wrapped_left');
          };
        }else{
          $(this).removeClass('wrapped_right');
          $(this).removeClass('wrapped_left');
        };
      };


      //window.console.log('nadpis polka '+(center_width/2));
      //window.console.log('back '+(back_width));
      //window.console.log('left '+(left_width));
      //window.console.log('polka cele'+all_width/2);

    });

  };

  $(window).resize(function() {
    ui_title_check_wrap();
  });

function ui_title_set_browser_title(title) {
    document.title = title;
  }
