<?php

namespace ADIOS\Widgets\Products\Models;


class ProductFeatureOptionAssignment extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_features_options_assignment";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_feature" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductFeature",
        "title" => "Feature",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_feature_option" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductFeatureOption",
        "title" => "Option",
        "required" => TRUE,
        "show_column" => TRUE,
      ]
    ]);
  }

  public function getOptionsIdsForFeature($idFeature) {
    return 
      self::where('id_feature', '=', $idFeature)
      ->pluck("id_feature_option")
      ->toArray()
    ;
  }

  /*public function saveOrderTags($idOrder, $tagIds) {
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
  }*/

}