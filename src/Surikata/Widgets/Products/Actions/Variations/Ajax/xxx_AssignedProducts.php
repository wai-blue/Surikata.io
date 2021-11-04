<?php

namespace ADIOS\Actions\Products\Variations\Ajax;

class AssignedProducts extends \ADIOS\Core\Action {
  public function render() {
    $uid = $this->uid;

    $parentUid = $this->params['parentUid'];
    $idProduct = (int) $this->params['idProduct'];

    $this->adios->checkUid($parentUid);

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $productVariationGroupAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroupAssignment($this->adios);

    $product = $productModel->getById($idProduct);
    $idVariationGroup = (int) $product['VARIATION_GROUP']['id_variation_group'];

    $assignedProductIds = $productVariationGroupAssignmentModel->getByIdVariationGroup($idVariationGroup);

    $html = "<table id='{$uid}_variation_values_table' style='width:100%'>";

    foreach ($assignedProductIds as $assignedProductId) {
      $assignedProduct = $productModel->getById($assignedProductId);
      $html .= "
        <tr>
          <td>".hsc($assignedProduct['name_lang_1'])."</td>
      ";
      $html .= "
          <td>
          ".($assignedProductId == $idProduct ? "" : "
              <a
                href='javascript:void(0);'
                onclick='{$uid}_deassignProduct({$assignedProductId});'
              >Remove</a>
            ")."
          </td>
        </tr>
      ";
    }

    $html .= "</table>";

    return "
      {$html}
      <script>
        function {$uid}_deassignProduct(idProduct) {
          _ajax_read_json(
            'Products/Variations/Ajax/DeassignProduct',
            {'idVariationGroup': {$idVariationGroup}, 'idProduct': idProduct},
            function(res) {
              {$parentUid}_updateAssignedProducts();
            }
          );
        }

        // function {$uid}_updateVariationAssignmentValues() {
        //   let data = {
        //     'idVariationAssignment': {$idVariationGroup},
        //     'idProduct': idProduct};

        //   $('#{$uid}_variation_values_table select').each(function() {
        //   })
        // }
      </script>
    ";
  }
}