<?php

use ADIOS\Widgets\Orders\Models\Order;
use ADIOS\Widgets\Customers\Models\Customer;

use ADIOS\Widgets\Orders\Exceptions\UnknownCustomer;
use ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID;
use ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields;
use ADIOS\Core\DBDuplicateEntryException;

class OrderTest extends SurikataTestCase {
  public function testUnknownCustomer(): void {
    $this->expectException(UnknownCustomer::class);
    (new Order($this->adminPanel))->placeOrder([]);
  }

  public function testInvalidCustomerID(): void {
    $this->expectException(InvalidCustomerID::class);
    (new Order($this->adminPanel))->placeOrder(['id_customer' => -1]);
  }

  public function testEmptyFields(): void {
    $this->expectException(EmptyRequiredFields::class);
    $customers = (new Customer($this->adminPanel))->getAll();
    (new Order($this->adminPanel))->placeOrder([
      'id_customer' => reset(array_keys($customers))
    ]);
  }

  public function testSameOrderNumber(): void {
    $this->expectException(DBDuplicateEntryException::class);
    $customer = reset((new Customer($this->adminPanel))->getAll());

    $addresses = $customer['ADDRESSES'] ?? [];
    $address = reset($addresses);

    (new Order($this->adminPanel))->placeOrder([
      'id_customer' => $customer['id'],
      "number" => date("Ymd")."9999",
      'id_customer' => $customer['id'],
      'inv_given_name' => $address['inv_given_name'] ?? '',
      'inv_family_name' => $address['inv_family_name'] ?? '',
      'inv_street_1' => $address['inv_street_1'] ?? '',
      'inv_city' => $address['inv_city'] ?? '',
      'inv_zip' => $address['inv_zip'] ?? '',
      'phone_number' => $address['phone_number'] ?? '',
      'email' => $address['email'] ?? '',
    ]);

    (new Order($this->adminPanel))->placeOrder([
      'id_customer' => $customer['id'],
      "number" => date("Ymd")."9999",
      'id_customer' => $customer['id'],
      'inv_given_name' => $address['inv_given_name'] ?? '',
      'inv_family_name' => $address['inv_family_name'] ?? '',
      'inv_street_1' => $address['inv_street_1'] ?? '',
      'inv_city' => $address['inv_city'] ?? '',
      'inv_zip' => $address['inv_zip'] ?? '',
      'phone_number' => $address['phone_number'] ?? '',
      'email' => $address['email'] ?? '',
    ]);
  }

  public function testOneOrderPerCustomer(): void {
    $this->expectNotToPerformAssertions();

    $customers = (new Customer($this->adminPanel))->getAll();

    foreach ($customers as $customer) {
      $addresses = $customer['ADDRESSES'] ?? [];
      $address = $addresses[rand(0, count($addresses) - 1)];

      (new Order($this->adminPanel))->placeOrder([
        'id_customer' => $customer['id'],
        'inv_given_name' => $address['inv_given_name'] ?? '',
        'inv_family_name' => $address['inv_family_name'] ?? '',
        'inv_street_1' => $address['inv_street_1'] ?? '',
        'inv_city' => $address['inv_city'] ?? '',
        'inv_zip' => $address['inv_zip'] ?? '',
        'phone_number' => $address['phone_number'] ?? '',
        'email' => $address['email'] ?? '',
      ]);
    }
  }

  public function testIssueInvoicesForAllOrders(): void {
    $this->expectNotToPerformAssertions();

    $orderModel = new Order($this->adminPanel);
    $orders = $orderModel->getAll();

    foreach ($orders as $order) {
      $orderModel->issueInvoce($order['id']);
    }

  }
}