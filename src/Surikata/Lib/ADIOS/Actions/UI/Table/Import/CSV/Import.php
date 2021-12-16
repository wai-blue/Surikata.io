<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table\Import\CSV;

/**
 * @package UI\Actions
 */
class Import extends \ADIOS\Core\Action {
  public function render() {
    $csvFile = $this->params['csv_file'];
    $columnNamesInFirstLine = (bool) $this->params['column_names_in_first_line'];
    $separator = $this->params['separator'];
    $model = $this->params['model'];
    $modelObject = $this->adios->getModel($model);

    if ($separator == "TAB") $separator = "\t";

    $log = "";
    $row = 0;
    $importedRows = 0;

    if (($handle = fopen("{$this->adios->config['files_dir']}/csv-import/{$csvFile}", "r")) !== FALSE) {
      while (($csvRow = fgetcsv($handle, 1000, $separator)) !== FALSE) {
        $row++;

        if ($columnNamesInFirstLine && $row == 1) continue;

        $data = [];
        foreach ($csvRow as $colIndex => $colValue) {
          $tmpColName = $this->params["column_{$colIndex}"] ?? "";
          if (!empty($tmpColName)) {
            $data[$tmpColName] = $colValue;
          }
        }

        // foreach ($data as $colName => $colValue) {
        //   $log .= "{$colName} = {$colValue}, ";
        // }
        // $log .= "\n";

        $modelObject->insertOrUpdateRow($data);
        $importedRows ++;

      }

      fclose($handle);
    }

    $content = "
      <script>
        function {$this->uid}_close() {
          window_close('{$this->uid}_window');
        }
      </script>
      
      <div style='text-align:center;margin-top:20px;font-size:20pt;color:green;'>
        ".$this->translate("Import was successful.")."<br/>
      </div>

      <pre>{$log}</pre>
    ";

    $window = $this->adios->ui->Window([
      'uid' => "{$this->uid}_window",
      'title' => " ",
      'content' => $content,
    ]);

    $window->params['header'] = [
      $this->adios->ui->button([
        'type' => 'close',
        'onclick' => "{$this->uid}_close();",
      ]),
    ];
    
    return $window->render();

  }
}
