  var ui_table_params = {};
 

  function ui_table_settings_click(uid){

    if ($('#'+uid+' .settings_menu').is(':visible')){
      if ($('#'+uid+'_columns_sortable').attr('data-columns-was-sorted') == 1){
        var params = {set_col_order: ''};
        $('#'+uid+'_columns_sortable li').each(function(){
          var col_name = $(this).attr('data-col-name');
          if( $(this).find('button:visible').hasClass('active') ){
            params['set_col_visibility_'+col_name] = 1;
          }else{
            params['set_col_visibility_'+col_name] = 0;
          };
          params['set_col_order'] += (params['set_col_order'] == '' ? '' : ',')+col_name;
        });
        ui_table_refresh(uid, params);
      };
    };

    $('#'+uid+' .settings_menu').fadeToggle();
  };

  function ui_table_enable_column(uid, column){
    $('#'+uid+'_settings_column_'+column+'_disable').show();
    $('#'+uid+'_settings_column_'+column+'_enable').hide();
    $('#'+uid+'_columns_sortable').attr('data-columns-was-sorted', 1);
  };

  function ui_table_disable_column(uid, column){
    $('#'+uid+'_settings_column_'+column+'_disable').hide();
    $('#'+uid+'_settings_column_'+column+'_enable').show();
    $('#'+uid+'_columns_sortable').attr('data-columns-was-sorted', 1);
  };

  function ui_table_refresh(uid, params) {
    if ($('#'+uid).length) {

      if (typeof params == 'undefined') params = {};

      let refresh_action = $('#' + uid).attr('data-refresh-action');
      let action = (refresh_action == '' ? $('#' + uid).attr('data-action') : refresh_action);
      // let is_ajax = $('#' + uid).attr('data-is-ajax') == '1';
      let refresh_params = JSON.parse($('#' + uid).attr('data-refresh-params'));

      for (var i in params) {
        refresh_params[i] = params[i];
      }

      params = refresh_params;

      // params.table = $('#' + uid).attr('data-table');

      $('.' + uid + '_column_filter').each(function () {
        if ($(this).val() != '') {
          params['column_filter_' + $(this).attr('data-col-name')] = $(this).val();
        }
      });

      $('.' + uid + '_table_custom_filter_select').each(function () {
        if ($(this).val() != '') {
          var tmp = 'set_custom_filter_' + $(this).attr('data-filter-name');
          params[tmp] = $(this).val();
        }
      });

      params.refresh = 1;

      ui_table_params[uid] = params;

      _ajax_update(action, params, uid);
    }
  };

  function ui_table_export(uid, params){
    if (typeof params == 'undefined') params = {};
    action = $('#'+uid).attr('data-refresh-action');
    params.uid = uid;
    params.export_csv = 1;
    params.action = action;
    params.export_csv_ids = ui_table_get_selected(uid);
    _file_download(action, params);
  };

  function ui_table_refresh_by_model(model, params) {
    if (typeof params == 'undefined') params = {};

    $('.adios.ui.Table[data-model="' + model + '"]').each(function(){
      ui_table_refresh($(this).attr('id'), params);
    });
  };

  function ui_table_set_column_filter(uid, params) {
    // $('.'+uid+'_column_filter').each(function(){
    //   params['column_filter_'+$(this).attr('data-col-name')] = $(this).val();
    // });

    ui_table_refresh(uid, params);
  };

  function ui_table_show_page(uid, page){
    ui_table_refresh(uid, {page: page});
  };

  function ui_table_change_items_per_page(uid, count){
    ui_table_refresh(uid, {items_per_page: count});
  }

  function ui_table_select_all(uid){
    $('.'+uid+'_multiselect').prop('checked', true);
  };

  function ui_table_deselect_all(uid){
    $('.'+uid+'_multiselect').prop('checked', false);
  };

  function ui_table_invert_selection(uid){
    $('.'+uid+'_multiselect').each(function(){
      if ($(this).prop('checked')) $(this).prop('checked', false);
      else $(this).prop('checked', true);
    });
  };

  function ui_table_get_selected(uid){
    ids = '';
    $('.'+uid+'_multiselect').each(function(){
      if ($(this).prop('checked')) ids += $(this).attr('data-id')+',';
    });
    ids = ids.substring(0, ids.length - 1);
    return ids;
  };

  function ui_table_delete_item(uid, id){
    table = $('#'+uid).attr('data-table');
    if (table != ''){
      action = 'UI/Table/Delete';
      var params = {};
      params.ids = id;
      params.table = table;
      res = _ajax_sread(action, params);
      if (isNaN(res)) _alert(res);
      ui_table_refresh(uid);
    }else{
      _alert('table missing');
    };
  };

  function ui_table_delete_selected(uid){
    var ids = ui_table_get_selected(uid);
    table = $('#'+uid).attr('data-table');
    if (table != ''){
      action = 'UI/Table/Delete';
      var params = {};
      params.ids = ids;
      params.table = table;
      res = _ajax_sread(action, params);
      if (isNaN(res)) _alert(res);
      ui_table_refresh(uid);
    }else{
      _alert('table missing');
    };
  };

  function ui_table_copy_selected(uid){
    var ids = ui_table_get_selected(uid);
    table = $('#'+uid).attr('data-table');
    if (table != ''){
      action = 'UI/Table/Copy';
      var params = {};
      params.ids = ids;
      params.table = table;
      res = _ajax_sread(action, params);
      if (isNaN(res)) _alert(res);
      ui_table_refresh(uid);
    }else{
      _alert('table missing');
    };
  };

  function ui_table_insert_row(uid){
    var data = ui_form_get_values(uid+'_insert_row_form');
    $('.'+uid+'_insert_row_button').attr('disabled', 'disabled');
    var action = 'UI/Form/Save';
    data.table = $('#'+uid).attr('data-table');
    _ajax_read(action, data, function(_saved_id){
      $('.'+uid+'_insert_row_button').removeAttr('disabled');
      if (isNaN(_saved_id)) _alert(_saved_id); else {

        if (data.id == -1) data.inserted_id = _saved_id;
        else data.inserted_id = 0;

        ui_table_refresh(uid);
      }
    });
  };
  //simple_insert,
  function ui_table_open_multiupload_forms(uid, data, params, col){
    if (!(Object.keys(params.default_values).length > 0)) params.default_values = {};
    var objects = [];
    var table = $('#'+uid).attr('data-table');
    var index = -1;
    if (data.length > 0){
      for (var item in data) {
        index--;
        objects[item] = JSON.parse(JSON.stringify(params.default_values));
        objects[item][col] = data[item]['file_path'];
        window_render('UI/Form',
          {table: table,
          id: index,
          simple_insert: params.simple_insert,
          // onclose: 'ui_table_refresh(\''+uid+'\');',
          default_values: objects[item],
          extra_params: params.extra_params });
      };
    };
  };
