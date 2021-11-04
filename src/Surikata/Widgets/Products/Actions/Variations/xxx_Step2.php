<?php

namespace ADIOS\Actions\Products\Variations;

class Step2 extends \ADIOS\Core\Action {
  public function render() {
    $uid = $this->uid;
    $idProduct = (int) $this->params['idProduct'];

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $productVariationGroupAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroupAssignment($this->adios);

    $product = $productModel->getById($idProduct);
    $idVariationGroup = (int) $product['VARIATION_GROUP']['id_variation_group'];

    $variationProducts = $productVariationGroupAssignmentModel->getByIdVariationGroup($idVariationGroup);

    $window = $this->adios->ui->Window([
      'title' => hsc($product['name_lang_1'])." - Variations",
    ]);

    $productSelectInputHtml = (new \ADIOS\Core\UI\Input(
      $this->adios,
      [
        "uid" => "{$uid}_id_product",
        "type" => "lookup",
        "input_style" => "autocomplete",
        "model" => "Widgets/Products/Models/Product",
        "key_column" => "id_product",
        "onchange" => "{$uid}_assignProduct(this.value);",
      ]
    ))->render();

    $window->setContent("
      {$productSelectInputHtml}

      <div id='{$uid}_assigned_products_div'></div>

      <script>
        function {$uid}_assignProduct(idProduct) {
          _ajax_read_json(
            'Products/Variations/Ajax/AssignProduct',
            {
              'idVariationGroup': {$idVariationGroup},
              'idProduct': idProduct,
            },
            function(res) {
              {$uid}_updateAssignedProducts();
            }
          );
        }

        function {$uid}_updateAssignedProducts() {
          _ajax_update(
            'Products/Variations/Ajax/AssignedProducts',
            {'parentUid': '{$uid}', 'idProduct': {$idProduct}},
            '{$uid}_assigned_products_div'
          );
        }

        {$uid}_updateAssignedProducts();
      </script>
    ");

    $window->params['header'] = [
      $this->adios->ui->button([
        'type' => 'close',
        'onclick' => "window_close('{$window->params['uid']}');",
      ]),
    ];
    
    return $window->render();
  }
}