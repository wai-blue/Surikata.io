
  function ui_treemenu_node_click(uid, id){
    ui_treemenu_save_node_status(uid, id);
    if ($('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').is(':visible')) ui_treemenu_collapse_node(uid, id);
    else ui_treemenu_expand_node(uid, id);
  }

  function ui_treemenu_expand_node(uid, id, now){
    if (now) $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').show();
    else $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').slideDown();
    $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>.treemenu_treeicon .plus_icon').hide();
    $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>.treemenu_treeicon .minus_icon').show();
  }

  function ui_treemenu_collapse_node(uid, id, now){
    if (now) $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').hide();
    else $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').slideUp();
    $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>.treemenu_treeicon .plus_icon').show();
    $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>.treemenu_treeicon .minus_icon').hide();
  }

  function ui_treemenu_set_active(uid, id){
    $('#'+uid+' .treemenu_item').removeClass('active_item');
    $('#'+uid+' #'+uid+'_treemenu_item_'+id).addClass('active_item');
  };

  function ui_treemenu_scroll_to_active(uid){

    el = false;
    if (ui_treemenu_has_scrollbar($('#'+uid))) el = $('#'+uid);
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent())) el = $('#'+uid).parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent())) el = $('#'+uid).parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent())) el = $('#'+uid).parent().parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent().parent())) el = $('#'+uid).parent().parent().parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent().parent().parent())) el = $('#'+uid).parent().parent().parent().parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent().parent().parent().parent())) el = $('#'+uid).parent().parent().parent().parent().parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent().parent().parent().parent().parent())) el = $('#'+uid).parent().parent().parent().parent().parent().parent().parent();
    else if (ui_treemenu_has_scrollbar($('#'+uid).parent().parent().parent().parent().parent().parent().parent().parent())) el = $('#'+uid).parent().parent().parent().parent().parent().parent().parent().parent();

    // if (el) el.scrollTop($('#'+uid+' .treemenu_item.active_item').offset().top-($(window).height()/2));

    var active_item = $('#'+uid+' .treemenu_item.active_item');
    var li = active_item.closest('li');
    var ul = li.closest('ul');
    var top = 0;
    var i = 0;
    while (ul.closest('li').hasClass('treemenu_li') && i < 100) {
      top += ul.position().top + li.position().top;
      li = ul.closest('li');
      ul = li.closest('ul');
      i++;
    }

    if (el) el.scrollTop(top - el.height() / 4);
  };

  function ui_treemenu_has_scrollbar(el){

    if (el.get(0).tagName == 'BODY') return true;
    else return el.get(0) ? el.get(0).scrollHeight > el.innerHeight() : false;
  };

  function ui_treemenu_activate_state(uid){
    tmp_state_session = localStorage.getItem(uid+'_state_session');

    if (typeof(treemenu_state_session) == 'undefined') document.treemenu_state_session = [];

    if (tmp_state_session == null || tmp_state_session == ''){
      document.treemenu_state_session[uid] = {};
      localStorage.setItem(uid+'_state_session', '');
    }else{
      document.treemenu_state_session[uid] = JSON.parse(tmp_state_session);
      for (key in document.treemenu_state_session[uid]) {
        if (document.treemenu_state_session[uid][key]){
          ui_treemenu_expand_node(uid, key, 1);
        }else{
          ui_treemenu_collapse_node(uid, key, 1);
        };
      }
    };
  };

  function ui_treemenu_save_node_status(uid, id){
    if (typeof(document.treemenu_state_session) !== 'undefined'){
      if (typeof(document.treemenu_state_session[uid]) !== 'undefined'){
        document.treemenu_state_session[uid][id] = ($('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul').is(':visible') ? 0 : 1);
        localStorage.setItem(uid+'_state_session', JSON.stringify(document.treemenu_state_session[uid]));
      };
    };
  };

  function ui_treemenu_show_active_tree(uid, id){

    if (typeof(id) == 'undefined'){
      par = $('#'+uid+' .treemenu_item.active_item').parent().parent();
    }else{
      par = id.parent().parent();
    };

    if (par.length > 0){
      if (par.get(0).tagName == 'UL' && par.hasClass('treemenu_level')){

        // kvoli vyhladavaniu dava show, inak zbytocny
        par.parent().show();
        ui_treemenu_expand_node(uid, par.parent().find('>.treemenu_item').attr('data-item-id'), 1);
        ui_treemenu_show_active_tree(uid, par);
      };
    };


  };

  function ui_treemenu_expand_all(uid){
    $('#'+uid+' ul.treemenu_level').each(function(){
      ui_treemenu_expand_node(uid, $(this).parent().find('>.treemenu_item').attr('data-item-id'));
    });
  }

  function ui_treemenu_collapse_all(uid){
    $('#'+uid+' ul.treemenu_level').each(function(){
      ui_treemenu_collapse_node(uid, $(this).parent().find('>.treemenu_item').attr('data-item-id'));
    });
  }

  function ui_treemenu_filter(uid, value){
    if (value != '') $('#'+uid+'_clear_filter').show();
    else $('#'+uid+'_clear_filter').hide();

    value = rmdiacritic(value).toLowerCase();

    $('#'+uid+'_treemenu_filter').val(value);
    $('#'+uid+' .treemenu_item').removeClass('searched');
    if (value != ''){
      $('#'+uid+' .treemenu_li').hide();
      $('#'+uid+' .treemenu_item').each(function(){


        if (rmdiacritic($(this).html()).toLowerCase().indexOf(value) != -1){

        //if (stristr((), value)){
          $(this).addClass('searched');
          $(this).parent().show();
          ui_treemenu_show_active_tree(uid, $(this));
        }
      });
    }else{
      $('#'+uid+' .treemenu_li').show();
      ui_treemenu_collapse_all(uid);
      setTimeout(function(){ ui_treemenu_activate_state(uid); }, 300);
    };

  };

  // tento kod sposobuje, ze vyssie pouzity contanis funguje ako case insensitive
  jQuery.expr[':'].ContainsInsensitive = function(a, i, m) {
    return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
  };


  function ui_treemenu_refresh(uid, params){
    if ($('#'+uid).length){

      if (typeof params == 'undefined') params = {};
      action = $('#'+uid).attr('data-refresh-action');
      params.uid = uid;
      params.treemenu_refresh = 1;
      _ajax_update(action, params, uid);
    };
  };

  function ui_treemenu_move_node(uid, from, to, from_node, to_node){
    if (from > 0 && to > 0){
      var tmp_confirm_text = $('#'+uid).attr('data-draggable-confirm');

      if (tmp_confirm_text != ''){
        if (confirm(tmp_confirm_text)) ui_treemenu_refresh(uid, {move_node_from: from, move_node_to: to});
      }else{
        ui_treemenu_refresh(uid, {move_node_from: from, move_node_to: to});
      };
    };
  };

  function ui_treemenu_hover_expand_node(uid, id){
    var el = $('#'+uid+' #'+uid+'_treemenu_item_'+id).parent().find('>ul');
    if (el.length){
      ui_treemenu_save_node_status(uid, id);
      ui_treemenu_expand_node(uid, id);
    };
  };