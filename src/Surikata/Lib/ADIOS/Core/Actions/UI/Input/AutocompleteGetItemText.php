<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Input;

/**
 * @package UI\Actions\Input
 */
class AutocompleteGetItemText extends \ADIOS\Core\Action {
  public function render() {
    $value = (int) $this->params['value'];
    $lookupModel = $this->adios->getModel($this->params['model']);

    $query = $lookupModel->lookupSqlQuery(
      $this->params['initiating_model'],
      $this->params['initiating_column'],
      [], // form_data
      [], // params
      "id = {$value}" // having
    );

    $lookupRow = reset($this->adios->db->get_all_rows_query($query));

    if (!is_array($lookupRow) && $value > 0) {
      return "-- Record not found --";
    } else {
      return $lookupRow['input_lookup_value'];
    }
  }
}