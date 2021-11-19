<?php

namespace ADIOS\Actions\Website;

class WebMenuSave extends \ADIOS\Core\Widget\Action {
  public function render() {
    $values = @json_decode($this->params['values'], TRUE);
    $items = @json_decode($values['items'], TRUE);

    $this->adios->getModel('Widgets/Website/Models/WebMenu')
      ->where('id', $this->params['id'])
      ->update(["name" => $values['name']])
    ;

    echo (new \ADIOS\Actions\UI\Tree\Save($this->adios, [
      "model" => "Widgets/Website/Models/WebMenuItem",
      "values" => $values['items'],
    ]))->render();

    echo "1";
  }
}