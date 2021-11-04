<?php

namespace ADIOS\Actions\Products\Variations;

class EditValues extends \ADIOS\Core\Action {
  public function render() {
    $uid = $this->uid;
    $idProduct = (int) $this->params['idProduct'];
    $refresh = (boolean) $this->params['refresh'];

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $productVariationModel = new \ADIOS\Widgets\Products\Models\ProductVariation($this->adios);
    $productVariationValueModel = new \ADIOS\Widgets\Products\Models\ProductVariationValue($this->adios);
    $productVariationGroupModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroup($this->adios);
    $productVariationGroupAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroupAssignment($this->adios);

    $product = $productModel->getById($idProduct);
    
    $idVariationGroup = (int) $product['VARIATION_GROUP']['id_variation_group'];

    $assignedProductIds = $productVariationGroupAssignmentModel->getByIdVariationGroup($idVariationGroup);
    $assignedVariationIds = $productVariationGroupModel->getByIdVariationGroup($idVariationGroup);

    $variations = [];
    $variationValues = [];
    foreach ($assignedVariationIds as $idVariation) {
      $variations[$idVariation] = $productVariationModel->getById($idVariation);
      $variationValues[$idVariation] = $productVariationValueModel->getByIdVariation($idVariation);

    }

    // variationsSelectHtml
    $variationsSelectHtml = "<table>";
    foreach ($productVariationModel->getAll() as $variation) {
      $variationsSelectHtml .= "
        <tr>
          <td>
            <input
              type='checkbox'
              id='{$uid}_chk_{$variation['id']}'
              data-id-variation='{$variation['id']}'
              ".(in_array($variation['id'], $assignedVariationIds) ? "checked" : "")."
              onchange='{$uid}_updateSelectedVariations()'
            >
          </td>
          <td>
            <label for='{$uid}_chk_{$variation['id']}'>
              ".hsc($variation['name_lang_1'])."
            </label>
          </td>
          <td>
      ";

      if (is_array($variationValues[$variation['id']])) {
        $variationsSelectHtml .= "
          <select onchange='{$uid}_updateSelectedVariations();'>
            <option value='0'>".$this->translate("Value not selected")."</option>
        ";
        foreach ($variationValues[$variation['id']] as $idValue => $variationValue) {
          $variationsSelectHtml .= "
            <option
              value='{$idValue}'
              ".($product['VARIATIONS'][$variation['id']]['id_value'] == $idValue ? "selected" : "")."
            >
              ".hsc($variationValue['value_lang_1'])."
            </option>
          ";
        }
        $variationsSelectHtml .= "
          </select>
        ";
      }
      $variationsSelectHtml .= "
          </td>
        </tr>
      ";
    }
    $variationsSelectHtml .= "</table>";

    // variationValuesHtml
    // $variationValuesHtml = "<table>";
    // foreach ($variationValues as $idVariation => $values) {
    //   $variationValuesHtml .= "
    //     <tr>
    //       <td style='padding:5px'>
    //         ".hsc($variations[$idVariation]['name_lang_1'])."
    //       </td>
    //       <td style='padding:5px'>
    //         <select
    //           data-id-product='{$idProduct}'
    //           data-id-variation='{$idVariation}'
    //           onchange='{$uid}_updateVariationAssignmentValues();'
    //         >
    //   ";
    //   foreach ($values as $value) {
    //     $variationValuesHtml .= "
    //       <option value='{$idVariation}'>".hsc($value)."</option>
    //     ";
    //   }

    //   $variationValuesHtml .= "
    //         </select>
    //       </td>
    //     </tr>
    //   ";
    // }
    // $variationValuesHtml .= "</table>";
    
    // variationGroupProductsHtml
    $variationGroupProductsHtml = "
      <hr/>
      <b>Products in this group of variations</b>
    ";
    foreach ($assignedProductIds as $tmpIdProduct) {
      if ($tmpIdProduct == $idProduct) continue;

      $tmpProduct = $productModel->getById($tmpIdProduct);
      $variationGroupProductsHtml .= "
        <div>
          <a
            href='javascript:void(0);'
            onclick='
              window_render(\"Products/{$tmpIdProduct}/Edit\");
            '
          >".hsc($tmpProduct['name_lang_1'])."</a>
          ".($tmpIdProduct == $idProduct ? "" : "
              <a
                href='javascript:void(0);'
                onclick='{$uid}_removeProductFromGroup({$tmpIdProduct});'
              >Remove</a>
            ")."
        </div>
      ";
    }
    $variationGroupProductsHtml .= "
    ";

    $productSelectInputHtml = (new \ADIOS\Core\UI\Input(
      $this->adios,
      [
        "uid" => "{$uid}_id_product",
        "type" => "lookup",
        "input_style" => "select",
        "model" => "Widgets/Products/Models/Product",
        "key_column" => "id_product",
        "onchange" => "{$uid}_addProductToGroup(this.value);",
      ]
    ))->render();

    $contentHtml = "
      <div id='{$uid}_main_div'>
        <!-- <a
          href='javascript:void(0)'
          class='btn btn-icon-split btn-light'
          style='margin-top:1em;'
          onclick='
            window_render(
              \"Products/{$idProduct}/Variations/ChooseAvailable\",
              {},
              function() {
                {$uid}_refresh();
              }
            );
          '
        >
          <span class=\"icon\"><i class=\"fas fa-euro-sign\"></i></span>
          <span class=\"text\">".$this->translate("Choose available product variations")."</span>
        </a>
        <br/> -->
        <div id='{$uid}_variations_div'>
          {$variationsSelectHtml}
        </div>

        <div>
          {$variationGroupProductsHtml}
        </div>
        Add product to this group of variations:<br/>
        {$productSelectInputHtml}

        <script>
          function {$uid}_updateSelectedVariations() {
            let variationIdsAndValues = [];

            $('#{$uid}_variations_div input[type=checkbox]:checked').each(function() {
              variationIdsAndValues.push({
                'id': $(this).data('id-variation'),
                'value': $(this).closest('tr').find('select').val(),
              });
            });

            _ajax_read_json(
              'Products/Variations/Ajax/SaveAvailable',
              {
                'idProduct': {$idProduct},
                'idVariationGroup': {$idVariationGroup},
                'variationIdsAndValues': JSON.stringify(variationIdsAndValues),
              },
              {$uid}_refresh
            )
          }

          function {$uid}_addProductToGroup(idProduct) {
            _ajax_read_json(
              'Products/Variations/Ajax/AddProductToGroup',
              {
                'idVariationGroup': {$idVariationGroup},
                'idProduct': idProduct,
              },
              {$uid}_refresh
            );
          }

          function {$uid}_removeProductFromGroup(idProduct) {
            _ajax_read_json(
              'Products/Variations/Ajax/RemoveProductFromGroup',
              {
                'idVariationGroup': {$idVariationGroup},
                'idProduct': idProduct
              },
              {$uid}_refresh
            );
          }
        </script>
        
        <script>
          function {$uid}_refresh() {
            _ajax_update(
              'Products/Variations/EditValues',
              {'refresh': true, 'idProduct': {$idProduct}},
              '{$uid}_main_div'
            );
          }
        </script>
      </div>
    ";

    if ($refresh) {
      return $contentHtml;
    } else {
      $window = $this->adios->ui->Window([
        "title" => "Manage product variations",
        "content" => $contentHtml,
      ]);

      $window->params['header'] = [
        $this->adios->ui->button([
          'type' => 'close',
          'onclick' => "window_close('{$window->params['uid']}');",
        ]),
      ];
      
      return $window->render();
    }
  }
}