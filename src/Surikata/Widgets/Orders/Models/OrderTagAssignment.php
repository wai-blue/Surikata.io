<?php

namespace ADIOS\Widgets\Orders\Models;


class OrderTagAssignment extends \ADIOS\Core\Model {
  var $sqlName = "orders_tags_assignment";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_order" => [
        "type" => "lookup",
        "model" => "Widgets/Orders/Models/Order",
        "title" => "Order",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_tag" => [
        "type" => "lookup",
        "model" => "Widgets/Orders/Models/OrderTag",
        "title" => "Tag",
        "required" => TRUE,
        "show_column" => TRUE,
      ]
    ]);
  }

  public function getTagsForOrder($idOrder) {
    $query = $this->getQuery()->where('id_order', '=', $idOrder);
    $tagList = $this->fetchRows($query);
    return (new OrderTag())->getSelectedTags($tagList);
  }

}