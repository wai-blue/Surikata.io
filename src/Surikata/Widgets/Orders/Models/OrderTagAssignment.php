<?php

namespace ADIOS\Widgets\Orders\Models;


class OrderTagAssignment extends \ADIOS\Core\Widget\Model {
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

    return self::where('id_order', '=', $idOrder)->get()->toArray();

  }

  public function getTagIdsForOrder($idOrder) {

    $tags = [];
    foreach ($this->getTagsForOrder($idOrder) as $tag) {
      $tags[] = $tag["id_tag"];
    }
    return $tags;

  }

  public function saveOrderTags($idOrder, $tagIds) {
    $idOrder = (int) $idOrder;

    $this->adios->db->query("
      delete from `{$this->table}` WHERE `id_order` = {$idOrder};
    ");

    if (count($tagIds) > 0) {
      $insertQuery = "insert into `{$this->table}` (`id_order`, `id_tag`) values ";
      foreach ($tagIds as $idTag) {
        $idTag = (int) $idTag;
        if ($idTag > 0) {
          $insertQuery .= "({$idOrder}, {$idTag}), ";
        }
      }
      $insertQuery = trim($insertQuery, ", ");
      $this->adios->db->query($insertQuery);
    }
  }

}