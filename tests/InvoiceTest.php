<?php

use ADIOS\Widgets\Orders\Models\Order;
// use ADIOS\Widgets\Customers\Models\Customer;

// use ADIOS\Widgets\Orders\Exceptions\UnknownCustomer;
// use ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID;
// use ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields;
// use ADIOS\Core\DBDuplicateEntryException;

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