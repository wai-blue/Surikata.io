<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class ManyToMany extends \ADIOS\Core\Input {
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
      throw new \ADIOS\Core\Exception("ManyToMany Input: Error #1");
    }

    $srcColumn = $this->params['relation'][0];
    $dstColumn = $this->params['relation'][1];

    $srcModel = $this->adios->getModel($columns[$srcColumn]['model']);
    $dstModel = $this->adios->getModel($columns[$dstColumn]['model']);

    $dstItems = $this->adios->db->get_all_rows_query("
      select
        `dst`.`id`,
        ".$dstModel->lookupSqlValue('dst')." as `lookup_sql_value`
      from `".$dstModel->getFullTableSQLName()."` dst
      order by ".($this->params['order'] ?? "id asc")."
    ");

    $valuesRaw = $this->adios->db->get_all_rows_query("
      select
        *
      from `".$model->getFullTableSQLName()."`
      where
        ".(empty($this->params['constraints'][$srcColumn]) ? "TRUE" : "`{$srcColumn}` = {$this->params['constraints'][$srcColumn]}")."
        and ".(empty($this->params['constraints'][$dstColumn]) ? "TRUE" : "`{$dstColumn}` = {$this->params['constraints'][$dstColumn]}")."
    ");
    $values = [];
    foreach ($valuesRaw as $valueRaw) {
      $values[] = $valueRaw[$dstColumn];
    }
    $values = array_unique($values);
    
    $html = "
      <div class='adios ui Input checkbox-field'>
        <input type='hidden' id='{$this->uid}' data-is-adios-input='1'>
        <div class='row'>
    ";
    foreach ($dstItems as $item) {
      $html .= "
        <div class='col-lg-{$bootstrapColumnSize} col-md-12'>
          <input
            type='checkbox'
            data-id='{$item['id']}'
            adios-do-not-serialize='1'
            id='{$this->uid}_checkbox_{$item['id']}'
            onchange='{$this->uid}_serialize();'
            ".(in_array($item['id'], $values) ? "checked" : "")."
          >
          <label for='{$this->uid}_checkbox_{$item['id']}'>
            ".hsc($item['lookup_sql_value'])."
          </label>
        </div>
      ";
    }
    $html .= "
        </div>
      </div>
      <script>
        function {$this->uid}_serialize() {
          let data = [];
          $('#{$this->uid}').closest('.checkbox-field').find('input[type=checkbox]:checked').each(function() {
            data.push($(this).data('id'));
          });
          $('#{$this->uid}').val(JSON.stringify(data));
        }

        {$this->uid}_serialize();
      </script>
    ";


    return $html;
  }
}
