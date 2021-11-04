<?php

namespace ADIOS\Widgets\Products\Models;

class ProductVariationGroupAssignment extends \ADIOS\Core\Model {
  var $sqlName = "products_variations_groups_assignments";
  var $urlBase = "Products/Variations/Assignments";
  var $lookupSqlValue = "{%TABLE%}.name_lang_1";

  public function init() {
    $this->tableTitle = $this->translate("Product variation assignment");
  }

  public function columns(array $columns = []) {
    $columns = parent::columns([
      "id_variation_group" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/ProductVariationGroup",
        "foreign_key_column" => "id_variation_group",
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
      ],
    ]);

    return $columns;
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      [
        "type" => "unique",
        "columns" => ["id_variation_group", "id_product"],
      ],
    ]);
  }

  public function getByIdVariationGroup(int $idVariationGroup) {
    return $this->where("id_variation_group", $idVariationGroup)->pluck('id_product')->toArray();
  }

  public function addProductToVariationGroup(int $idVariationGroup, int $idProduct) {
    if ($idVariationGroup <= 0 || $idProduct <= 0) {
      return FALSE;
    }

    $assignedProductIds = $this->getByIdVariationGroup($idVariationGroup);
    if (!in_array($idProduct, $assignedProductIds)) {
      $this->insertRow(["id_variation_group" => $idVariationGroup, "id_product" => $idProduct]);
    }

  }

  public function removeProductFromVariationGroup(int $idVariationGroup, int $idProduct) {
    if ($idVariationGroup <= 0 || $idProduct <= 0) {
      return FALSE;
    }

    $this->adios->db->query("
      delete from `{$this->table}`
      where
        `id_variation_group` = {$idVariationGroup}
        and `id_product` = {$idProduct}
    ");
  }

}