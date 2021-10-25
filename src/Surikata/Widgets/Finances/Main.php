<?php

namespace ADIOS\Widgets;

class Finances extends \ADIOS\Core\Widget {
  public function init() {
    // TODO: ked budeme robit dobropisy, nastudovat toto https://www.superfaktura.sk/blog/dobropis-vzor-a-niekolko-pravidiel/

    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Finances'] = [
        "fa_icon" => "fas fa-file-invoice-dollar",
        "title" => $this->translate("Invoices"),
        "onclick" => "desktop_update('Invoices');",
        "sub" => [
          [
            "title" => $this->translate("Settings"),
            "sub" => [
              [
                "title" =>  $this->translate("Merchant profiles"),
                "onclick" => "desktop_update('Merchants');",
              ],
              [
                "title" =>  $this->translate("Numeric series"),
                "onclick" => "desktop_update('Invoices/NumericSeries');",
              ],
            ],
          ],
        ],
      ];
    }
  }

  public static function calculatePricesForInvoice($items) {
    foreach ($items as $key => $item) {
      $quantity = $item['quantity'];
      $vatPercent = $item['vat_percent'];

      $unitPriceExclVAT = round($item['unit_price'], 2);
      $totalPriceExclVAT = round($unitPriceExclVAT * $quantity, 2);

      $unitVAT = round($unitPriceExclVAT * ($vatPercent / 100), 2);
      $totalVAT = round($unitVAT * $quantity, 2);

      $unitPriceInclVAT = $unitPriceExclVAT + $unitVAT;
      $totalPriceInclVAT = $totalPriceExclVAT + $totalVAT;

      $items[$key]['PRICES_FOR_INVOICE']['vatPercent'] = $vatPercent;

      // REVIEW: poprekladat
      $items[$key]['PRICES_FOR_INVOICE']['unitPriceExclVAT'] = $unitPriceExclVAT;
      $items[$key]['PRICES_FOR_INVOICE']['unitVAT'] = $unitVAT;
      $items[$key]['PRICES_FOR_INVOICE']['unitPriceInclVAT'] = $unitPriceInclVAT;

      $items[$key]['PRICES_FOR_INVOICE']['totalPriceExclVAT'] = $totalPriceExclVAT;
      $items[$key]['PRICES_FOR_INVOICE']['totalVAT'] = $totalVAT;
      $items[$key]['PRICES_FOR_INVOICE']['totalPriceInclVAT'] = $totalPriceInclVAT;

      // if (!is_array($items['VAT_GROUPS'][$vatPercent])) {
      //   $items['VAT_GROUPS'][$vatPercent] = [
      //     "vatPercent" => $vatPercent,
      //     "totalPriceExclVAT" => 0,
      //     "totalVAT" => 0,
      //     "totalPriceInclVAT" => 0,
      //   ];
      // }

      // $items['VAT_GROUPS'][$vatPercent]['totalPriceExclVAT'] += $totalPriceExclVAT;
      // $items['VAT_GROUPS'][$vatPercent]['totalVAT'] += $totalVAT;
      // $items['VAT_GROUPS'][$vatPercent]['totalPriceInclVAT'] += $totalPriceInclVAT;
    }

    return $items;
  }

}