<?php

namespace ADIOS\Actions\Products\Variations;

class ChooseAvailable extends \ADIOS\Core\Action {
  public function render() {
    $uid = $this->uid;
    $idProduct = (int) $this->params['idProduct'];

    $window = $this->adios->ui->Window([
      'title' => "Product Variations - Step 1",
    ]);

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $productVariationModel = new \ADIOS\Widgets\Products\Models\ProductVariation($this->adios);
    $productVariationGroupModel = new \ADIOS\Widgets\Products\Models\ProductVariationGroup($this->adios);

    $product = $productModel->getById($idProduct);
    $variations = $productVariationModel->getAll();
    $idVariationGroup = (int) $product['VARIATION_GROUP']['id_variation_group'];

    $productVariations = $productVariationGroupModel->getByIdVariationGroup($idVariationGroup);

    $variationsSelectHtml = "";
    $i = 0;
    foreach ($variations as $variation) {
      $variationsSelectHtml .= "
        <div>
          <input
            type='checkbox'
            id='{$uid}_chk_{$i}'
            data-id-variation='{$variation['id']}'
            ".(in_array($variation['id'], $productVariations) ? "checked" : "")."
          >
          <label for='{$uid}_chk_{$i}'>
            ".hsc($variation['name_lang_1'])."
          </label>
        </div>
      ";
      $i++;
    }

    $window->setContent("
      <div id='{$uid}_variations_div'>
        {$variationsSelectHtml}
      </div>
      <a
        href='javascript:void(0);'
        onclick='{$uid}_next();'
      >".$this->translate("Next")."</a>

      <script>
        function {$uid}_next() {
          data = {
            'idProduct': {$idProduct},
            'idVariationGroup': {$idVariationGroup},
            'variationIds': '',
          };

          $('#{$uid}_variations_div input[type=checkbox]:checked').each(function() {
            data.variationIds += (data.variationIds == '' ? '' : ',') + $(this).data('id-variation');
          });

          _ajax_read_json(
            'Products/Variations/Ajax/Save',
            data,
            function(res) { // onsuccess
              window_close('{$window->params['uid']}');
            }
          )
        }
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