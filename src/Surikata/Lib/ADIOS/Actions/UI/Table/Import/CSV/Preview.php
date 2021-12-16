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
class Preview extends \ADIOS\Core\Action {
  public function getModelColumnSelectHtml($colIndex, $colName) {
    $parentUid = $this->params['parentUid'];
    $model = $this->params['model'];
    $modelObject = $this->adios->getModel($model);

    $this->adios->checkUid($parentUid);

    $modelColumnsSelectHtml = "
      <select
        id='{$parentUid}_column_{$colIndex}'
        onchange='
          $(this).css(\"opacity\", (this.value == \"\" ? 0.35 : 1));
        '
      >
        <option value=''>-- ".$this->translate("Do not import this column")." --</option>
    ";
    foreach ($modelObject->columns() as $tmpColName => $tmpColDefinition) {
      $modelColumnsSelectHtml .= "
        <option
          value='".ads($tmpColName)."'
          ".(
            trim(strtolower($colName)) == trim(strtolower($tmpColName))
            || trim(strtolower($colName)) == trim(strtolower($tmpColDefinition['title']))
            ? "selected"
            : ""
          )."
        >
          ".hsc($tmpColDefinition['title'])."
        </option>
      ";
    }
    $modelColumnsSelectHtml .= "
      </select>
      <script> $('#{$parentUid}_column_{$colIndex}').trigger('change'); </script>
    ";

    return $modelColumnsSelectHtml;

  }

  public function render() {
    $csvFile = $this->params['csvFile'];
    $columnNamesInFirstLine = (bool) $this->params['columnNamesInFirstLine'];
    $separator = $this->params['separator'];

    if ($separator == "TAB") $separator = "\t";

    $csvRows = [];
    $row = 1;

    if (($handle = fopen("{$this->adios->config['files_dir']}/csv-import/{$csvFile}", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
        $csvRows[] = $data;

        if ($row == 10) break;
      }
      fclose($handle);
    }

    $conversionTableHtml = "
      <div class='card shadow mb-4'>
        <div class='card-header py-3'>
          <h6 class='m-0 font-weight-bold text-primary'>Assign columns</h6>
        </div>
        <div class='card-body' style='height:calc(100vh - 550px);overflow:auto'>
          <div class='table-responsive'>
            <table class='table table-bordered' id='dataTable' width='100%' cellspacing='0'>
              <thead>
                <tr>
                  ".($columnNamesInFirstLine ? "<th>Column in CSV file</th>" : "")."
                  <th>Preview</th>
                  <th>Column in Surikata</th>
                </tr>
              </thead>
              <tbody>
    ";
    $firstLine = $csvRows[0] ?? [];
    $secondLine = $csvRows[$columnNamesInFirstLine ? 1 : 0] ?? [];

    foreach ($firstLine as $colIndex => $colName) {
      $conversionTableHtml .= "
        <tr>
          ".($columnNamesInFirstLine ? "<td>".hsc($colName)."</td>" : "")."
          <td>".hsc($secondLine[$colIndex])."</td>
          <td>".$this->getModelColumnSelectHtml($colIndex, $colName)."</td>
        </tr>
      ";
    }
    $conversionTableHtml .= "
              </tbody>
            </table>
          </div>
        </div>
      </div>
    ";


    // $csv = file_get_contents("{$this->adios->config['files_dir']}/csv-import/{$csvFile}");
    return $conversionTableHtml;

  }
}
