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
      $allTags[] = strtolower($option["tag_lang_1"]);
    }
    $allTagsAutocomplete = "'" . implode("','", $allTags). "'";

    $html = "<textarea id='{$this->uid}'></textarea>";

    $html .= "
      <script>
        $('#{$this->uid}').tagEditor({
          autocomplete: {
              delay: 0,
              position: { collision: 'flip' },
              source: [{$allTagsAutocomplete}],
          },
          forceLowercase: true,
          placeholder: 'Enter tags ...',
        });
      </script>";
    return $html;
  }
}
