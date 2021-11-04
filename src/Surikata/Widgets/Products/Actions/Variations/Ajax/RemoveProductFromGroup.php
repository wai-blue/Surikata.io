<?php

namespace ADIOS\Actions\Products\Variations\Ajax;

class RemoveProductFromGroup extends \ADIOS\Core\Action {
  public function render() {
    $idProduct = (int) $this->params['idProduct'];
    $idVariationGroup = (int) $this->params['idVariationGroup'];

    try {
      $productVariationGroupAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroupAssignment($this->adios);
      $productVariationGroupAssignmentModel->removeProductFromVariationGroup($idVariationGroup, $idProduct);

      return [
        "idVariationGroup" => $idVariationGroup,
      ];
    } catch (\Exception $e) {
      return $this->adios->renderFatal([
        "error" => $e->getMessage(),
      ]);
    }
  }
}