<?php

namespace ADIOS\Actions\Finances\Dashboard;

class MonthlyTurnover extends \ADIOS\Core\Widget\Action {
  public function preRender() {
    $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($this->adios);
    $invoiceItemModel = new \ADIOS\Widgets\Finances\Models\InvoiceItem($this->adios);

    $turnover = $invoiceItemModel->pdoPrepareExecuteAndFetch("
      select
        year(`i`.`delivery_time`) as `year`,
        month(`i`.`delivery_time`) as `month`,
        sum(`ii`.`unit_price` * `ii`.`quantity`) as `turnover`
      from `:table` `ii`
      left join `".$invoiceModel->getFullTableSQLName()."` `i` on `i`.`id` = `ii`.`id_invoice`
      group by `i`.`delivery_time`
      having `year` = :year
      order by month(`i`.`delivery_time`)
    ", ["year" => (int) date("Y")]);

    $data = [
      1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0,
      7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0
    ];

    foreach ($turnover as $turnoverMonth) {
      $data[$turnoverMonth['month']] = $turnoverMonth['turnover'];
    }

    return [
      "currentYear" => date("Y"),
      "data" => json_encode(array_values($data)),
    ];
  }
}