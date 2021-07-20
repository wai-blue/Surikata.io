<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Form extends \ADIOS\Core\UI\View
{
  public function __construct(&$adios, $params = null)
  {

    $this->adios = &$adios;
    $gtp = $this->gtp;

    // defaultne parametre

    $params = parent::params_merge([
      'table' => '',
      'id' => '-1',
      'title' => '',
      'formatter' => 'ui_form_formatter',
      'columns_order' => [],
      'default_values' => [],
      'readonly' => false,
      'template' => [],
      'template_callback' => '',
      'show_save_button' => true,
      'save_button_params' => [],
      'show_close_button' => true,
      'close_button_params' => [],
      'show_delete_button' => true,
      'delete_button_params' => [],
      'show_copy_button' => false,
      'copy_button_params' => [],
      'append_buttons' => [],
      'form_type' => 'window',
      'window_uid' => '',
      'window_params' => [],
      'show_modal' => false,
      'width' => 700,
      'height' => '',
      'onclose' => '',
      'hide_id_column' => true,
      'save_action' => 'UI/Form/Save',
      'delete_action' => 'UI/Table/Delete',
      'copy_action' => 'UI/Table/Copy',
      'do_not_close' => false,
      'onbeforesave' => '',
      'onaftersave' => '',
      'onbeforeclose' => '',
      'onafterclose' => '',
      'onbeforedelete' => '',
      'onafterdelete' => '',
      'onbeforecopy' => '',
      'onaftercopy' => '',
      'onload' => '',
      'simple_insert' => false,
      'javascript' => '',
      'refresh_table_onclose' => false,
    ], $params);

    // nacitanie udajov
    if (empty($params['model'])) {
      exit("UI/Form: Don't know what model to work with.");
      return;
    }

    $this->model = $this->adios->getModel($params['model']);
    $this->data = $this->model->getById($params['id']);

    $params['table'] = $this->model->getFullTableSQLName();

    if (empty($params['uid'])) {
      $params['uid'] = $this->adios->getUid("{$params['model']}_{$params['id']}");
    }

    if (empty($params['title'])) {
      $tmpFormTitle = ($params['id'] <= 0 ? $this->model->formTitleForInserting : $this->model->formTitleForEditing);
      if (empty($tmpFormTitle)){
        $this->params['title'] = "{$this->params['model']}: ".($this->params['id'] == -1
          ? "Nový záznam"
          : "Upraviť záznam č. {$this->params['id']}"
        );
      } else {
        $params['title'] = $tmpFormTitle;
      }
    }

    parent::__construct($adios, $params);

    $this->params['columns'] = $this->adios->db->tables[$this->params['table']];

    $this->params = $this->model->formParams($this->data, $this->params);

    unset($this->params['columns']['id']);

    // default values
    if (is_string($this->params['default_values'])) {
      $this->params['default_values'] = @json_decode($this->params['default_values'], TRUE);
    }
    if (_count($this->params['default_values']) && $this->params['id'] <= 0) {
      foreach ($this->params['default_values'] as $col_name => $def_value) {
        $this->data[$col_name] = $def_value;
      }
    }

    foreach ($this->data as $key => $value) {
      if (is_string($value)) {
        $this->params['title'] = str_replace("{{ {$key} }}", hsc($value), $this->params['title']);
      }
    }

    // $this->params['show_save_button'] = ('UI/Form/Save' != $this->params['save_action'] || '' != $this->params['save_button_params']['onclick'] ? $this->params['show_save_button'] : false);
    // $this->params['show_delete_button'] = ('UI/Table/Delete' != $this->params['delete_action'] || '' != $this->params['delete_button_params']['onclick'] ? $this->params['show_delete_button'] : false);
    // $this->params['show_copy_button'] = ('UI/Table/Copy' != $this->params['copy_action'] || '' != $this->params['copy_button_params']['onclick'] ? $this->params['show_copy_button'] : false);

    if (!($this->params['id'] > 0)) {
      $this->params['show_delete_button'] = false;
    }
    if (!($this->params['id'] > 0)) {
      $this->params['show_copy_button'] = false;
    }

    if ($this->params['show_save_button']) {
      if ($params['id'] <= 0) {
        $this->params['save_button_params']['type'] = 'add';
        if (!empty($this->model->formAddButtonText)) {
          $this->params['save_button_params']['text'] = $this->model->formAddButtonText;
        }
      } else {
        $this->params['save_button_params']['type'] = 'save';
        if (!empty($this->model->formSaveButtonText)) {
          $this->params['save_button_params']['text'] = $this->model->formSaveButtonText;
        }
      }
      if ('' == $this->params['save_button_params']['onclick']) {
        $this->params['save_button_params']['onclick'] = "ui_form_save('{$this->params['uid']}', {}, this);";
      }
      $this->params['save_button_params']['class'] = "btn-save";
      $this->params['save_button_params']['id'] = "{$this->params['uid']}_save_btn";
      $this->save_button = $this->adios->ui->button($this->params['save_button_params']);
    }

    if ($this->params['show_close_button']) {
      if ('' == $this->params['close_button_params']['type']) {
        $this->params['close_button_params']['type'] = 'close';
      }
      if ('' == $this->params['close_button_params']['onclick']) {
        $this->params['close_button_params']['onclick'] = "ui_form_close('{$this->params['uid']}');";
      }
      $this->close_button = $this->adios->ui->button($this->params['close_button_params']);
    }

    if ($this->params['show_delete_button']) {
      if ('' == $this->params['delete_button_params']['type']) {
        $this->params['delete_button_params']['type'] = 'delete';
      }
      if ('' == $this->params['delete_button_params']['onclick']) {
        $this->params['delete_button_params']['onclick'] = "
          _confirm(
            'You are about to delete the record. Continue?',
            {
              'content_class': 'border-left-danger',
              'confirm_button_class': 'btn-danger',
              'confirm_button_text': 'Yes, delete the record',
              'cancel_button_text': 'Do not delete',
            },
            function() { ui_form_delete('{$this->params['uid']}') }
          );
        ";
      }
      $this->params['delete_button_params']['style'] .= 'float:right;';
      $this->delete_button = $this->adios->ui->button($this->params['delete_button_params']);
      $this->delete_button->add_class("{$this->params['uid']}_button");
    }

    if ($this->params['show_copy_button']) {
      if ('' == $this->params['copy_button_params']['type']) {
        $this->params['copy_button_params']['type'] = 'copy';
      }
      if ('' == $this->params['copy_button_params']['onclick']) {
        $this->params['copy_button_params']['onclick'] = "_confirm('".l('Naozaj si želáte kopírovať záznam')."?', {}, function(){ ui_form_copy('{$this->params['uid']}') });";
      }
      $this->params['copy_button_params']['style'] .= 'float:right;';
      $this->copy_button = $this->adios->ui->button($this->params['copy_button_params']);
      $this->copy_button->add_class("{$this->params['uid']}_button");
    }

    if (empty($this->params['header'])) {
      $this->params['window']['header'] = [
        $this->close_button,
        $this->save_button,
        $this->delete_button,
        $this->copy_button
      ];
    }

    if ('' == $this->params['window_uid']) {
      $this->params['window_uid'] = $this->params['uid'].'_form_window';
    }
    if ('' != $this->params['window_params']['uid']) {
      $this->params['window_uid'] = $this->params['window_params']['uid'];
    }

    if ('desktop' == $this->params['form_type']) {
      if (is_array($this->params['title_params']['left'])) {
        $this->params['title_params']['left'] = array_merge([$this->close_button, $this->save_button], $this->params['title_params']['left']);
      } elseif ('' != $this->params['title_params']['left']) {
        $this->params['title_params']['left'] = [$this->close_button, $this->save_button, $this->params['titles']['left']];
      } else {
        $this->params['title_params']['left'] = [$this->close_button, $this->save_button];
      }

      if (is_array($this->params['title_params']['right'])) {
        $this->params['title_params']['right'] = array_merge([$this->copy_button, $this->delete_button], $this->params['title_params']['right']);
      } elseif ('' != $this->params['title_params']['right']) {
        $this->params['title_params']['right'] = array_merge([$this->copy_button, $this->delete_button], [$this->params['title_params']['right']]);
      } else {
        $this->params['title_params']['right'] = [$this->copy_button, $this->delete_button];
      }

      if ('' == $this->params['title_params']['center']) {
        $this->params['title_params']['center'] = $this->params['title'];
      }
      $this->add($this->adios->ui->Title($this->params['title_params']), 'title');
    }
  }

  // renderRows
  function renderRows($rows) {
    $html = "";

    if (!empty($rows['action'])) {
      // ak je definovana akcia, generuje akciu s parametrami
      $html = $this->adios->renderAction($rows['action'], $rows['params']);
    } else if (is_callable($rows['template'])) {
      // template je definovany ako anonymna funkcia
      $html = $rows['template']($this->params['columns'], $this);
    } else if (is_string($rows)) {
      $html = $rows;
    } else {
      $html = "
        <div class='adios ui Form default_table_wrapper'>
          <div class='adios ui Form table'>
            <div class='adios ui Form subrow save_error_info' style='display:none'>
              ".$this->translate("Some of the required fields are empty.")."
            </div>
      ";
      foreach ($rows as $row) {
        if (is_string($row)) {
          $html .= "
            <div
              class='
                adios ui Form subrow
                ".($this->params['columns'][$row]['required'] ? "required" : "")."
                ".(empty($this->params['columns'][$row]['pattern']) ? "" : "has_pattern")."
              '
            >
              <div class='adios ui Form form_title'>
                ".hsc($this->params['columns'][$row]['title'])."
              </div>
              <div class='adios ui Form form_input'>
                ".$this->Input($row, $this->data, $this->params['model'])."
              </div>
              ".(empty($this->params['columns'][$row]['description']) ? "" : "
                <div class='adios ui Form form_description'>
                  ".hsc($this->params['columns'][$row]['description'])."
                </div>
              ")."
            </div>
          ";
        } else if (is_string($row['html'])) {
          $html .= "
            <div class='adios ui Form subrow'>
              {$row['html']}
            </div>
          ";
        } else if (is_string($row['title']) && is_string($row['input'])) {
          $html .= "
            <div class='adios ui Form subrow'>
              <div class='adios ui Form form_title {$row['class']}'>
                {$row['title']}
              </div>
              <div
                class='adios ui Form form_input {$row['class']}'
                style='{$row['style']}'
              >
                {$row['input']}
              </div>
              ".(empty($row['description']) ? "" : "
                <div class='adios ui Form form_description'>
                  ".hsc($row['description'])."
                </div>
              ")."
            </div>
          ";
        }
      }
      $html .= "
          </div>
        </div>
      ";
    }

    return $html;
  }

  // render

  public function render($render_panel = '') {
    
    if (!_count($this->params['columns'])) {
      $this->adios->console->log('UI/Form', "No columns provided: {$this->params['model']}");
    }

    $html = "";

    if (is_callable($this->params['formatter'])) {
      $html .= $this->params['formatter']('before_html', $this, []);
    }

    foreach ($this->params['columns'] as $col_name => $col_def) {

      $this->params['columns'][$col_name]['row_id'] = $this->data['id'];

      // andy test - mozno sposobi problemy v o forme, ale riesi moznost zmenit enum_values v ui formularu cez objekt - inak by dochadzalo k opatovnemu merge enum_values v input komponente
      // nahradene specialitkou pre table input - vid if nizsie
      //if ($this->params['table'] != '') $this->params['columns'][$col_name]['table_column'] = $this->params['table'].".".$col_name;
      if ('table' == $col_def['type']) {
        if ('' != $col_def['child_table']) {
          $this->params['columns'][$col_name]['default_table'] = $col_def['child_table'];
        } else {
          $this->params['columns'][$col_name]['default_table'] = $this->params['table'];
        }
        $this->params['columns'][$col_name]['default_column'] = $col_name;
      }
      if ($this->params['readonly']) {
        $this->params['columns'][$col_name]['readonly'] = true;
      }
      if ('' !== $this->data[$col_name] && _count($this->data) && isset($this->data[$col_name])) {
        if (!$col_def['no_view_permissions']) {
          $this->params['columns'][$col_name]['value'] = $this->data[$col_name];
        }
      }
      if ($col_def['virtual'] || 'none' == $col_def['type'] || 'virtual' == $col_def['type']) {
        unset($this->params['columns'][$col_name]);
      }
    }

    // params['template']
    if (empty($this->params['template'])) {
      $this->params['template'] = [
        "columns" => [
          [
            "rows" => array_keys($this->params['columns']),
          ],
        ],
      ];

    }

    if (_count($this->params['columns'])) {

      // renderovanie template

      if (is_callable($this->params['template'])) {

        // cely template definovany ako anonymna funkcia vracajuca HTML
        $form_content_html = $this->params['template']($this->params['columns'], $this);

      } else {

        $cols_html = [];

        foreach ($this->params['template']['columns'] as $col) {

          $col_html = "<div class='".($col["class"] ?? "col-12 px-0")."'>";

          if (is_string($col)) {
            $col_html .= $col;
          } else if (!empty($col['rows'])) {
            $col_html .= $this->renderRows($col['rows']);
          } else if (is_array($col['tabs'])) {
            $tab_pages = [];

            // kazdy element predstavuje jeden tab vo formulari
            foreach ($col['tabs'] as $tab_name => $rows) {
              $tab_pages[] = [
                'title' => $tab_name,
                'content' => $this->renderRows($rows),
              ];

            }

            $col_html .= $this->adios->ui->Tabs(parent::params_merge(
              [
                'padding' => false,
                'height' => "calc(100vh - 17em)",
                'tabs' => $tab_pages
              ],
              $this->params['tab_params']
            ))->render();

          } else if (is_string($col['html'])) {
            $col_html .= $col['html'];
          } else if (is_array($col['content'])) {
            foreach ($col['content'] as $element) {
              if (is_string($element)) {
                $col_html .= $element;
              } else {
                $col_html .= $element->render();
              }
            }
          }

          $col_html .= "</div>";

          $cols_html[] = $col_html;
        }

        //////////////////////////////
        // FORM_CONTENT_HTML

        $form_content_html = "
          <div class='row'>
            ".join("", $cols_html)."
          </div>
        ";

      }

      $html .= '
        <div
          '.$this->main_params()."
          data-save-action='{$this->params['save_action']}'
          data-delete-action='{$this->params['delete_action']}'
          data-copy-action='{$this->params['copy_action']}'
          data-id='{$this->params['id']}'
          data-model='{$this->params['model']}'
          data-table='{$this->params['table']}'
          data-do-not-close='{$this->params['do_not_close']}'
          data-window-uid='{$this->params['window_uid']}'
          data-form-type='{$this->params['form_type']}'
          data-refresh-table-onclose='".($this->params['refresh_table_onclose'] ? 1 : 0)."'
          data-is-ajax='".($this->params['__IS_AJAX__'] || $this->adios->isAjax() ? "1" : "0")."'
        >
          {$form_content_html}
        </div>
      ";

    }
    if (is_callable($this->params['formatter'])) {
      $html .= $this->params['formatter']('after_html', $this, []);
    }

    $this->params['onclose'] = $this->params['form_onclose'].$this->params['onclose'];

    $html .= "
      <script>

        function {$this->params['uid']}_onbeforesave(uid, data, params){
          var allowed = true;
          {$this->params['onbeforesave']}
          return {data: data, allowed: allowed}
        }
        function {$this->params['uid']}_onaftersave(uid, data, params){
          {$this->params['onaftersave']}

          ".($this->params['simple_insert'] ?
          "var re_render_params = $.parseJSON(decodeURIComponent('".rawurlencode(json_encode($_REQUEST))."'));
          re_render_params['simple_insert'] = 0;
          re_render_params.id = data.inserted_id;
          re_render_params.after_simple_insert = 1;
          window_render('{$this->adios->action}', re_render_params);" : '')."
          return {}
        }

        function {$this->params['uid']}_onbeforedelete(uid, data, params){
          var allowed = true;
          {$this->params['onbeforedelete']}
          return {data: data, allowed: allowed}
        }
        function {$this->params['uid']}_onafterdelete(uid, data, params){
          {$this->params['onafterdelete']}
          return {}
        }

        function {$this->params['uid']}_onbeforeclose(uid, data, params){
          var allowed = true;
          {$this->params['onbeforeclose']}
          return {data: data, allowed: allowed}
        }
        function {$this->params['uid']}_onafterclose(uid, data, params){
          {$this->params['onafterclose']}
          return {}
        }

        function {$this->params['uid']}_onbeforecopy(uid, data, params){
          var allowed = true;
          {$this->params['onbeforecopy']}
          return {data: data, allowed: allowed}
        }
        function {$this->params['uid']}_onaftercopy(uid, data, params){
          {$this->params['onaftercopy']}
          return {}
        }

        ".('' != $this->params['onclose'] ?
          "function {$this->params['uid']}_ondesktopclose(uid, data, params){
          {$this->params['onclose']}
          return {}
          }" : '').'

        '.('' != $this->params['onclose'] ?
          "function {$this->params['uid']}_onclose(uid, data, params){
          {$this->params['onclose']}
          return {}
          }" : '').'

        '.$this->params['javascript']."

        $(document).ready(function(){
          var uid = '{$this->params['uid']}';
          ".$this->params['onload'].'

        });
      </script>
    ';

    if ('window' == $this->params['form_type']) {
      $window_params = parent::params_merge(
        [
          'content' => $html,
          'header' => $this->params['window']['header'],
          'footer' => $this->params['window']['footer'],
          'show_modal' => $this->params['show_modal'],
          'form_close_click' => $this->params['close_button_params']['onclick'],
          'uid' => $this->params['window_uid'],
          'titleRaw' => $this->params['titleRaw'],
          'title' => $this->params['title'],
          'subtitle' => $this->params['subtitle'],
        ],
        $this->params['window_params']
      );
      $html = $this->adios->ui->Window($window_params)->render();
    } elseif ('desktop' == $this->params['form_type']) {
      $html = parent::render('title').$html;
    } elseif ('form' == $this->params['form_type']) {
      $html = $html;
    }

    return $html;
  }

  public function Input($colName, $formData = NULL, $initiatingModel = NULL) {
    return $this->adios->ui->Input(
      array_merge(
        [
          'uid' => $this->params['uid'].'_'.$colName,
          'form_uid' => $this->params['uid'],
          'form_data' => $formData,
          'initiating_column' => $colName,
          'initiating_model' => $initiatingModel,
        ],
        $this->params['columns'][trim($colName)] ?? []
      )
    )->render();
  }

}
