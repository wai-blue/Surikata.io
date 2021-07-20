<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI;
class Table extends \ADIOS\Core\Action {
  function render() {

    // nebezpecne parametre prenasane cez request su zakazane kvoli sql injection
    $secure_inputs = [
      'where',
      'having',
      'order',
      'custom_select',
      'custom_group',
      'summary_settings',
      'custom_filters',
      'sortable_column',
      'fulltext_search_columns',
      'group'
    ];
    foreach ($secure_inputs as $val) {
      if ($this->params[$val] == $_REQUEST[$val] && '' != $this->params[$val]) {
        $this->adios->console->log('UI TABLE', "Unsecure REQUEST input {$val}: {$this->params[$val]}");
        unset($this->params[$val]);
      }
    }

    $table = $this->adios->ui->Table($this->params);
    return $table->render();

  }
}