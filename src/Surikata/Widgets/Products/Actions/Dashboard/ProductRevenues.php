<?php

namespace ADIOS\Actions\Products\Dashboard;

class ProductRevenues extends \ADIOS\Core\Widget\Action {
  public function preRender() {
    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);
    $orderItemModel = new \ADIOS\Widgets\Orders\Models\OrderItem($this->adios);

    $q = $orderItemModel->pdo->prepare("
      select
        year(`o`.`confirmation_time`) as `year`,
        sum(`oi`.`unit_price` * `oi`.`quantity`) as `revenue`,
        `p`.`name_lang_1` as `product_name`
      from `".$orderItemModel->getFullTableSQLName()."` `oi`
      left join `".$productModel->getFullTableSQLName()."` `p` on `p`.`id` = `oi`.`id_product`
      left join `".$orderModel->getFullTableSQLName()."` `o` on `o`.`id` = `oi`.`id_order`
      group by `o`.`confirmation_time`
      having `year` = :year
      order by `revenue`
      limit 7
    ");
    $q->execute(["year" => (int) date("Y")]);
    $revenues = $q->fetchAll(\PDO::FETCH_ASSOC);

    $chartLabels = [];
    $data = [];

    foreach ($revenues as $revenues) {
      $chartLabels[] = $revenues['product_name'];
      $data[] = $revenues['revenue'];
    }

    return [
      "currentYear" => date("Y"),
      "chartLabels" => json_encode(array_reverse($chartLabels)),
      "data" => json_encode(array_reverse($data)),
    ];
  }
}