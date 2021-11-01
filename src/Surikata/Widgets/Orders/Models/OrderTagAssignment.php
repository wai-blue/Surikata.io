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

    $tags = self::where('id_order', '=', $idOrder)->get()->toArray();
    return $tags;

  }

  public function saveOrderTags($idOrder, $tags) {
    $idOrder = (int) $idOrder;

    $this->adios->db->query("
      delete from `{$this->table}` WHERE `id_order` = {$idOrder};
    ");

    if (count($tags) > 0) {
      $insertQuery = "insert into `{$this->table}` (`id_order`, `id_tag`) values ";
      foreach ($tags as $tag) {

        $insertQuery .= "({$idOrder}, {$tag}), ";
      }
      $insertQuery = trim($insertQuery, ", ");
      $this->adios->db->query($insertQuery);
    }
  }

}