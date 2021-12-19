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
    $columns = $modelObject->columns();

    $this->adios->checkUid($parentUid);

    $modelColumnsSelectHtml = "
      <select
        id='{$parentUid}_column_{$colIndex}'
        onchange='
          $(this).css(\"opacity\", (this.value == \"\" ? 0.35 : 1));
          $(\".{$parentUid}_column_{$colIndex}_info\").hide();
          $(\".{$parentUid}_column_{$colIndex}_info[data-col-name=\" + this.value + \"]\").css(\"display\", \"inline-block\");
        '
      >
        <option value=''>-- ".$this->translate("Do not import this column")." --</option>
    ";
    foreach ($columns as $tmpColName => $tmpColDefinition) {
      if (in_array($columns[$tmpColName]['type'], ["image", "file"])) continue;
      if ($columns[$tmpColName]['virtual']) continue;

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
    ";

    foreach ($columns as $tmpColName => $tmpColDefinition) {
      $acceptedValuesHtml = "";
      switch ($columns[$tmpColName]['type']) {
        case "lookup":
          $lookupModelObject = $this->adios->getModel($columns[$tmpColName]["model"]);
          $tmp = $this->adios->db->get_all_rows_query($lookupModelObject->lookupSqlQuery());

          $acceptedValuesHtml = "<select style='font-size:inherit;color:inherit'>";
          foreach ($tmp as $value) {
            $acceptedValuesHtml .= "<option>".hsc($value['input_lookup_value'])."</option>";
          }
          $acceptedValuesHtml .= "</select>";
        break;
        case "boolean":
          $acceptedValuesHtml = "0, 1";
        break;
        case "float":
          $acceptedValuesHtml = $this->translate("Decimal numbers with dot (.) as a decimal separator and no thousands separator. Example: 123456.78");
        break;
        case "int":
          if (is_array($columns[$tmpColName]["enum_values"])) {
            $acceptedValuesHtml = "<select style='font-size:inherit;color:inherit'>";
            foreach ($columns[$tmpColName]["enum_values"] as $value) {
              $acceptedValuesHtml .= "<option>".hsc($value)."</option>";
            }
            $acceptedValuesHtml .= "</select>";
          } else {
            $acceptedValuesHtml = $this->translate("Non-decimal number, no thousands separator. Example: 123456");
          }
        break;
        case "varchar":
          $acceptedValuesHtml = $this->translate("Single-line text");
        break;
        case "text":
          $acceptedValuesHtml = $this->translate("Multi-line text");
        break;
        case "date":
          $acceptedValuesHtml = $this->translate("Date in YYYY-MM-DD format. Example: 2020-12-31");
        break;
        case "time":
          $acceptedValuesHtml = $this->translate("Time in HH:MM:SS format. Example: 14:05:07");
        break;
        case "date":
          $acceptedValuesHtml = $this->translate("Datetime in YYYY-MM-DD HH:MM:SS format. Example: 2020-12-31 14:05:07");
        break;
      }

      if (!empty($acceptedValuesHtml)) {
        $modelColumnsSelectHtml .= "
          <div
            class='{$parentUid}_column_{$colIndex}_info'
            data-col-name='{$tmpColName}'
            style='background:#ffecc8;padding:5px;font-size:0.8em;float:right;display:none'
          >
            ".$this->translate("Accepted values").":
            {$acceptedValuesHtml}
          </div>
        ";
      }
    }

    $modelColumnsSelectHtml .= "
      <script> $('#{$parentUid}_column_{$colIndex}').trigger('change'); </script>
    ";

    return $modelColumnsSelectHtml;

  }

  public function render() {
    $model = $this->params['model'];
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
          ".($columnNamesInFirstLine ? "<td style='width:10%'>".hsc($colName)."</td>" : "")."
          <td style='width:45%'>".hsc($secondLine[$colIndex])."</td>
          <td style='width:45%'>".$this->getModelColumnSelectHtml($colIndex, $colName)."</td>
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
