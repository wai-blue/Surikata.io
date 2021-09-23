<?php

use ADIOS\Widgets\Orders\Models\Order;

class InvoiceTest extends SurikataTestCase {
  public function testIssueInvoicesForAllOrders(): void {
    $this->expectNotToPerformAssertions();

    $orderModel = new Order($this->adminPanel);
    $orders = $orderModel->getAll();

    foreach ($orders as $order) {
      $orderModel->issueInvoce($order['id']);
    }

  }
}