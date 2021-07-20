<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeColor extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : " default '' ";

        return "`$col_name` char(10) {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        return "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
    }

    public function filter($col_name, $value, $params = [])
    {
        if ('' == trim($value)) {
            return 'TRUE';
        }

        return "($col_name='$value')";
    }

    public function get_control_params($table_name, $col_name, $value, $col_definition, $params = [])
    {
        return [];
    }

    public function get_control($params = [])
    {
        extract($params, EXTR_OVERWRITE);

        if ('yes' == $only_display) {
            $html = "<input type=hidden name='$name' id='$name' value='".addslashes($value)."'><div style='width:25px;background:$value'>&nbsp;&nbsp;&nbsp;</div>";

            return $html;
        } else {
            return "
      <input type='text' id='{$name}' value='".ads($value)."' style='width:80px' onchange='{$name}_onchange();'>
      <span id='{$name}_selected_div' style='width:12px;height:12px;display:inline-block'>&nbsp;</span>
      <br/>
      <div class='{$name}_div' farba='#CC0000'>&nbsp;</div>
      <div class='{$name}_div' farba='#FB940B'>&nbsp;</div>
      <div class='{$name}_div' farba='#FFFF00'>&nbsp;</div>
      <div class='{$name}_div' farba='#00CC00'>&nbsp;</div>
      <div class='{$name}_div' farba='#03C0C6'>&nbsp;</div>
      <div class='{$name}_div' farba='#0000FF'>&nbsp;</div>
      <div class='{$name}_div' farba='#762CA7'>&nbsp;</div>
      <div class='{$name}_div' farba='#FF98BF'>&nbsp;</div>
      <div class='{$name}_div' farba=''>&nbsp;</div>
      <div class='{$name}_div' farba='#999999'>&nbsp;</div>
      <div class='{$name}_div' farba='#000000'>&nbsp;</div>
      <div class='{$name}_div' farba='#885418'>&nbsp;</div>
      <script>
        function {$name}_onchange() {
          $('#{$name}_selected_div').css('background', $('#{$name}').val());
        }
        {$name}_onchange();

        $('.{$name}_div').each(function() {
          $(this).css({'background': $(this).attr('farba'), 'cursor': 'pointer', 'width': '12px', 'height': '12px', 'border': '2px solid white', 'display': 'inline-block'});
          $(this).click(function() {
            $('.{$name}_div').css({'border': '2px solid white', 'margin': '0px'});
            $(this).css({'border': '2px solid #494949', 'margin': '0px'});
            $('#{$name}').val($(this).attr('farba'));
            {$name}_onchange();
          });
        });

        $('.{$name}_div[farba='+$('#{$name}').val()+']').trigger('click');
      </script>
    ";
        }
    }

    public function get_html($value, $params = [])
    {
        $value = htmlspecialchars($value);

        $html = '';

        if ('' != $value) {
            $html = "<span style='width:15px;background:{$value};border:1px solid black'>&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        }

        return $html;
    }

    public function get_csv($value, $params = [])
    {
        return $value;
    }
}
