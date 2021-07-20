
  function ui_tabs_change_tab(uid, tab) {
    let scrollTo = $('#' + uid + ' .tab_content_' + tab);
    let container = $('#' + uid + ' .tab_contents');

    container.find('.tab_title_tag').removeClass('active');
    scrollTo.find('.tab_title_tag').addClass('active');

    setTimeout(function () {
      scrollTo.find('.tab_title_tag').removeClass('active');
    }, 500);

    container.animate({
      scrollTop:
        scrollTo.offset().top
        - parseInt(scrollTo.css('margin-top'))
        - container.offset().top + container.scrollTop()
    }, 300, function() {
      $('#'+uid+' .tab_title').blur().removeClass('active').removeClass('active');
      $('#'+uid+' .tab_title_'+tab).addClass('active');

      container.find('.tab_content').removeClass('active');
      scrollTo.addClass('active');
    });
  };

  function ui_tab_load_content(el_id, action, params){

    if ($(el_id).attr('data-content-loaded') != 1 && $(el_id).length){
      setTimeout(function(){
        $(el_id).attr('data-content-loaded', 1);
        _ajax_supdate(action, params, el_id);
      }, 200);
    };
  };