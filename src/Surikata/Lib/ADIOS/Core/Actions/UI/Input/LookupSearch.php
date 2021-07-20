<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Input;
class LookupSearch extends \ADIOS\Core\Action {
  public function render() {

    // $this->adios->getUid("{$this->params['uid']}_lookup_select_window_action");

    $tableUid = $this->params['uid'] ?? $this->adios->getUid("{$this->params['model']}_LookupSearch");
    $windowUid = "{$tableUid}_lookup_select_window";

    $lookupModel = $this->adios->getModel($this->params['model']);

    $content = $this->adios->ui->Table([
      "uid" => $tableUid,
      "model" => $this->params['model'],
      "where" => $lookupModel->lookupSqlWhere(
        $this->params['initiating_model'],
        $this->params['initiating_column'],
        @json_decode($this->params['form_data'], TRUE) ?? [], // form_data
        [],
      ),
      "list_type" => "lookup_select",
      "onclick" => "
        ui_input_lookup_set_value('{$this->params['inputUid']}', id, '');
        window_close('{$windowUid}');
      ",
    ]);

    $window_params = [
      'uid' => $windowUid,
      'content' => $content->render(),
      'header' => [
        $this->adios->ui->Button([
          'type' => 'close',
          'onclick' => "window_close('{$windowUid}');"
        ]),
      ],
      'title' => l('Vyhľadať v zozname'),
    ];

    return $this->adios->ui->Window($window_params)->render();
  }
}