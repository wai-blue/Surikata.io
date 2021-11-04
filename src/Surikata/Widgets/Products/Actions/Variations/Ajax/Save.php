<?php

namespace ADIOS\Actions\Products\Variations\Ajax;

class Save extends \ADIOS\Core\Action {
  public function render() {
    $idProduct = (int) $this->params['idProduct'];
    $idVariationGroup = (int) $this->params['idVariationGroup'];
    $variationIdsAndValues = json_decode($this->params['variationIdsAndValues'], TRUE);

    try {
      // if (count($variationIds) == 0) throw new \ADIOS\Core\Exceptions\GeneralException("No variation IDs.");

      $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
      $productVariationAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationAssignment($this->adios);
      $productVariationGroupModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroup($this->adios);
      $productVariationGroupAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroupAssignment($this->adios);

      $this->adios->db->query("set foreign_key_checks = 0");

      $this->adios->db->startTransaction();

      if ($idVariationGroup <= 0) {
        $this->adios->db->query("
          set @___tmp_id_variation_group := (ifnull(
            (
              select
                ifnull(max(`pvg`.`id_variation_group`), 0)
              from `".$productVariationGroupModel->getFullTableSQLName()."` pvg
            ),
            0
          ) + 1)
        ");

       } else {
        $this->adios->db->query("set @___tmp_id_variation_group := {$idVariationGroup}");
       }

      $this->adios->db->query("
        delete from `".$productVariationGroupModel->getFullTableSQLName()."`
        where `id_variation_group` = @___tmp_id_variation_group
      ");

      $this->adios->db->query("
        delete from `".$productVariationAssignmentModel->getFullTableSQLName()."`
        where `id_product` = {$idProduct}
      ");

      foreach ($variationIdsAndValues as $tmp) {
        $idVariation = (int) $tmp['id'];
        $variationValue = $tmp['value'];

        if ($idVariation == 0) continue;

        $productVariationGroupModel->insertRow([
          "id_variation_group" => ["sql" => "@___tmp_id_variation_group"],
          "id_variation" => $idVariation,
        ]);

        $productVariationAssignmentModel->insertRow([
          "id_variation_group" => ["sql" => "@___tmp_id_variation_group"],
          "id_product" => $idProduct,
          "id_variation" => $idVariation,
          "id_value" => $variationValue,
        ]);
      }

      $this->adios->db->commit();

      $this->adios->db->query("set foreign_key_checks = 1");

      $tmp = reset($this->adios->db->get_all_rows_query("
        select @___tmp_id_variation_group
      "));
      $idVariationGroup = $tmp["@___tmp_id_variation_group"];

      $productVariationGroupAssignmentModel->addProductToVariationGroup($idVariationGroup, $idProduct);

      return [
        "params" => $this->params,
        "idVariationGroup" => $idVariationGroup,
      ];
    } catch (\Exception $e) {
      return $this->adios->renderFatal([
        "error" => $e->getMessage(),
      ]);
    }
  }
}