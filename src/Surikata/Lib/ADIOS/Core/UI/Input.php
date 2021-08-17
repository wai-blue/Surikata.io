<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Input extends \ADIOS\Core\UI\View {
    /*             */
    /* __construct */
    /*             */
    public function __construct(&$adios, $params = null) {
        $this->adios = $adios;

        $this->supported_events = [
            'onclick',
            'ondblclick',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmouseover',
            'onmouseout',
            'onmouseup',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onblur',
            'onchange',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'oninput',
            'onselect',
        ];

        $this->params = [
            'table_column' => '',
            'type' => '',
            'width' => '',
            'value' => '',
            'html_attributes' => '',
            'placeholder' => '',
            'readonly' => false,
            'default_color_value' => '#000000',
            'default_date_value' => '',
            'max_date' => '',
            'min_date' => '',
            'enum_values' => '',
            'decimals' => 2,
            'interface' => '',
            'editor' => [],
            'rename_file' => true,
            // 'subdir' => 'upload',
            'show_file_browser' => true,
            'show_download_url_button' => true,
            'show_open_button' => true,
            'show_delete_button' => true,
            'allowed_extensions' => '',
            'where' => '',
            'follow_lookups' => true,
            'order' => '',
            'table' => '',
            'not_selected_text' => '',
            'lookup_auto_limit' => 100,
            'input_style' => '',
            'lookup_detail_enabled' => true,
            'lookup_detail_onclick' => '',
            'lookup_search_enabled' => true,
            'lookup_search_onclick' => '',
            'lookup_add_enabled' => false,
            'lookup_add_onclick' => '',
            'gc_function' => '',
            'table_checkboxes_cols' => '',
            'text_onchange' => '',
            'help' => '',
            'unit' => '',
            'table_related_column' => '',
            'parent_column' => '',
            'translate_value' => false,
            'lookup_search_type' => '',
            'hide_selected_text_option' => false,
            'show_lookup_not_selected_option' => true,
            'disabled' => false,
        ];

        // nacita parametre z tables a zmerguje s obdrzanymi
        if ('' != $params['table_column']) {
            $tmp_col = explode('.', $params['table_column']);
            $params['default_table'] = $tmp_col[0];
            $params['default_column'] = $tmp_col[1];
            $params = parent::params_merge($this->adios->db->tables[$tmp_col[0]][$tmp_col[1]], $params);
        }

        if (!empty($params['model'])) {
          $this->model = $adios->getModel($params['model']);
        }
        
        if (empty($params['table']) && !empty($params['model'])) {
          $params['table'] = $adios->getModel($params['model'])->getFullTableSQLName();
        }

        parent::__construct($adios, $params);

        $this->add_class($this->params['type']);
    }

    /*        */
    /* render */
    /*        */
    public function render($render_panel = '') {

        if (!empty($this->params['input'])) {
          if (is_string($this->params['input'])) {
            $tmpParams = $this->params;
            unset($tmpParams['uid']);
            $inputClassName = "\\ADIOS\\".str_replace("/", "\\", $this->params['input']);
            $input = new $inputClassName($this->adios, $this->params['uid'], $tmpParams);
            return $input->render();
          } else if (is_array($this->params['input'])) {
            $this->params = array_merge($this->params, $this->params['input']);
            unset($this->params['input']);
          }
        }

        if ($this->params['disabled']) {
            $this->params['readonly'] = true;
        }

        if ('' != $this->params['gc_function'] && is_callable($this->params['gc_function'])) {
            $html = $this->params['gc_function']($this->params, $this);

            return $html;
        }

        // pre inputy, ktore su disabled sa nastavi tento parameter, aby sa nedostali do udajov selectovanych cez ui_form_get_values
        if ($this->params['disabled']) {
            $adios_disabled_attribute = "adios-do-not-serialize='1'";
        }

        /* bool */
        if (
          $this->params['type'] == 'bool'
          || $this->params['type'] == 'boolean'
        ) {
          // hodnota
          if ('bool' == $this->params['type']) {
            if (1 === $this->params['value'] || true === $this->params['value'] || 'Y' === $this->params['value']) {
              $this->params['value'] = 'Y';
            } else {
              $this->params['value'] = 'N';
            }
            $true_val = 'Y';
          } else {
            if (0 != $this->params['value']) {
              $this->params['value'] = 1;
            }
            $true_val = '1';
          }

          $html = "
            <input
              type='checkbox'
              id='{$this->params['uid']}'
              name='{$this->params['uid']}'
              data-is-adios-input='1'
              class='ui_input_type_boolean ".join(' ', $this->classes)."'
              ".$this->generate_input_events().'
              '.($this->params['value'] == $true_val ? "checked='checked'" : '')."
              {$this->params['html_attributes']}
              value='{$true_val}'
              ".($this->params['readonly'] ? "disabled='disabled'" : '')."
            />

            <label for='{$this->params['uid']}'>
              <i class='input_bool_true fas fa-check-square'></i>
              <i class='input_bool_false fas fa-square'></i>
            </label>
          ";
        }

        /* varchar / int (s enum_values) */
        if (
          in_array($this->params['type'], ['int', 'varchar'])
          && is_array($this->params['enum_values'])
        ) {
          $html = "
            <select
              name='{$this->params['uid']}'
              data-is-adios-input='1'
              ".$this->main_params()."
              ".$this->generate_input_events()."
              title=\"".htmlspecialchars($this->params['title'])."\"
              {$this->params['html_attributes']}
              ".($this->params['readonly'] ? "disabled='disabled'" : '')."
            >
              ".($this->params['type'] == 'int' && empty($this->params['enum_values'][0])
                ? "<option value=''>".('' == $this->params['not_selected_text'] ? l('unselected') : $this->params['not_selected_text']).'</option>'
                : ""
              )."
          ";

          foreach ($this->params['enum_values'] as $enum_key => $enum_value) {
            if (strval($this->params['value']) === strval($enum_key)) {
              $sel = 'selected';
            } else {
              $sel = '';
            }
            $html .= "
              <option value='{$enum_key}' {$sel}>
                ".hsc($enum_value)."
              </option>
            ";
          }

          $html .= '</select>';
        }

        /* text (plain text) */
        if ('text' == $this->params['type'] && ('' == $this->params['interface'] || 'plain_text' == $this->params['interface'] || 'text' == $this->params['interface'])) {
          $html .= "
            <textarea
              id='{$this->params['uid']}'
              name='{$this->params['uid']}'
              data-is-adios-input='1'
              ".$this->main_params().'
              '.$this->generate_input_events().'
              title="'.htmlspecialchars($this->params['title']).'"
              placeholder="'.htmlspecialchars($this->params['placeholder'])."\"
              {$this->params['html_attributes']}
              ".($this->params['readonly'] ? "disabled='disabled'" : '').'
            >'.htmlspecialchars($this->params['value']).'</textarea>
          ';
        }

        /* text (json editor) */
        if ('text' == $this->params['type'] && $this->params['interface'] == 'json_editor') {
          $html .= "
            <div class='row'>
              <div class='col-lg-8'>
                <div id='{$this->params['uid']}_editor'></div>
              </div>
              <div class='col-lg-4'>
                <textarea
                  name='{$this->params['uid']}'
                  data-is-adios-input='1'
                  ".$this->main_params()."
                  style='font-size:0.8em;background:#EEEEEE;height:2em;opacity:0.5'
                >".htmlspecialchars($this->params['value'])."</textarea>
              </div>
            </div>
            <script>
              var {$this->params['uid']}_editorOptions = {
                schema: ".json_encode($this->params['schema']).",
                theme: 'bootstrap4',
                disable_collapse: true,
                disable_edit_json: true,
                disable_properties: true,
              }

              ".(empty($this->params['value']) ? "" : "
                {$this->params['uid']}_editorOptions.startval =
                  ".json_encode(json_decode($this->params['value'], TRUE))."
                ;
              ")."

              var editor = new JSONEditor(
                document.getElementById('{$this->params['uid']}_editor'),
                {$this->params['uid']}_editorOptions
              );

              editor.on('change', function() {
                document.getElementById('{$this->params['uid']}').value = JSON.stringify(editor.getValue());
              });

            </script>
            <style>
              #{$this->params['uid']}_editor h3.card-title { display: none !important; }
              #{$this->params['uid']}_editor span.btn-group.je-object__controls { display: none !important; }
            </style>
          ";
        }

        /* text (editor) */
        if ('text' == $this->params['type'] && $this->params['interface'] == 'formatted_text') {
            $html .= "
              <textarea
                name='{$this->params['uid']}'
                data-is-adios-input='1'
                style='display:none'
                ".$this->main_params()."
              >".hsc($this->params['value'])."</textarea>

              <div id='{$this->params['uid']}_editor'></div>
              <script>
                setTimeout(function() {
                  var {$this->params['uid']}_quill = new Quill('#{$this->params['uid']}_editor', {
                    theme: 'snow',
                    placeholder: '".ads($this->params['placeholder'])."',
                    readOnly: ".($this->params['readonly'] ? "true" : "false").",
                  });

                  let delta = {$this->params['uid']}_quill.clipboard.convert(
                    $('#{$this->params['uid']}').val()
                  );
                  
                  {$this->params['uid']}_quill.setContents(delta);
                  
                  {$this->params['uid']}_quill.on('editor-change', function(eventName, ...args) {
                    $('#{$this->params['uid']}').val({$this->params['uid']}_quill.root.innerHTML);
                  });
                  

                }, 10);
              </script>
            ";

            $html .= "
            ";
        }

        /* password */
        if ($this->params['type'] == 'password') {
          $this->add_class("ui_input_type_password");

          $html .= "
            <input
              type='hidden'
              id='{$this->params['uid']}'
              data-is-adios-input='1'
              value=''
            /> <!-- toto tu je iba preto, aby do formSave() presli inputy '_1' a '_2' -->
            <input
              type='password'
              id='{$this->params['uid']}_1'
              name='{$this->params['uid']}_1'
              ".$this->main_params()."
              title='".htmlspecialchars($this->params['title'])."'
              placeholder='New password'
              ".($this->params['readonly'] ? "disabled='disabled'" : '')."
              onkeyup='{$this->params['uid']}_check_passwords();'
            />
            <input
              type='password'
              id='{$this->params['uid']}_2'
              name='{$this->params['uid']}_2'
              ".$this->main_params()."
              title='".htmlspecialchars($this->params['title'])."'
              placeholder='Confirm new password'
              ".($this->params['readonly'] ? "disabled='disabled'" : '')."
              onkeyup='{$this->params['uid']}_check_passwords();'
            />
            <script>
              function {$this->params['uid']}_check_passwords() {
                let input_1 = $('#{$this->params['uid']}_1');
                let input_2 = $('#{$this->params['uid']}_2');
                let pswd_1 = input_1.val();
                let pswd_2 = input_2.val();

                input_1.removeClass('password_mismatch');
                input_2.removeClass('password_mismatch');

                if (pswd_1 != '') {
                  if (pswd_1 == pswd_2) {
                  } else {
                    input_1.addClass('password_mismatch');
                    input_2.addClass('password_mismatch');
                  }
                }
              }
            </script>
          ";
        }

        /* char, varchar */
        /* color */
        /* date, datetime, time, timestamp */
        /* int (bez enum_values), year */
        /* float */
        /* text (single_line) */
        if (
          ($this->params['type'] == 'varchar' && !_count($this->params['enum_values']))
          || ($this->params['type'] == 'int' && !_count($this->params['enum_values']))
          || ($this->params['type'] == 'text' && 'single_line' == $this->params['interface'])
          || in_array(
            $this->params['type'],
            ['color', 'date', 'datetime', 'timestamp', 'float', 'year', 'time']
          )
        ) {

            if ($this->params['type'] == 'color') {
              $input_type = 'color';
            } else {
              $input_type = "text";
            }

            /* date */
            if ('date' == $this->params['type']) {
                if ('' == $this->params['placeholder']) {
                    $this->params['placeholder'] = 'dd.mm.yyyy';
                }
                if ('' == $this->params['value']) {
                    $this->params['value'] = ('' != $this->params['default_date_value'] ? $this->params['default_date_value'] : '');
                }
                $this->params['value'] = (false !== strtotime($this->params['value']) ? date($this->adios->locale->dateFormat(), strtotime($this->params['value'])) : '');
            }

            /* datetime, timestamp */
            if ('datetime' == $this->params['type'] || 'timestamp' == $this->params['type']) {
                if ('' == $this->params['placeholder']) {
                    $this->params['placeholder'] = 'dd.mm.yyyy hh:mm';
                }
                if ('' == $this->params['value']) {
                    $this->params['value'] = ('' != $this->params['default_date_value'] ? $this->params['default_date_value'] : '');
                }
                $this->params['value'] = (false !== strtotime($this->params['value']) ? date('d.m.Y H:i:s', strtotime($this->params['value'])) : '');
            }

            /* float */
            if ('float' == $this->params['type']) {
                if (!($this->params['decimals'] > 0)) {
                    $this->params['decimals'] = 2;
                }
                // $this->params['onchange'] = " ui_input_check_float_number($(this), {$this->params['decimals']}, '".l('Zadajte číselnú hodnotu')."'); ".$this->params['onchange'];
            }

            /* int - slider */
            if ('int' == $this->params['type'] && 'slider' == $this->params['input_style']) {
                $input_type = 'hidden';
            }

            /* time */
            if ('time' == $this->params['type']) {
                if ('' == $this->params['placeholder']) {
                    $this->params['placeholder'] = 'hh:mm:ss';
                }
                // medzera s x je tam naschval kvoli parsovaniu do inputov. nemazat
                $this->params['onkeyup'] = "ui_input_parse_time('{$this->params['uid']}', 'x '+this.value); ".$this->params['onchange'];
                $this->params['onkeydown'] = ' if (event.which > 31 && (event.which < 48 || event.which > 57) && event.which != 186) return false; '.$this->params['onchange'];
            }

            $this->params['onkeyup'] .= "
              $(this).removeClass('invalid');
              if (!this.checkValidity()) {
                $(this).addClass('invalid');
              }
            ";

            $this->add_class("ui_input_type_{$this->params['type']}");

            if (empty($this->params['placeholder'])) {
                $tmp_placeholder = $this->params['title'];
            } else {
                $tmp_placeholder = $this->params['placeholder'];
            }

            $html .= "
              <span style='white-space:nowrap'>
                <input
                  name='{$this->params['uid']}'
                  data-is-adios-input='1'
                  ".$this->main_params()."
                  type='{$input_type}'
                  ".($this->params['pattern'] == "" ? "" : "pattern='".hsc($this->params['pattern'])."'")."
                  ".$this->generate_input_events()."
                  value=\"".htmlspecialchars($this->params['value'])."\"
                  title=\"".htmlspecialchars($this->params['title'])."\"
                  placeholder=\"".htmlspecialchars($tmp_placeholder)."\"
                  {$this->params['html_attributes']}
                  onkeypress='if (event.keyCode == 13) { ui_form_save(\"{$this->params['form_uid']}\"); }'
                  ".($this->params['readonly'] ? "disabled='disabled'" : '')."
                />

                ".($input_type == "color" ? "
                  <span style='margin-left:3em'>
                    <div style='background:#000000;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#000000\");'>&nbsp;</div>
                    <div style='background:#666666;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#666666\");'>&nbsp;</div>
                    <div style='background:#AAAAAA;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#AAAAAA\");'>&nbsp;</div>
                    <div style='background:#EEEEEE;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#EEEEEE\");'>&nbsp;</div>
                    <div style='background:#FFFFFF;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#FFFFFF\");'>&nbsp;</div>
                    <div style='background:#FF0000;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#FF0000\");'>&nbsp;</div>
                    <div style='background:#00FF00;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#00FF00\");'>&nbsp;</div>
                    <div style='background:#0000FF;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#0000FF\");'>&nbsp;</div>
                    <div style='background:#FFFF00;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#FFFF00\");'>&nbsp;</div>
                    <div style='background:#FF00FF;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#FF00FF\");'>&nbsp;</div>
                    <div style='background:#00FFFF;display:inline-block;width:1em;height:1em;cursor:pointer;border:1px solid #AAAAAA' onclick='$(\"#{$this->params['uid']}\").val(\"#00FFFF\");'>&nbsp;</div>
                  </span>
                " : "")."
              </span>
            ";

            //        //
            // slider //
            //        //

            /* int - slider */
            if ('int' == $this->params['type'] && 'slider' == $this->params['input_style']) {
                $html .= "
                  <div id='{$this->params['uid']}_slider' class='adios ui Input_slider'></div>
                  <script>
                    $('#{$this->params['uid']}_slider').slider({
                      disabled: ".($this->params['readonly'] ? 'true' : 'false').',
                      '.($this->params['max'] ? "max: {$this->params['max']}," : '').'
                      '.($this->params['min'] ? "min: {$this->params['min']}," : '').'
                      '.($this->params['step'] ? "step: {$this->params['step']}," : '').'
                      '.($this->params['value'] ? "value: {$this->params['value']}," : '')."
                      change: function( event, ui ) {
                        $('#{$this->params['uid']}').val(ui.value);
                        ".($this->params['onchange'] ? "{$this->params['onchange']}" : '').'
                      }
                    });
                  </script>
                ';
            }

            //               //
            // datumove veci //
            //               //

            if (!$this->params['readonly']) {
                /* date, datetime, timestamp */
                if ('date' == $this->params['type'] || 'datetime' == $this->params['type'] || 'timestamp' == $this->params['type']) {
                  $html .= "
                    <script>
                      $(function() {
                        $('#{$this->params['uid']}').datepicker({
                          changeYear: true,
                          dateFormat: 'dd.mm.yy".('datetime' == $this->params['type'] ? ' H:i:s' : '')."',
                          'buttonImage': '', //{$this->adios->config['adios_images_url']}/black/app/calendar-3.png',
                          'showOn': 'both',
                          'buttonImageOnly': true,
                          'constrainInput': true,
                          'dayNames': [ '".l('Nedeľa')."', '".l('Pondelok')."', '".l('Utorok')."', '".l('Streda')."', '".l('Štvrtok')."', '".l('Piatok')."', '".l('Sobota')."' ],
                          'dayNamesMin': [ '".l('Ne', '', ['context' => 'days'])."', '".l('Po', '', ['context' => 'days'])."', '".l('Ut', '', ['context' => 'days'])."', '".l('St', '', ['context' => 'days'])."', '".l('Št', '', ['context' => 'days'])."', '".l('Pi', '', ['context' => 'days'])."', '".l('So', '', ['context' => 'days'])."' ],
                          'monthNames' : [ '".l('Január')."', '".l('Február')."', '".l('Marec')."', '".l('Apríl')."', '".l('Máj')."', '".l('Jún')."', '".l('Júl')."', '".l('August')."', '".l('September')."', '".l('Október')."', '".l('November')."', '".l('December')."' ],
                          'nextText': '',
                          'prevText': '',
                          'defaultDate': '{$this->params['default_date_value']}',
                          'firstDay': 1,
                          ".('' != $this->params['max_date'] ? "'maxDate': '{$this->params['max_date']}'," : '').'
                          '.('' != $this->params['min_date'] ? "'minDate': '{$this->params['min_date']}'," : '').'
                          '.('datetime' == $this->params['type'] || 'timestamp' == $this->params['type'] ? "'onSelect': function(){ ui_input_datetime_change('{$this->params['uid']}'); }," : '').'
                        });

                        '.('datetime' == $this->params['type'] || 'timestamp' == $this->params['type'] ? " $('#{$this->params['uid']}').keyup(function(){ ui_input_parse_time('{$this->params['uid']}', $(this).val()); }); " : '').'
                      });
                    </script>
                  ';
                }

                /* datetime, timestamp */
                if ('datetime' == $this->params['type'] || 'timestamp' == $this->params['type']) {
                    $tmp_timeval = explode(' ', $this->params['value']);
                    $tmp_timeval = explode(':', $tmp_timeval[1]);
                    $html .= "
                      <input type='text' id='{$this->params['uid']}_time_hour_picker' class='adios ui Input ui_input_type_hour draggable' value='{$tmp_timeval[0]}' placeholder='HH' onchange=\" ui_input_datetime_change('{$this->params['uid']}'); \" /> :
                      <input type='text' id='{$this->params['uid']}_time_minute_picker' class='adios ui Input ui_input_type_minute draggable' value='{$tmp_timeval[1]}' placeholder='MM' onchange=\" ui_input_datetime_change('{$this->params['uid']}'); \"/> :
                      <input type='text' id='{$this->params['uid']}_time_second_picker' class='adios ui Input ui_input_type_second draggable' value='{$tmp_timeval[2]}' placeholder='SS' onchange=\" ui_input_datetime_change('{$this->params['uid']}'); \"/>
                      <script>
                        $(function() {
                          draggable_int_input('{$this->params['uid']}_time_hour_picker', {sensitivity: 6, min_val: 0, max_val: 23, callback: function(){ ui_input_datetime_change('{$this->params['uid']}'); } });
                          draggable_int_input('{$this->params['uid']}_time_minute_picker', {sensitivity: 6,min_val: 0, max_val: 59, callback: function(){ ui_input_datetime_change('{$this->params['uid']}'); }});
                          draggable_int_input('{$this->params['uid']}_time_second_picker', {sensitivity: 6,min_val: 0, max_val: 59, callback: function(){ ui_input_datetime_change('{$this->params['uid']}'); }});
                        });
                      </script>
                    ";
                }

                /* time */
                if ('time' == $this->params['type']) {
                    $tmp_timeval = explode(':', $this->params['value']);
                    $html .= "
                      <input type='text' id='{$this->params['uid']}_time_hour_picker' class='adios ui Input ui_input_type_hour draggable' value='{$tmp_timeval[0]}' placeholder='hh' onchange=\" ui_input_time_change('{$this->params['uid']}'); \" />
                      <input type='text' id='{$this->params['uid']}_time_minute_picker' class='adios ui Input ui_input_type_minute draggable' value='{$tmp_timeval[1]}' placeholder='mm' onchange=\" ui_input_time_change('{$this->params['uid']}'); \"/>
                      <input type='text' id='{$this->params['uid']}_time_second_picker' class='adios ui Input ui_input_type_second draggable' value='{$tmp_timeval[2]}' placeholder='ss' onchange=\" ui_input_time_change('{$this->params['uid']}'); \"/>
                      <script>
                        $(function() {
                          draggable_int_input('{$this->params['uid']}_time_hour_picker', {sensitivity: 6,min_val: 0, max_val: 23, callback: function(){ ui_input_time_change('{$this->params['uid']}'); } });
                          draggable_int_input('{$this->params['uid']}_time_minute_picker', {sensitivity: 6,min_val: 0, max_val: 59, callback: function(){ ui_input_time_change('{$this->params['uid']}'); }});
                          draggable_int_input('{$this->params['uid']}_time_second_picker', {sensitivity: 6,min_val: 0, max_val: 59, callback: function(){ ui_input_time_change('{$this->params['uid']}'); }});
                        });
                      </script>
                    ";
                }

            }

            if ('' != $this->params['unit']) {
                $html .= "<span class='unit'>".hsc($this->params['unit']).'</span>';
            }
        }

        /* image */
        if ('image' == $this->params['type']) {
          $img_src_base = "{$this->adios->config['url']}/Image?cfg=input&f=";

          if ('' != $this->params['value']) {
              $img_src = "{$this->adios->config['url']}/Image?cfg=input&f=".urlencode($this->params['value']);
          } else {
              $img_src = "{$this->adios->config['url']}/adios/assets/images/empty.png";
          }

          $html = "
            <img
              src='{$img_src}'
              id='{$this->params['uid']}_image'
              class='adios ui Input image_input_img'
              onclick='$(\"#{$this->params['uid']}_browser\").show(100);'
            />

            <!-- ".$this->adios->ui->Button([
              "fa_icon" => "fas fa-file-upload",
              "class" => "btn btn-light btn-icon-split ml-1",
              "text" => "Browse or Upload",
              "onclick" => "
                $('#{$this->params['uid']}_browser').show(100);
              ",
            ])->render()." -->

            <div
              id='{$this->params['uid']}_browser'
              style='
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: #FFFFFF80;
                display: none;
              '
            >
              <div
                class='shadow'
                style='
                  position: fixed;
                  right: 0px;
                  top: 0px;
                  width: 85%;
                  height: 100%;
                  z-index: 1;
                  background: white;
                  
                '
              >
                <div>
                  ".$this->adios->ui->Button([
                    "fa_icon" => "fas fa-times",
                    "text" => "Close files and media browser",
                    "class" => "btn btn-secondary btn-icon-split",
                    "onclick" => "
                      $('#{$this->params['uid']}_browser').hide();
                    ",
                  ])->render()."
                </div>
                <div style='margin:1em'>
                  ".(new \ADIOS\Core\UI\Input\FileBrowser($this->adios, $this->params['uid'], [
                    "mode" => "select",
                    "value" => $this->params['value'],
                    "subdir" => $this->params['subdir'],
                    "onchange" => "
                      $('#{$this->params['uid']}_image').attr(
                        'src',
                        '{$img_src_base}/' + file
                      );

                      $('#{$this->params['uid']}_browser').hide();
                    ",
                  ]))->render()."
                </div>
              </div>
            </div>
          ";
        }

        /* file */
        if ('file' == $this->params['type']) {
            $default_src = $this->translate("No file uploaded");
            $file_src_base = "{$this->adios->config['url']}/File?f=";
            $upload_params = "type=file&table_column={$this->params['table_column']}&rename_file={$this->params['rename_file']}&allowed_extensions={$this->params['allowed_extensions']}&subdir={$this->params['subdir']}";
            $file_upload_url = "{$this->adios->config['url']}/UI/FileBrowser/Upload?output=json&".$upload_params;

            if ('' != $this->params['value']) {
              $file_short_name = end(explode('/', $this->params['value']));
              if (strlen($file_short_name) > 75) {
                $file_short_name = substr($file_short_name, 0, 75).'...';
              }
            } else {
              $file_short_name = $default_src;
            }

            $html = "
              <div
                onmouseover='$(\"#{$this->params['uid']}_operations\").css({opacity: 1});'
                onmouseleave='$(\"#{$this->params['uid']}_operations\").css({opacity: 0});'
              >
                <input
                  type='hidden'
                  id='{$this->params['uid']}'
                  name='{$this->params['uid']}'
                  data-is-adios-input='1'
                  ".$this->main_params().'
                  '.$this->generate_input_events().'
                  value="'.htmlspecialchars($this->params['value'])."\"
                  {$this->params['html_attributes']}
                  data-src-real-base=\"".htmlspecialchars($this->adios->config['files_url']).'"
                  data-src-base="'.htmlspecialchars($file_src_base).'"
                  data-default-txt="'.htmlspecialchars($default_src).'"
                  data-upload-params="'.htmlspecialchars($upload_params).'"
                  '.($this->params['readonly'] ? "disabled='disabled'" : '')."
                />
                
                <div style='float:left'>
                  <form id='{$this->params['uid']}_file_form' enctype='multipart/form-data'>
                    <input
                      ".($this->params['readonly'] ? "disabled='disabled'" : '')."
                      type='file' style='display:none;'
                      name='{$this->params['uid']}_file_input'
                      id='{$this->params['uid']}_file_input'
                      onchange='ui_input_upload_file(\"{$this->params['uid']}\");'
                    />
                    <label for='{$this->params['uid']}_file_input'>
                      <span
                        class='adios ui Input file_upload_div'
                        id='{$this->params['uid']}_file'
                        ".($this->params['readonly'] && '' != $this->params['value'] ? "
                          onclick=\"
                            ui_input_file_open('{$this->params['uid']}');
                          \"
                        " : '')."
                        title='".($this->params['readonly'] ? '' : l('Presuňte sem súbor, alebo kliknite pre nahratie súboru'))."'
                      >
                        {$file_short_name}
                      </span>
                      <span class='ml-1'>
                        <div class='btn float-left ml-1 btn-primary btn-sm btn-icon-split'>
                          <span class='icon'><i class='fas fa-window-restore'></i></span>
                          <span class='text'>".$this->translate("Find on this computer")."</span>
                        </div>
                      </span>
                    </label>
                  </form>
                  <div class='adios ui Input file_info_div' id='{$this->params['uid']}_info_div'>
                    ".$this->translate('Uploading file. Please wait.')."
                  </div>
                </div>

                ".(!$this->params['readonly'] ? "
                    <div class='adios ui file_operations_div' id='{$this->params['uid']}_operations' style='opacity:0;float:left;'>
                      ".(FALSE && $this->params['show_file_browser'] ?
                        $this->adios->ui->button([
                          'uid' => $this->params['uid'].'_file_input_browser_button',
                          'onclick' => "ui_input_ftp_browser('{$this->params['uid']}', 'file');",
                          'fa_icon' => 'fas fa-search',
                          'text' => l('Použiť už nahratý (Nájsť na serveri)'),
                          'class' => "float-left mr-1 btn-secondary btn-sm btn-icon-split",
                        ])->render()
                      : '').
                      (FALSE && $this->params['show_download_url_button'] ?
                        $this->adios->ui->button([
                          'uid' => $this->params['uid'].'_file_input_download_button',
                          'onclick' => "ui_input_file_download('{$this->params['uid']}', '".l('Zadajte URL adresu')."');",
                          'fa_icon' => 'fas fa-download',
                          'title' => l('Stiahnuť'),
                          'class' => "float-left mr-1 btn-secondary btn-sm btn-icon-split",
                        ])->render()
                      : '').
                      ($this->params['show_open_button'] ?
                        $this->adios->ui->button([
                          'uid' => $this->params['uid'].'_file_input_open_button',
                          'onclick' => "ui_input_file_open('{$this->params['uid']}');",
                          'fa_icon' => 'fas fa-eye',
                          'title' => l('Náhľad'),
                          'class' => "float-left mr-1 btn-secondary btn-sm btn-icon-split",
                          'style' => (empty($this->params['value']) ? 'display:none;' : '')
                        ])->render()
                      : '').
                      ($this->params['show_delete_button'] ?
                        $this->adios->ui->button([
                          'uid' => $this->params['uid'].'_file_input_delete_button',
                          'onclick' => "ui_input_file_remove('{$this->params['uid']}');",
                          'fa_icon' => 'fas fa-trash-alt',
                          'title' => l('Vyčistiť'),
                          'class' => "float-left mr-1 btn-danger btn-sm btn-icon-split",
                          'style' => (empty($this->params['value']) ? 'display:none;' : '')
                        ])->render()
                      : '')."
                    </div>
                " : "")."
              </div>

              <div style='clear:both'></div>

              ".(!$this->params['readonly'] ? "
                <script>
                  $(document).ready(function(){
                    ui_input_activate_drop('{$this->params['uid']}');
                  });
                </script>
              " : "")."
            ";
        }

        /* lookup */
        if ('lookup' == $this->params['type']) {
          $lookupModel = $this->adios->getModel($this->params['model']);
          $value = (int) $this->params['value'];
          $inputStyle = $this->params['input_style'] ?? "";

          $lookupSqlQuery = $lookupModel->lookupSqlQuery(
            $this->params['initiating_model'],
            $this->params['initiating_column'],
            $this->params['form_data'],
            $this->params
          );

          if (!in_array($inputStyle, ['autocomplete', 'select'])) {
            $rowsCnt = reset($this->adios->db->get_all_rows_query("
              select
                ifnull(count(*), 0) as cnt
              from ({$lookupSqlQuery}) dummy
            "))['cnt'];

            if ($rowsCnt > 10) {
              $inputStyle = 'autocomplete';
            } else {
              $inputStyle = 'select';
            }
          }

          switch ($inputStyle) {
            case "select":
              $rows = $this->adios->db->get_all_rows_query($lookupSqlQuery);

              $html = "
                <select
                  id='{$this->params['uid']}'
                  name='{$this->params['uid']}'
                  data-is-adios-input='1'
                  ".$this->main_params()."
                  ".$this->generate_input_events()."
                  title='".hsc($this->params['title'])."'
                  {$this->params['html_attributes']}
                  ".($this->params['readonly'] ? "disabled='disabled'" : '')."
                >
                  ".($this->params['show_lookup_not_selected_option']
                    ? "<option value='0'>".($this->params['not_selected_text'] ?? "[Not selected]")."</option>"
                    : ""
                  )."
              ";

              if (is_array($rows)) {
                foreach ($rows as $row) {
                  if ($this->params['translate_value']) {
                    $row['input_lookup_value'] = l($row['input_lookup_value']);
                  }

                  $html .= "
                    <option
                      value='{$row['id']}'
                      ".((int) $row['id'] === $value ? "selected" : "")."
                    >
                      ".hsc($row['input_lookup_value'])."
                    </option>
                  ";
                }
              }

              $html .= "</select>";
            break;
            case "autocomplete":
            default:

              $inputText = "";

              if ($value > 0) {
                $row = reset($this->adios->db->get_all_rows_query(
                  $lookupModel->lookupSqlQuery(
                    $this->params['initiating_model'],
                    $this->params['initiating_column'],
                    $this->params['form_data'],
                    $this->params,
                    "`id` = {$value}"
                  )
                ));

                if ((int) $row['id'] === $value) {
                  $inputText = $row['input_lookup_value'];
                }
              } else {
                $inputText = '';
              }

              if ('' == $this->params['placeholder']) {
                $this->params['placeholder'] = $this->translate('Search')."...";
              }

              $onchange_hidden = $this->params['onchange'];
              $this->params['onchange'] = $this->params['onchange_text'];

              $this->params['onkeydown'] = " ui_input_lookup_onkeydown(event, '{$this->params['uid']}'); ".$this->params['onkeydown'];
              $this->params['onchange'] = " ui_input_lookup_set_value('{$this->params['uid']}', $('#{$this->params['uid']}').val(), '', function(){ ".$this->params['onchange'].' }); ';

              if (!$this->adios->db_perms($this->params['table'].'/select')) {
                if ('' == $this->params['lookup_detail_onclick']) {
                  $this->params['lookup_detail_enabled'] = false;
                }
                if ('' == $this->params['lookup_search_onclick']) {
                  $this->params['lookup_search_enabled'] = false;
                }
              }

              $detail_onclick = ('' != $this->params['lookup_detail_onclick'] ? $this->params['lookup_detail_onclick'] : 'ui_input_lookup_detail');
              $search_onclick = ('' != $this->params['lookup_search_onclick'] ? $this->params['lookup_search_onclick'] : 'ui_input_lookup_search');
              $add_onclick = ('' != $this->params['lookup_add_onclick'] ? $this->params['lookup_add_onclick'] : 'ui_input_lookup_add');

              $html .= "
                <span style='white-space:nowrap;'>
                  <input type='hidden'
                    id='{$this->params['uid']}'
                    data-is-adios-input='1'
                    {$adios_disabled_attribute}
                    name='{$this->params['uid']}'
                    data-form-uid='{$this->params['form_uid']}'
                    data-initiating-model='{$this->params['initiating_model']}'
                    data-initiating-column='{$this->params['initiating_column']}'
                    value='{$value}'
                    data-model='".hsc($this->params['model'])."'
                    xxx-data-table='".hsc($this->params['table'])."'
                    xxx-data-key='".hsc($this->params['key'])."'
                    xxx-data-order='".hsc($this->params['order'])."'
                    xxx-data-where='".hsc($this->params['where'])."'
                    xxx-data-lookup-search-type='".hsc($this->params['lookup_search_type'])."'
                    xxx-data-follow-lookups='".hsc($this->params['follow_lookups'])."'
                    onchange=\"{$onchange_hidden}\"
                  />
                  <input
                    type='{$input_type}'
                    name='{$this->params['uid']}_autocomplete_input'
                    id='{$this->params['uid']}_autocomplete_input'
                    class='col-md-9 px-1 ".join(' ', $this->classes)."'
                    style='{$this->params['style']}'
                    ".$this->generate_input_events().'
                    value="'.htmlspecialchars($inputText).'"
                    data-value="'.htmlspecialchars($inputText).'"
                    title="'.htmlspecialchars($this->params['title']).'"
                    placeholder="'.htmlspecialchars($this->params['placeholder'])."\"
                    {$this->params['html_attributes']}
                    ".($this->params['readonly'] ? "disabled='disabled'" : '')."
                    autocomplete='off'
                    onfocus='ui_input_lookup_onkeydown(event, \"{$this->params['uid']}\");'
                  />
                  <div
                    class='adios ui Input lookup autocomplete shadow-sm col-md-9'
                    style='display:none;'
                    id='{$this->params['uid']}_result_div'
                  >
                    <div id='{$this->params['uid']}_result_div_inner' class='innner' style='text-align:left;' >
                    </div>
                  </div>
                  <div class='adios ui Input lookup_controls'>
                    ".($this->params['lookup_search_enabled'] && !$this->params['readonly'] ? "
                      <span class='btn btn-light btn-sm' onclick=\"{$search_onclick}('{$this->params['uid']}')\">
                        <i
                          class='icon fas fa-search'
                          title='".$this->translate('Search in list')."'
                        ></i>
                      </span>
                    " : "")."
                    ".($this->params['lookup_detail_enabled'] ? "
                      <span class='btn btn-light btn-sm' onclick=\"{$detail_onclick}($('#{$this->params['uid']}').val(), '{$this->params['uid']}');\">
                        <i
                          id='{$this->params['uid']}_detail_button'
                          style='".($this->params['value'] > 0 && is_array($row) ? '' : 'display:none;')."'
                          class='icon fas fa-id-card'
                          title='".l('Zobraziť detail záznamu')."' 
                        ></i>
                      </span>
                    " : "")."
                    ".($this->params['lookup_add_enabled'] && !$this->params['readonly'] ? "<img id='{$this->params['uid']}_add_button' style='".($this->params['value'] > 0 ? 'display:none;' : '')."' src='{$this->adios->config['adios_images_url']}/black/app/plus.png' onclick=\" {$add_onclick}('{$this->params['uid']}'); \" title='".l('Pridať')."' />" : '').'
                    '.(!$this->params['readonly'] ? "
                      <span class='btn btn-light btn-sm' onclick=\"ui_input_lookup_set_value('{$this->params['uid']}', 0);\">
                        <i
                          id='{$this->params['uid']}_clear_button'
                          style='".($this->params['value'] > 0 && is_array($row) ? '' : 'display:none;').";'
                          class='icon fas fa-times'
                          title='".l('Zrušiť výber')."' 
                        ></i>
                      </span>
                    " : '')."
                  </div>
                </span>
              ";
            break;
          }
        }

        return $html;
    }

    /*                       */
    /* generate_input_events */
    /*                       */
    public function generate_input_events() {
      $events = "";
      foreach ($this->supported_events as $event) {
        if ('' != $this->params[$event]) {
          $events .= " $event=\"{$this->params[$event]}\" ";
        }
      }

        return $events;
    }

    /*            */
    /* order_tree */
    /*            */
    // public function order_tree($data, $id)
    // {
    //     ++$this->order_tree_level;
    //     if (_count($data)) {
    //         foreach ($data as $key => $val) {
    //             if ($val[$this->params['parent_column']] + 0 == $id) {
    //                 $val['order_tree_level'] = $this->order_tree_level;
    //                 $res[$val['id']] = $val;
    //                 $sub = $this->order_tree($data, $val['id']);
    //                 if (_count($sub)) {
    //                     foreach ($sub as $k => $v) {
    //                         $res[$v['id']] = $v;
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     --$this->order_tree_level;

    //     return $res;
    // }
}
