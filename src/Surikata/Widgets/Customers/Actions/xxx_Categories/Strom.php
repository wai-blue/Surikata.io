<?php

namespace ADIOS\Actions\Customers\Categories;

class Strom extends \ADIOS\Core\Action {
  public function init() {
    $this->languageDictionary["en"] = [
      "Kategórie klientov" => "Customer categories",
    ];
  }

  public function render() {
    return $this->adios->ui->Tree([
      "uid" => "{$this->uid}_tree",
      "model" => "Widgets/Customers/Models/CustomerCategory",
      "title" => $this->translate("Customer categories"),
    ])->render();
  }
}