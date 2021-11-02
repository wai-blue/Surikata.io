<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class Tags extends \ADIOS\Core\Input {
  public function render() {
    $model = $this->adios->getModel($this->params['model']);
    $options = $model->getAll();

    $allTags = [];
    foreach($options as $option) {
      $allTags[] = strtolower($option["tag"]);
    }
    $allTagsAutocomplete = "'" . implode("','", $allTags). "'";

    $html = "<textarea id='{$this->uid}_tag'></textarea>";
    $html .= "<input type='hidden' name='{$this->uid}' id='{$this->uid}'>";

    $html .= "
      <script>
        $('#{$this->uid}_tag').tagEditor({
          initialTags: {$this->params["initialTags"]},
          autocomplete: {
              delay: 0,
              position: { collision: 'flip' },
              source: [{$allTagsAutocomplete}],
          },
          forceLowercase: true,
          placeholder: 'Enter tags ...',
          onChange: function(field, editor, tags) {
            let tagsJson = JSON.stringify(tags);
            $('#{$this->uid}').val(
              tagsJson
            );
          },
        });
      </script>
      <style>
        .ui-menu {
          z-index: 9999999;
        }
      </style>
    ";

    return $html;
  }
}
