<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class CheckboxField extends \ADIOS\Core\Input {
  public function render() {
    $model = $this->adios->getModel($this->params['model']);

    switch ($this->params['columns'] ?? 3) {
      case 1: default: $bootstrapColumnSize = 12; break;
      case 2: $bootstrapColumnSize = 6; break;
      case 3: $bootstrapColumnSize = 4; break;
      case 4: $bootstrapColumnSize = 3; break;
      case 6: $bootstrapColumnSize = 2; break;
    }
    
    $columns = $model->columns();

    if (empty($this->params['model']) || !is_array($columns)) {
      throw new \ADIOS\Core\Exception("CheckboxField Input: Error #1");
    }

    $valuesRaw = $this->adios->db->get_all_rows_query("
      select
        *
      from `".$model->getFullTableSQLName()."`
      where
        `{$this->params['key_column']}` = '".$this->adios->db->escape($this->params['key_value'])."'
    ");
    $values = [];
    foreach ($valuesRaw as $valueRaw) {
      $values[] = $valueRaw[$this->params['value_column']];
    }
    $values = array_unique($values);
    
    $html = "
      <div class='adios ui Input checkbox-field'>
        <input type='hidden' id='{$this->uid}' data-is-adios-input='1'>
        <div class='row'>
    ";
    $i = 0;
    foreach ($this->params['values'] as $key => $displayValue) {
      $html .= "
        <div class='col-lg-{$bootstrapColumnSize} col-md-12'>
          <input
            type='checkbox'
            data-key='".ads($key)."'
            adios-do-not-serialize='1'
            id='{$this->uid}_checkbox_{$i}'
            onchange='{$this->uid}_serialize();'
            ".(in_array($key, $values) ? "checked" : "")."
          >
          <label for='{$this->uid}_checkbox_{$i}'>
            ".hsc($displayValue)."
          </label>
        </div>
      ";
      $i++;
    }
    $html .= "
        </div>
      </div>
      <script>
        function {$this->uid}_serialize() {
          let data = [];
          $('#{$this->uid}').closest('.checkbox-field').find('input[type=checkbox]:checked').each(function() {
            data.push($(this).data('key'));
          });
          $('#{$this->uid}').val(JSON.stringify(data));
        }

        {$this->uid}_serialize();
      </script>
    ";


    return $html;
  }
}
