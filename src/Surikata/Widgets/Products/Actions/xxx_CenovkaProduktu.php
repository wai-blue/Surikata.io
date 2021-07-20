<?php

namespace ADIOS\Actions\Products;

class CenovkaProduktu extends \ADIOS\Core\Action {
  public function render() {
    $tmp = $this->adios
      ->getModel("Widgets/Products/Models/Product")
      ->getPriceInfoForSingleProduct((int) $this->params['id_product'])
    ;

    $zakladnaPredajnaCena = $tmp['salePrice'];
    $calculationSteps = $tmp['calculationSteps'];

    $html = "
      <div class='card border-left-primary shadow mb-2 py-2 mr-2'>
        <div class='card-body'>
          <div class='row no-gutters align-items-center'>
            <div class='col mr-2'>
              <div class='text-xs font-weight-bold text-primary text-uppercase mb-1'>Basic selling price</div>
              <div class='h5 mb-0 font-weight-bold text-gray-800'>€ ".number_format($zakladnaPredajnaCena, 2, ",", " ")."</div>
              <div class='mt-2 text-xs text-danger'>
                The final sale price may differ if client is logged on and discounts or margins are set.
              </div>
            </div>
            <div class='col-auto'>
              <i class='fas fa-tag fa-2x text-gray-300'></i>
            </div>
          </div>
        </div>
      </div>
      <div class='card shadow mb-4'>
        <a href='#{$this->uid}_sposobVypoctuCard' class='d-block card-header py-3' data-toggle='collapse' role='button' aria-expanded='true' aria-controls='{$this->uid}_sposobVypoctuCard'>
          <h6 class='m-0 font-weight-bold text-primary'>Price calculation info</h6>
        </a>
        <div class='collapse' id='{$this->uid}_sposobVypoctuCard'>
          <div class='card-body'>
            <div class='table-responsive'>
              <table class='table table-bordered'>
                <thead>
                  <tr>
                    <th>Item</th>
                    <th class='text-right'>Calculated price</th>
                  </tr>
                </thead>
                <tbody>
    ";
    foreach ($calculationSteps as $step) {
      $html .= "
        <tr>
          <td>".hsc($step[0])."</td>
          <td class='text-right'>€ ".number_format($step[1], 2, ",", " ")."</td>
        </tr>
      ";
    }
    $html .= "
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    ";

    return $html;
  }
}