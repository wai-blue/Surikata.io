<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table\Import;

/**
 * @package UI\Actions
 */
class CSV extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function render() {
    $model = $this->adios->getModel($this->params['model']);
    // $columns = $model->columns();
    // $tableParams = json_decode(base64_decode($this->params['tableParams']), TRUE);

    // $uiTable = new \ADIOS\Core\UI\Table($this->adios, $tableParams);
    // $data = $uiTable->data;
    // $firstRow = reset($data);

    $fileUploadInput = new \ADIOS\Core\UI\Input(
      $this->adios,
      [
        "uid" => "{$this->uid}_csv_file",
        "type" => "file",
        "subdir" => "csv-import",
        "onchange" => "{$this->uid}_previewCsv()",
      ]
    );

    $content = "
      <script>
        function {$this->uid}_close() {
          window_close('{$this->uid}_window');
        }

        function {$this->uid}_import() {
          console.log($('#{$this->uid}_csv_file').val());
        }

        function {$this->uid}_previewCsv() {
          let csvFile = $('#{$this->uid}_csv_file').val();

          _ajax_update(
            'UI/Table/Import/CSV/Preview',
            {
              'model': '{$this->params['model']}',
              'csvFile': csvFile,
            },
            '{$this->uid}_preview_div'
          );
        }
      </script>

      ".$fileUploadInput->render()."

      <div id='{$this->uid}_preview_div'></div>
    ";

    $window = $this->adios->ui->Window([
      'uid' => "{$this->uid}_window",
      'title' => $this->translate("Import from CSV"),
      'content' => $content,
    ]);

    $window->params['header'] = [
      $this->adios->ui->button([
        'type' => 'close',
        'onclick' => "{$this->uid}_close();",
      ]),
      $this->adios->ui->button([
        'type' => 'save',
        'text' => $this->translate('Import !'),
        'onclick' => "{$this->uid}_import();",
      ]),
    ];
    
    return $window->render();

  }
}
