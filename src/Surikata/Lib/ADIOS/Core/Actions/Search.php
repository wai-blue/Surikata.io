<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions;

/**
 * @package UI\Actions
 */
class Search extends \ADIOS\Core\Action {
  public function preRender() {
    $items = [];

    if (strlen($this->params['q']) >= 3) {

      foreach ($this->adios->db->tables as $table_name => $table_columns) {
        if ($table_columns['%%table_params%%']['model'] instanceof \ADIOS\Core\Model) {
          $tmp_items = $table_columns['%%table_params%%']['model']->search($this->params['q']);
          $items = array_merge(
            $items,
            is_array($tmp_items) ? $tmp_items : []
          );
        }
      }

    } else {
      $items = [
        ['name' => 'Zadajte aspoň 3 znaky pre vyhľadávanie.'],
      ];
    }

    return [
      "items" => $items,
    ];
  }

  public function render() {
    $content = parent::render();

    $window = $this->adios->ui->Window([
      'title' => "Hľadanie: {$this->params['q']}",
      'content' => $content,
    ]);

    $window->params['header'] = [
      $this->adios->ui->button([
        'type' => 'close',
        'onclick' => "window_close('{$window->params['uid']}');",
      ]),
    ];
    
    return $window->render();
  }
}
