<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Table extends \ADIOS\Core\UI\View {

    var $model = NULL;

    var $columns = [];
    var $columnsFilter = [];

    public function __construct(&$adios, $params = null) {

      $this->adios = &$adios;
      $this->userParams = $params;

      if ($params['refresh'] && !empty($params['uid'])) {
        $params = parent::params_merge(
          $_SESSION[_ADIOS_ID]['table'][$params['uid']],
          $params
        );
      }

      // defaultne parametre
      $params = parent::params_merge([
        'table' => '',
        'column_settings' => [],
        'title_type' => 'title',
        'white_space' => 'nowrap',
        'order' => ('' != $params['table'] ? "{$params['table']}.id desc" : ''),
        'title' => '',
        'tag' => '',
        'page' => 1,
        'where' => '',
        'having' => '',
        'group' => '',
        'onclick' => '',
        'ondblclick' => '',
        'show_title' => true,
        'show_paging' => true,
        'show_titles' => true,
        'show_filter' => true,
        'show_multiselect' => false,
        'show_multiselect_delete' => true,
        'show_multiselect_copy' => false,
        'multiselect_position' => 'left',
        'show_delete_column' => false,
        'show_refresh' => false,
        'show_export_csv' => false,
        'show_settings' => true,
        'show_controls' => true,
        'show_add_button' => true,
        'show_search_button' => true,
        'sortable' => false,
        'custom_select' => '',
        'custom_group' => '',
        'refresh_action' => 'UI/Table',
        'items_per_page' => 25,
        'width' => '',
        'allow_order_modification' => true,
        'form_type' => 'window',
        'info_no_data' => "No items found.",
        'clear_filter' => false,
        'count_select_values' => [10, 25, 100, 1000],
        'add_button_params' => [],
        'title_params' => [],
        'custom_filters' => [],
        'columns_order' => [],
        'show_fulltext_search' => false,
        'fulltext_search_columns' => [],
        'min_paging_count' => 0,
        'simple_insert' => false,
        'edit_form_default_values' => [],
        'edit_form_extra_params' => [],
        'default_filter' => [],
        'force_default_filter' => false,
        'display_columns' => [],
        'onclick_available_columns' => [],
        'multiselect_position' => 'right',
      ], $params);

      if (empty($params['model'])) {
        exit("UI/Table: Don't know what model to work with.");
        return;
      }

      $this->model = $this->adios->getModel($params['model']);
      $params['table'] = $this->model->getFullTableSQLName();

      if (empty($params['uid'])) {
        $params['uid'] = $this->adios->getUid($params['model']);
      }

      if (empty($params['title'])) {
        $params['title'] = $this->model->tableTitle;
      }

      if (empty($params['onclick'])) {
        $params['onclick'] = "
          window_render('".$this->model->getFullUrlBase($params)."/' + id + '/Edit')
        ";
      }

      if (empty($params['add_button_params']['onclick'])) {
        $params['add_button_params']['onclick'] = "
          window_render('".$this->model->getFullUrlBase($params)."/Add')
        ";
      }

      if (!empty($params['search'])) {
        $this->search = @json_decode(base64_decode($params['search']), TRUE);
      } else {
        $this->search = NULL;
      }

      $_SESSION[_ADIOS_ID]['table'][$params['uid']] = $params;

      parent::__construct($adios, $params);







      $this->columns = $this->model->columns();

      $this->params = $this->model->tableParams($this->params);

      foreach ($this->userParams as $key => $value) {
        $this->params[$key] = $value;
      }

      $this->params['page'] = (int) $this->params['page'];
      $this->params['items_per_page'] = (int) $this->params['items_per_page'];


      if (_count($this->params['columns_order'])) {
        $tmp_columns = [];
        foreach ($this->params['columns_order'] as $col_name) {
          $tmp_columns[$col_name] = $this->columns[$col_name];
        }
        foreach ($this->columns as $col_name => $col_definition) {
          if (!isset($tmp_columns[$col_name])) {
            $tmp_columns[$col_name] = $col_definition;
          }
        }
        $this->columns = $tmp_columns;
      }

      // nastavenie poradia
      $tmp = explode(' ', $this->params['order_by']);
      if (empty($this->columns[$tmp[0]])) {
        $this->params['order_by'] = '';
      }

      //
      $this->columnsFilter = [];

      foreach ($this->columns as $col_name => $col_def) {
        if (isset($this->params['column_filter_'.$col_name])) {
          $this->columnsFilter[$col_name] = $this->params['column_filter_'.$col_name];
          unset($this->params['column_filter_'.$col_name]);
        }
      }

      // ak programator dal iny pocet na stranku ako predvoleny, tak sa zapamata
      if (!in_array($this->params['items_per_page'], $this->params['count_select_values'])) {
        $this->params['count_select_values'][] = $this->params['items_per_page'];
        sort($this->params['count_select_values']);
        $this->params['custom_items_per_page'] = $this->params['items_per_page'];
      }

      if (!in_array($this->params['custom_items_per_page'], $this->params['count_select_values']) && is_numeric($this->params['custom_items_per_page'])) {
        $this->params['count_select_values'][] = $this->params['custom_items_per_page'];
        sort($this->params['count_select_values']);
      }

      // kontroly pre vylucenie nelogickosti parametrov

      if (!$this->params['show_controls']) {
        $this->params['show_paging'] = false;
      }

      if ('lookup_select' == $this->params['list_type']) {
        $this->params['show_insert_row'] = false;
        $this->params['show_insert_row'] = false;
        $this->params['ondblclick'] = '';
        $this->params['show_title'] = false;

        $this->params['show_multiselect'] = false;
        $this->params['show_multiselect_delete'] = false;
        $this->params['show_multiselect_copy'] = false;
        $this->params['show_delete_column'] = false;
        $this->params['show_export_csv'] = false;

        $this->params['show_settings'] = false;
        $this->params['show_add_button'] = false;
        $this->params['sortable'] = false;
        $this->params['show_fulltext_search'] = false;
      }

      // where a having

      $where = '('.('' == $this->params['where'] ? 'TRUE' : $this->params['where']).')';

      $having = (empty($this->params['having']) ? 'TRUE' : $this->params['having']);
      if (_count($this->columnsFilter)) {
        $having .= " and ".$this->model->tableFilterSqlWhere($this->columnsFilter);
      }
      if (_count($this->search)) {
        $having .= " and ".$this->model->tableFilterSqlWhere($this->search);
      }

      $order_by = $this->params['order_by'];

      if ($this->params['debug']) {
        _d(true);
      }

      if ($this->params['show_paging']) {
        // ak sa zobrazuje sumarny/statisticky riadok,
        // tak namiesto countu vybera statisticke udaje, pricom je pre id nastavene selectovanie count(id)

        $count_rows = true;

        if (!empty($this->params['table'])) {
          $this->tmp_column_settings = $this->adios->db->tables[$this->params['table']];
          $this->adios->db->tables[$this->params['table']] = $this->columns;

          $this->table_item_count = $this->adios->db->count_all_rows($this->params['table'], [
            'where' => $where,
            'having' => $having,
            'group' => $this->params['group'],
            // 'follow_lookups' => (_count($this->search) ? TRUE : FALSE),
            // 'wa_list_follow' => true,
            // 'count_rows' => $count_rows,
            // 'left_join' => $this->params['left_join'],
          ]);

          if (_count($this->tmp_column_settings)) {
            $this->adios->db->tables[$this->params['table']] = $this->tmp_column_settings;
          }
        }

      }

      if ($this->table_item_count <= $this->params['min_paging_count']) {
        $this->params['show_paging'] = false;
      }

      if ($this->params['show_paging']) {
        if ($this->params['page'] * $this->params['items_per_page'] > $this->table_item_count) {
          $this->params['page'] = floor($this->table_item_count / $this->params['items_per_page']) + 1;
        }
        $limit_1 = ($this->params['show_paging'] ? max(0, ($this->params['page'] - 1) * $this->params['items_per_page']) : '');
        $limit_2 = ($this->params['show_paging'] ? $this->params['items_per_page'] : '');
      } else {
        $this->table_item_count = 0;
        $page = 1;
      }

      $get_all_rows_params = [
        'where' => $where,
        'having' => $having,
        'order' => $order_by,
        // 'follow_lookups' => (_count($this->search) ? TRUE : FALSE),
        // 'wa_list_follow' => true,
      ];

      if (is_numeric($limit_1)) $get_all_rows_params['limit_start'] = $limit_1;
      if (is_numeric($limit_2)) $get_all_rows_params['limit_end'] = $limit_2;

      if ($this->params['export_csv']) {
        unset($get_all_rows_params['limit_start']);
        unset($get_all_rows_params['limit_end']);
        unset($limit_1);
        unset($limit_2);
      }

      if ('' != $this->params['table']) {
        $this->tmp_column_settings = $this->adios->db->tables[$this->params['table']];
        $this->adios->db->tables[$this->params['table']] = $this->columns;
        $this->table_data = $this->adios->db->get_all_rows($this->params['table'], $get_all_rows_params);
        if (_count($this->tmp_column_settings)) {
          $this->adios->db->tables[$this->params['table']] = $this->tmp_column_settings;
        }
      }

      if (!$this->params['show_paging']) {
        $this->table_item_count = count($this->table_data);
      }

      $this->model->onTableAfterDataLoaded($this);

      // CSV export

      if ($this->params['export_csv']) {
          $csv_data = '';
          foreach ($this->columns as $col_name => $col_def) {
              if ($col_def['show_column']) {
                  if ('' == $col_def['title']) {
                      $title = $col_name;
                  } else {
                      $title = ($col_def['title']);
                  }
                  $csv_data .= '"'.iconv('UTF-8', 'CP1250//IGNORE', $title).'";';
              }
          }
          $csv_data .= "\n";

          $export_csv_ids = explode(',', $_REQUEST['export_csv_ids']);

          if (_count($this->table_data)) {
              foreach ($this->table_data as $row) {
                  // v pripade multiselect zvolenych ids sa vyexportuju len tie
                  if (in_array($row['id'], $export_csv_ids) || '' == $_REQUEST['export_csv_ids']) {
                      foreach ($this->columns as $col_name => $col_def) {
                          if ($col_def['show_column']) {
                              // table cell HTML value
                              $cell_csv = false;

                              if (false === $cell_csv || null === $cell_csv) {
                                  $cell_csv = '';
                                  if ($this->adios->db->is_registered_column_type($col_def['type'])) {
                                      $cell_csv = $this->adios->db->registered_columns[$col_def['type']]->get_csv($row[$col_name], [
                                          'wa_list_html' => true,
                                          'col_name' => $col_name,
                                          'col_definition' => $col_def,
                                          'row' => $row,
                                          'export_csv' => true,
                                      ]);
                                  }
                              }

                              $tmp_formatter_res = $this->model->tableCellCSVExportFormatter([
                                'table' => $this,
                                'column' => $col_name,
                                'row' => $row,
                                'csv' => $cell_csv
                              ]);

                              // ak nevrati ziadne html, neprepisuje sa
                              if ('' != $tmp_formatter_res) {
                                $cell_csv = $tmp_formatter_res;
                              }

                              $csv_data .= '"'.str_replace('"', '""', trim(/*strip_tags*/(iconv('UTF-8', 'CP1250//IGNORE', $cell_csv)))).'";';
                          }
                      }
                      $csv_data .= "\n";
                  }
              }
          }

          header('Expires: 0');
          header('Cache-control: private');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Content-Description: File Transfer');
          header('Content-Type: application/csv');
          header('Content-disposition: attachment; filename='.('' == $this->params['output_csv_filename'] ? 'export.csv' : $this->params['output_csv_filename']));

          echo $csv_data;
          die();
          //echo iconv("UTF-8", "CP1250//IGNORE", $csv_data);
      }

      // generovanie komponentov

      $_from = ($this->params['page'] - 1) * $this->params['items_per_page'] + 1;
      $_to = min($this->table_item_count, ($this->params['page']) * $this->params['items_per_page']);

      if ($this->table_item_count > 0) {
        $this->add("<div class='count_content'>{$this->table_item_count} items total</div>", 'count');


        $items_per_page_select = "
          <select
            id='{$this->params['uid']}_table_count'
            onchange='ui_table_change_items_per_page(\"{$this->params['uid']}\", this.value);'
          >
        ";
        foreach ($this->params['count_select_values'] as $val) {
          $items_per_page_select .= "
            <option value='{$val}' ".($this->params['items_per_page'] == $val ? 'selected=selected' : '').">{$val}</option>
          ";
        }
        $items_per_page_select .= '</select>';

        $this->add($items_per_page_select, 'settings');
      }

      if ($this->params['show_export_csv']) {
        $this->add($this->adios->ui->button(['icon' => 'app/external-link.png', 'title' => l('Export CSV'), 'onclick' => "ui_table_export('{$this->params['uid']}');", 'show_border' => false]), 'settings');
      }
      if ($this->params['show_refresh']) {
        $this->add(
          $this->adios->ui->button([
            'fa_icon' => 'fas fa-sync-alt',
            'class' => 'btn-light btn-circle btn-sm',
            'title' => l('Obnoviť'),
            'onclick' => " ui_table_refresh('{$this->params['uid']}');",
          ]),
          'settings'
        );
      }
      if ($this->params['show_multiselect']) {
        if ($this->params['show_multiselect_delete']) {
          $this->add($this->adios->ui->button(['icon' => 'app/delete.png', 'title' => l('Zmazať označené'), 'onclick' => " _confirm('".l('Naozaj si želáte zmazať označené položky?')."', {}, function(){ ui_table_delete_selected('{$this->params['uid']}'); });", 'show_border' => false]), 'settings');
        }
        if ($this->params['show_multiselect_copy']) {
          $this->add($this->adios->ui->button(['icon' => 'app/copy.png', 'title' => l('Kopírovať označené'), 'onclick' => " _confirm('".l('Naozaj si želáte kopírovať označené položky?')."', {}, function(){ ui_table_copy_selected('{$this->params['uid']}');  });", 'show_border' => false]), 'settings');
        }
      }

      // strankovanie

      $page_count = ceil($this->table_item_count / $this->params['items_per_page']);
      $show_pages = 4;

      if ($this->params['show_paging']) {
        $this->add(
          $this->adios->ui->button([
            'fa_icon' => 'fas fa-angle-double-left',
            'class' => 'btn-light btn-circle btn-sm',
            'title' => l('Prvá stránka'),
            'onclick' => "ui_table_show_page('{$this->params['uid']}', '1'); ",
            'disabled' => (1 == $this->params['page'] ? true : false)]
          ),
          'paging'
        );
        $this->add(
          $this->adios->ui->button([
            'fa_icon' => 'fas fa-angle-left',
            'class' => 'btn-light btn-circle btn-sm',
            'title' => l('Predošlá stránka'),
            'onclick' => "ui_table_show_page('{$this->params['uid']}', '".($this->params['page'] - 1)."'); ",
            'disabled' => (1 == $this->params['page'] ? true : false)
          ]),
          'paging'
        );

        for ($i = 1; $i <= $page_count; ++$i) {
          if ($i == $this->params['page']) {
            $this->add("<input type='text' value='{$this->params['page']}' class='paging_input' id='{$this->params['uid']}_paging_bottom_input' onchange=\"ui_table_show_page('{$this->params['uid']}', this.value);\" onkeypress=\"if (event.keyCode == 13) { ui_table_show_page('{$this->params['uid']}', this.value); } \" onclick='this.select();' /><script> draggable_int_input('{$this->params['uid']}_paging_bottom_input', {min_val: 1, max_val: {$page_count}});</script>", 'paging');
          } elseif (abs($this->params['page'] - $i) <= ($show_pages / 2) || ($this->params['page'] <= ($show_pages / 2) && $i <= ($show_pages + 1)) || (($page_count - $this->params['page']) <= ($show_pages / 2) && $i >= ($page_count - $show_pages))) {
            $this->add($this->adios->ui->button(['text' => $i, 'class' => 'pages', 'onclick' => "ui_table_show_page('{$this->params['uid']}', '{$i}'); ", 'show_border' => false]), 'paging');
          }
        }

        $this->add(
          $this->adios->ui->button([
            'fa_icon' => 'fas fa-angle-right',
            'class' => 'btn-light btn-circle btn-sm',
            'title' => l('Nasledujúca stránka'),
            'onclick' => "ui_table_show_page('{$this->params['uid']}', '".($this->params['page'] + 1)."'); ",
            'disabled' => ($this->params['page'] == $page_count || 0 == $this->table_item_count ? true : false)
          ]),
          'paging'
        );
        $this->add(
          $this->adios->ui->button([
            'fa_icon' => 'fas fa-angle-double-right',
            'class' => 'btn-light btn-circle btn-sm',
            'title' => l('Posledná stránka'),
            'onclick' => "ui_table_show_page('{$this->params['uid']}', '".($page_count)."'); ",
            'disabled' => ($this->params['page'] == $page_count || 0 == $this->table_item_count ? true : false)
          ]),
          'paging'
        );
      }

      $this->params['show_add_button'] = ('' != $this->params['add_button_params']['onclick'] || $this->adios->has_perms("db/{$this->params['table']}/insert") ? $this->params['show_add_button'] : false);

      if ($this->params['show_add_button']) {
        if ('' == $this->params['add_button_params']['type']) {
          $this->params['add_button_params']['type'] = 'add';
        }
        if ('' == $this->params['add_button_params']['onclick']) {
          if ('desktop' == $this->params['form_type']) {
            $this->params['add_button_params']['onclick'] = "
              desktop_render(
                'UI/Form', 
                {
                  form_type: 'desktop',
                  table: '{$this->params['table']}', 
                  id: -1,
                  simple_insert: ".($this->params['simple_insert'] ? 1 : 0).",
                  default_values: $.parseJSON(decodeURIComponent('".rawurlencode(json_encode($this->params['edit_form_default_values']))."')),
                  extra_params: $.parseJSON(decodeURIComponent('".rawurlencode(json_encode($this->params['edit_form_extra_params']))."'))
                }
              );
            ";
          } else {
            $this->params['add_button_params']['onclick'] = "
              let tmp_params = {
                table: '{$this->params['table']}',
                id: -1
              };

              ".($this->params['simple_insert'] ? "
                tmp_params['simple_insert'] = '1';
              " : "")."

              ".(empty($this->params['edit_form_default_values']) ? "" : "
                tmp_params['default_values'] = '".rawurlencode(json_encode($this->params['edit_form_default_values']))."';
              ")."

              ".(empty($this->params['edit_form_extra_params']) ? "" : "
                tmp_params['extra_params'] = '".rawurlencode(json_encode($this->params['edit_form_default_values']))."';
              ")."

              window_render('UI/Form', tmp_params);
            ";
          }
        }
        $add_button = $this->adios->ui->button($this->params['add_button_params']);
      }

      if ($this->params['show_search_button']) {
        if (empty($this->model->searchAction)) {
          $search_action = $this->model->getFullUrlBase($params)."/Search";
        } else {
          $search_action = $this->model->searchAction;
        }

        $search_button = $this->adios->ui->button([
          "type" => "search",
          "onclick" => "window_render('{$search_action}');",
        ]);
      }

      if ($this->params['show_title']) {
        $this->params['title_params']['left'] = [$add_button, $search_button];
        $this->params['title_params']['center'] = $this->params['title'];
        $this->params['title_params']['class'] = 'table_title';
        if ('title' == $this->params['title_type']) {
          $this->add(
            $this->adios->ui->Title($this->params['title_params']),
            'title'
          );
        }
      }

      if (_count($this->search)) {
        $tmpSearchHtml = "";

        foreach ($this->search as $searchColName => $searchValue) {
          if (!empty($searchValue)) {
            if (strpos($searchColName, "LOOKUP___") === 0) {
              list($tmp, $tmpSrcColName, $tmpLookupColName) = explode("___", $searchColName);
              $tmpSrcColumn = $this->model->columns()[$tmpSrcColName];
              $tmpLookupModel = $this->adios->getModel($tmpSrcColumn['model']);
              $tmpColumn = $tmpLookupModel->columns()[$tmpLookupColName];
              $tmpTitle = $tmpLookupModel->tableTitle." / ".$tmpColumn["title"];
            } else {
              $tmpColumn = $this->columns[$searchColName];
              $tmpTitle = $tmpColumn['title'];
            }

            $tmpSearchHtml .= "
              ".hsc($tmpTitle)."
              = ".hsc($searchValue)."
            ";
          }
        }
        $this->add(
          "
            <div class='card shadow mb-4'>
              <a class='card-header py-3'>
                <h6 class='m-0 font-weight-bold text-primary'>Search is activated</h6>
              </a>
              <div>
                <div class='card-body'>
                  <div class='mb-2'>
                    {$tmpSearchHtml}
                  </div>
                  ".$this->adios->ui->Button([
                    "type" => "close",
                    "text" => "Clear filter (Show all records)",
                    "onclick" => "desktop_update('{$this->adios->requestedAction}');",
                  ])->render()."
                </div>
              </div>
            </div>
          ",
          "title"
        );
      }
    }











    // render

    public function render(string $panel = '') {
        $params = $this->params;

        $html = "";

        $this->add_class('shadow');

        if (!_count($this->columns)) {
            $this->adios->console->log('Component:table', 'No column_settings provided');
        }

        if ('' != $params['width']) {
            $this->params['style'] .= "width:{$params['width']}";
        }
        if (!$this->params['__IS_WINDOW__']) {
          $this->add_class('desktop');
        }
        if ('' != $params['tag']) {
            $this->add_class('ui_table_tag_'.$params['tag']);
        }

        $this->add_class('ui_table_tag_'.$params['table']);

        if (!$this->params['refresh']) {
            $html .= parent::render('title');

            if (!empty($this->params['header'])) {
              $html .= "
                <div class='adios ui Table header'>
                  {$params['header']}
                </div>
              ";
            }

            $html .= "
              <div
                ".$this->main_params()."
                data-refresh-action='".ads($this->params['refresh_action'])."'
                data-refresh-params='".(empty($this->params['uid'])
                  ? json_encode($this->params['_REQUEST'])
                  : json_encode(['uid' => $this->params['uid']])
                )."'
                data-action='".ads($this->adios->action)."'
                data-page='".(int) $this->params['page']."'
                data-items-per-page='".(int) $this->params['items-per-page']."'
                data-is-ajax='".($this->adios->isAjax() ? "1" : "0")."'
                data-is-in-form='".(in_array("UI/Form", $this->adios->actionStack) ? "1" : "0")."'
              >
            ";
        }

        if (_count($this->columns)) {
            // ponecha len zobrazene stlpce
            $mandatory['id'] = $this->columns['id'];
            if ('' != $params['mandatory_column'] && _count($this->columns[$params['mandatory_column']])) {
                $mandatory[$params['mandatory_column']] = $this->columns[$params['mandatory_column']];
            }
            foreach ($this->columns as $col_name => $col_def) {
                if (!$col_def['show_column']) {
                    unset($this->columns[$col_name]);
                }
            }
            if (!_count($this->columns)) {
                $this->columns = $mandatory;
            }

            // zakladne nastavenia

            $ordering = explode(' ', $this->params['order_by']);

            if ($params['allow_order_modification']) {
                $order_modification_class = 'modificable';
            }

            // tabulka zoznamu

            $html .= "
              <div class='overflow_wrapper'>
                <div class='Table'>
            ";

            // title riadok - nazvy stlpcov

            if ($params['show_titles']) {
                $html .= "<div class='table_tr table_header'>";

                if ($params['show_multiselect']) {
                    $multiselect_html = "<div class='table_td'>".l('Výber').'</div>';
                } else {
                    $multiselect_html = '';
                }

                if ('left' == $params['multiselect_position']) {
                    $html .= $multiselect_html;
                }

                foreach ($this->columns as $col_name => $col_def) {
                    if ($params['allow_order_modification']) {
                        $new_ordering = "$col_name asc";
                        $order_class = 'unordered';
                        $order_title = l('Zoradiť vzostupne');

                        if ($ordering[0] == $col_name || $params['table'].'.'.$col_name == $ordering[0]) {
                            switch ($ordering[1]) {
                                case 'asc': $new_ordering = "$col_name desc";
                                    $order_class = 'asc_ordered';
                                    $order_title = l('Zoradiť zostupne');
                                    break;
                                case 'desc': $new_ordering = 'none';
                                    $order_class = 'desc_ordered';
                                    $order_title = l('Zrušiť zoradenie');
                                    break;
                            }
                        }
                    }

                    $order_img_down = "<i class='fas fa-chevron-down order_desc'></i>";
                    $order_img_up = "<i class='fas fa-chevron-up order_asc'></i>";
                    $order_img_none = "<i class='fas fa-minus order_none'></i>";
                    $html .= "
                      <div
                        class='table_td {$order_modification_class} {$order_class}'
                        title='$order_title'
                        ".($params['allow_order_modification'] ? "
                          onclick='
                            ui_table_refresh(\"{$params['uid']}\", {order_by: \"{$new_ordering}\"});
                          '
                        " : "")."
                        style='
                          width:{$col_def['column_width']};
                          ".($params['allow_order_modification'] ? "" : "cursor:default;")."
                        '
                      >
                        {$order_img_down}{$order_img_up}{$order_img_none}
                        ".hsc($col_def['title'])."
                        ".('' == $col_def['unit'] ? '' : '['.hsc($col_def['unit']).']')."
                      </div>
                    ";
                }

                if ('right' == $params['multiselect_position']) {
                    $html .= $multiselect_html;
                }

                if ($params['show_delete_column']) {
                    $html .= "<div class='table_td'>".(!$params['show_insert_row'] ? l('Zmazať') : '').'</div>';
                }

                // koniec headeru
                $html .= '  </div>';
            }

            // filtrovaci riadok

            if ($params['show_filter']) {
                $html .= "<div class='table_tr table_filter'>";

                if ($params['show_multiselect']) {
                    $multiselect_html = "
                      <div class='table_td multiselect_controls'>
                        <img src='{$this->adios->config['adios_images_url']}/black/app/checkmark.png' title='".l('Označiť všetky')."' onclick=' ui_table_select_all(\"{$params['uid']}\"); '/>
                        <img src='{$this->adios->config['adios_images_url']}/black/app/refresh.png' title='".l('Invertovať výber')."' onclick=' ui_table_invert_selection(\"{$params['uid']}\"); '/>
                        <img src='{$this->adios->config['adios_images_url']}/black/app/minus-7.png' title='".l('Odznačiť všetky')."' onclick=' ui_table_deselect_all(\"{$params['uid']}\"); '/>
                        <script> $('#{$params['uid']} .multiselect_controls img').tooltip({track: 1});</script>
                      </div>
                    ";
                } else {
                    $multiselect_html = '';
                }

                if ('left' == $params['multiselect_position']) {
                    $html .= $multiselect_html;
                }

                foreach ($this->columns as $col_name => $col_def) {
                    $filter_input = "";
                    
                    switch ($col_def['type']) {
                        case 'varchar':
                        case 'text':
                        case 'password':
                        case 'lookup':
                            $tooltip = l('Vpíšte vyhľadávaný text a stlačte enter').'.<br/>'.
                                l('Pre hľadanie výrazu obsahujúceho viac slov ich oddeľte medzerou').'<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>'.
                                l('Pre hľadanie čiarky, alebo medzery napíšte na začiatok =').'';
                            $input_type = 'text';
                            break;
                        case 'color':
                            $tooltip = l('Vpíšte hexadecimálny kód farby a stlačte enter').'.<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>';
                            $input_type = 'text';
                            break;
                        case 'date':
                        case 'datetime':
                            $tooltip = l('Vpíšte dátum vo formáte dd.mm.yyyy a stlačte enter').'.<br/>'.
                                l('Pre hľadanie medzi dvomi dátumami ich oddeľte pomlčkou (dd.mm.yyyy - dd.mm.yyyy)').'.<br/>'.
                                l('Pred hodnotou je možné používať operátory =, >, <, >=, <=, <>, !=').'.<br/>'.
                                l('Pre hľadanie v konkrétnom mesiaci zadajte mm.yyyy').'.<br/>'.
                                l('Pre hľadanie v konkrétnom roku zadajte yyyy').'.<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>'.
                                l("Pre hľadanie nevyplnených dátumov zadajte znak -.");
                            $input_type = 'text';
                            break;
                        case 'timestamp':
                            $tooltip = l('Vpíšte dátum vo formáte dd.mm.yyyy a stlačte enter').'.<br/>'.
                                l('Pre hľadanie medzi dvomi dátumami ich oddeľte pomlčkou (dd.mm.yyyy - dd.mm.yyyy)').'.<br/>'.
                                l('Pred hodnotou je možné používať operátory =, >, <, >=, <=, <>, !=').'.<br/>'.
                                l('Pre hľadanie v konkrétnom mesiaci zadajte mm.yyyy').'.<br/>'.
                                l('Pre hľadanie v konkrétnom roku zadajte yyyy').'.<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>'.
                                l("Pre hľadanie nevyplnených dátumov zadajte znak -.");
                            $input_type = 'text';
                            break;
                        case 'time':
                            $tooltip = l('Vpíšte čas vo formáte hh:mm a stlačte enter').'.<br/>'.
                                l('Pre hľadanie medzi dvomi časmi ich oddeľte pomlčkou (hh:mm - hh:mm)').'.<br/>'.
                                l('Pred hodnotou je možné používať operátory =, >, <, >=, <=, <>, !=').'.<br/>'.
                                l('Pre hľadanie v konkrétnej hodine zadajte hh').'.<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>'.
                                l("Pre hľadanie nevyplnených dátumov zadajte znak -.");
                            $input_type = 'text';
                            break;
                        case 'year':
                            $tooltip = l('Vpíšte rok vo formáte yyyy a stlačte enter').'.<br/>'.
                                l('Pre hľadanie medzi dvomi rokmi ich oddeľte pomlčkou (yyyy - yyyy)').'.<br/>'.
                                l('Pred hodnotou je možné používať operátory =, >, <, >=, <=, <>, !=').'.<br/>'.
                                l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>';
                            $input_type = 'text';
                            break;
                        case 'float':
                        case 'int':

                            if (_count($col_def['enum_values']) && 'select'
                                == $col_def['filter_style']) {
                                $tooltip = '';
                                $input_type = 'select';
                                $input_values = $col_def['enum_values'];
                            } else {
                                $tooltip = l('Vpíšte číslo a stlačte enter').'.<br/>'.
                                    l('Pre hľadanie medzi dvoma hodnotami ich oddeľte pomlčkou (x - y)').'.<br/>'.
                                    l('Pred hodnotou je možné používať operátory =, >, <, >=, <=, <>, !=').'.<br/>'.
                                    l('Pre hľadanie viacerých výrazov ich oddeľte čiarkou').'.<br/>'.
                                    l('V prípade desatinných čísiel používajte desatinnú bodku').'.<br/>';
                                $input_type = 'text';
                            }

                            break;
                        case 'enum':
                            $tooltip = '';
                            $input_type = 'select';
                            $input_values = explode(',', $col_def['enum_values']);
                            break;
                        case 'bool':
                            $input_type = 'bool';
                            $true_value = 'Y';
                            $false_value = 'N';
                            break;
                        case 'boolean':
                            $input_type = 'bool';
                            $true_value = 1;
                            $false_value = 0;
                            break;

                        default: $tooltip = '&nbsp;';
                            $input_type = '';
                            $filter_input = '';
                    }

                    if ('text' == $input_type) {
                        $filter_input = "
                          <input
                            type='text'
                            class='{$params['uid']}_column_filter'
                            data-col-name='{$col_name}'
                            id='{$params['uid']}_column_filter_{$col_name}'
                            required='required'
                            value=\"".htmlspecialchars($this->columnsFilter[$col_name])."\"
                            title=' '
                            onkeydown='if (event.keyCode == 13) { event.cancelBubble = true; }'
                            onkeypress='if (event.keyCode == 13) { event.cancelBubble = true; ui_table_set_column_filter(\"{$params['uid']}\", {}); }'
                            {$col_def['table_filter_attributes']}
                            placeholder='🔍'
                          >
                        ";
                    }

                    if ('select' == $input_type) {
                        $filter_input = "<select
                             class='{$params['uid']}_column_filter'
                             data-col-name='{$col_name}'
                             id='{$params['uid']}_column_filter_{$col_name}'
                             title=' '
                             required='required'
                             onchange=' ui_table_set_column_filter(\"{$params['uid']}\", {}); '><option></option>";

                        if (_count($input_values)) {
                            foreach ($input_values as $enum_val) {
                                $filter_input .= "<option value='{$enum_val}' ".($this->columnsFilter[$col_name] == $enum_val ? "selected='selected'" : '').'>'.l($enum_val).'</option>';
                            }
                        }

                        $filter_input .= '</select>';
                    }

                    if ('bool' == $input_type) {
                        $filter_input = "
                          <div
                            class='bool_controls ".(is_numeric($this->columnsFilter[$col_name]) ? "filter_active" : "")."'
                          >
                            <input type='hidden'
                              class='{$params['uid']}_column_filter'
                              data-col-name='{$col_name}'
                              id='{$params['uid']}_column_filter_{$col_name}'
                              required='required'
                              value='".ads($this->columnsFilter[$col_name])."'
                            />

                            <i
                              class='fas fa-check-circle ".($this->columnsFilter[$col_name] == 1 ? "active" : "")."'
                              style='color:#4caf50'
                              onclick='
                                if ($(\"#{$params['uid']}_column_filter_{$col_name}\").val() == \"$true_value\") $(\"#{$params['uid']}_column_filter_{$col_name}\").val(\"\"); else $(\"#{$params['uid']}_column_filter_{$col_name}\").val(\"{$true_value}\");
                                ui_table_set_column_filter(\"{$params['uid']}\", {});
                              '
                            ></i>
                            <i
                              class='fas fa-times-circle ".($this->columnsFilter[$col_name] == 0 ? "active" : "")."'
                              style='color:#ff5722'
                              onclick='
                                if ($(\"#{$params['uid']}_column_filter_{$col_name}\").val() == \"{$false_value}\") $(\"#{$params['uid']}_column_filter_{$col_name}\").val(\"\"); else $(\"#{$params['uid']}_column_filter_{$col_name}\").val(\"{$false_value}\");
                                ui_table_set_column_filter(\"{$params['uid']}\", {});
                              '
                            ></i>
                            <!-- <i
                              class='fas fa-minus'
                              onclick='
                                $(\"#{$params['uid']}_column_filter_{$col_name}\").val(\"\");
                                ui_table_set_column_filter(\"{$params['uid']}\", {});
                              '
                            ></i> -->

                            <script> $('#{$params['uid']} .multiselect_controls img').tooltip({track: 1});</script>
                          </div>
                        ";
                    }

                    $html .= "
                      <div class='table_td {$input_type}'>
                        {$filter_input}
                      </div>
                    ";
                }

                //multi select

                if ('right' == $params['multiselect_position']) {
                    $html .= $multiselect_html;
                }

                if ($params['show_delete_column']) {
                    $html .= "<div class='table_td'>
                    &nbsp;
                  </div>";
                }

                // koniec filtra
                $html .= '</div>';
            }

            // zaznamy tabulky

            if (_count($this->table_data)) {
                foreach ($this->table_data as $val) {
                    $onclick = '';
                    $ondblclick = '';

                    if ('' == $params['onclick']) {
                        if ('desktop' == $params['form_type']) {
                            $params['onclick'] = "
                              desktop_render(
                                'UI/Form',
                                {
                                  form_type: 'desktop',
                                  table: '{$this->params['table']}',
                                  id: id,
                                  extra_params: $.parseJSON(decodeURIComponent('".rawurlencode(json_encode($this->params['edit_form_extra_params']))."'))
                                }
                              );
                            ";
                        } else {
                            $params['onclick'] = "
                              let tmp_params = {
                                table: '{$this->params['table']}',
                                id: id
                              };

                              ".(empty($this->params['edit_form_extra_params']) ? "" : "
                                tmp_params['extra_params'] = $.parseJSON(
                                  decodeURIComponent(
                                    '".rawurlencode(json_encode($this->params['edit_form_extra_params']))."'
                                  )
                                );
                              ")."

                              window_render('UI/Form', tmp_params);
                            ";
                        }
                    }

                    $onclick_available_columns_js = '';

                    if (_count($params['onclick_available_columns'])) {
                        foreach ($params['onclick_available_columns'] as $click_column) {
                            if (isset($val[$click_column])) {
                                $onclick_available_columns_js .= " var {$click_column} = '".$this->adios->db->escape($val[$click_column])."'; ";
                            }
                        }
                    }

                    if ('' != $params['onclick']) {
                        $onclick = "
                          onclick=\"
                            let _this = $(this);
                            _this.closest('.data_tr').css('opacity', 0.5);
                            setTimeout(function() {
                              _this.closest('.data_tr').css('opacity', 1);
                            }, 300);
                            let id = ".(int) $val['id'].";
                            {$onclick_available_columns_js}
                            {$params['onclick']}
                          \"
                        ";
                    }
                    if ('' != $params['ondblclick']) {
                        $ondblclick = "
                          ondblclick=\"
                            let id = ".(int) $val['id'].";
                            {$onclick_available_columns_js}
                            {$params['ondblclick']}
                          \"
                        ";
                    }

                    $row_css = $this->model->tableRowCSSFormatter([
                      'table' => $this,
                      'row' => $val,
                    ]);

                    $html .= "<div ";
                      $html .= "class='table_tr data_tr ".($params['sortable'] ? 'sortable' : '')."' ";
                      $html .= $onclick;
                      $html .= $ondblclick;
                      $html .= "data-id='{$val['id']}' ";
                      $html .= "style='{$row_css}'";
                    $html .= ">";

                    if ($params['show_multiselect']) {
                        $multiselect_html = "
                            <div class='table_td' onclick='event.cancelBubble = true; $(\"#{$params['uid']}_multiselect_{$val['id']}\").trigger(\"click\"); ' >
                                <input type='checkbox' class='{$params['uid']}_multiselect' id='{$params['uid']}_multiselect_{$val['id']}' data-id='{$val['id']}' onclick='event.cancelBubble = true;' />
                            </div>
                        ";
                    } else {
                        $multiselect_html = '';
                    }

                    if ('left' == $params['multiselect_position']) {
                        $html .= $multiselect_html;
                    }

                    foreach ($this->columns as $col_name => $col_def) {
                        if (!empty($col_def['input']) && is_string($col_def['input'])) {
                            $inputClassName = "\\ADIOS\\".str_replace("/", "\\", $col_def['input']);
                            $tmpInput = new $inputClassName($this->adios, "", ["value" => $val[$col_name]]);
                            $cell_html = $tmpInput->formatValueToHtml();
                        } else if ($this->adios->db->is_registered_column_type($col_def['type'])) {
                            $cell_html = $this->adios->db->registered_columns[$col_def['type']]->get_html($val[$col_name], [
                              'wa_list_html' => true,
                              'col_name' => $col_name,
                              'col_definition' => $col_def,
                              'row' => $val,
                            ]);
                        } else {
                            $cell_html = $val[$col_name];
                        }

                        if (empty($cell_html)) {
                            $cell_html = "<i style='color:#ccc'>[N/A]</i>";
                        }

                        if ((in_array($col_def['type'], ['int', 'float']) && !is_array($col_def['enum_values']))) {
                            $align_class = 'align_right';
                        } else {
                            $align_class = 'align_left';
                        }

                        $col_def['style'] .= $this->model->tableCellCSSFormatter([
                          'table' => $this,
                          'column' => $col_name,
                          'row' => $val,
                          'value' => $val[$col_name],
                        ]);

                        $tmp_formatter_res = $this->model->tableCellHTMLFormatter([
                          'table' => $this,
                          'column' => $col_name,
                          'row' => $val,
                          'html' => $cell_html,
                        ]);
                        
                        // ak nevrati ziadne html, neprepisuje sa
                        if ($tmp_formatter_res !== NULL) {
                          $cell_html = $tmp_formatter_res;
                        }

                        $html .= "
                            <div
                                class='
                                    table_td data_td
                                    {$col_def['class']}
                                    ".('' != $col_def['white_space'] ? $col_def['white_space'] : $params['white_space'])." {$align_class}
                                '
                                style='{$col_def['style']}'
                            >
                                {$cell_html}
                            </div>
                        ";
                    }

                    if ('right' == $params['multiselect_position']) {
                        $html .= $multiselect_html;
                    }

                    if ($params['show_delete_column']) {
                        $html .= "
                          <div class='table_td delete_column' onclick=' event.cancelBubble = true; _confirm(\"".l('Naozaj chcete zmazať záznam?')."\", {}, function(){ ui_table_delete_item(\"{$params['uid']}\", {$val['id']}); });'>
                            <img src='{$this->adios->config['adios_images_url']}/black/app/delete.png' />
                          </div>
                        ";
                    }

                    $html .= '</div>';
                }

                // koniec tabulky
                $html .= '  </div>
                </div>';
            } else {
                // koniec tabulky
                $html .= "
                    </div>
                    <div class='info-no-data'>".hsc($params['info_no_data'])."</div>
                  </div>
                ";
            }

            // nastavenia


            if ($params['show_controls']) {
              $html .= "
                <div class='table_paging'>
                  <div class='table_paging_content'>
                    <div class='count'>
                      ".parent::render('count')."
                    </div>
                    <div class='paging'>
                      ".parent::render('paging')."
                    </div>
                    <div class='settings'>
                      ".parent::render('settings')."
                    </div>
                  </div>
                </div>
              ";
            }

        }

        // koniec obsahu
        if (!$this->params['refresh']) { // || $_REQUEST['adios_history_go_back']) {
            $html .= '</div>';
        }

        if ($params['__IS_WINDOW__']) {
          $html = $this->adios->ui->Window(
            [
              'uid' => "{$this->uid}_window",
              'content' => $html,
              'header' => [
                $this->adios->ui->Button(["text" => "Zavrieť", "type" => "close", "onclick" => "ui_form_close('{$this->uid}_window');"]),
              ],
              'title' => " ",
            ]
          )->render();
        } else {
          //
        }

        return \ADIOS\Core\HelperFunctions::minifyHtml($html);
    }
}
