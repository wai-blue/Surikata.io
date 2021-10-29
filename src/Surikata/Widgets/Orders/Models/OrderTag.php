<?php

namespace ADIOS\Widgets\Orders\Models;

use ADIOS\Core\Models\User;

class OrderTag extends \ADIOS\Core\Model {
  var $sqlName = "orders_tags";

  public function columns(array $columns = []) {
    return parent::columns([
      "tag" => [
       "type" => "varchar",
       "title" => $this->translate("Notes"),
       "show_column" => TRUE,
      ],
    ]);
  }

}