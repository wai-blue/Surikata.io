<?php

namespace ADIOS\Widgets\Finances\Models;

class Invoice extends \ADIOS\Core\Widget\Model {

  /* Invoice payment methods */
  const PAYMENT_METHOD_WIRE_TRANSFER = 1;
  const PAYMENT_METHOD_CASH          = 2;
  const PAYMENT_METHOD_CHEQUE        = 3;
  const PAYMENT_METHOD_CARD          = 4;

  /* Invoice states */
  const STATE_ISSUED = 1;
  const STATE_SENT   = 2;
  const STATE_PAID   = 3;

  /* Invoice languages */
  const LANGUAGE_SK = "sk";
  const LANGUAGE_EN = "en";

  const TEMPLATE_WITH_VAT       = 1;
  const TEMPLATE_WITHOUT_VAT    = 2;
  const TEMPLATE_FOREIGN_INV    = 3;

  var $sqlName = "invoices";
  var $lookupSqlValue = "{%TABLE%}.number";
  var $urlBase = "Invoices";

  var $disableNotifications = FALSE;

  public function init() {
    $this->tableTitle = $this->translate("Invoices");
    $this->formTitleForInserting = $this->translate("New invoice");
    $this->formTitleForEditing = $this->translate("Invoice nr.")." {{ number }}";

    $this->enumInvoicePaymentMethods = [
      self::PAYMENT_METHOD_WIRE_TRANSFER => $this->translate("Bank transfer"),
      self::PAYMENT_METHOD_CASH          => $this->translate("In cash"),
      self::PAYMENT_METHOD_CHEQUE        => $this->translate("Cash on delivery"),
      self::PAYMENT_METHOD_CARD          => $this->translate("Payment card"),
    ];

    $this->enumInvoiceStates = [
      self::STATE_ISSUED => $this->translate("Issued"),
      self::STATE_SENT   => $this->translate("Sent"),
      self::STATE_PAID   => $this->translate("Paid"),
    ];

    $this->enumInvoiceLanguages = [
      self::LANGUAGE_SK   => $this->translate("Slovak"),
      self::LANGUAGE_EN   => $this->translate("English"),
    ];

    $this->enumInvoiceTemplates = [
      self::TEMPLATE_WITH_VAT        => $this->translate('With Value Added Tax'),
      self::TEMPLATE_WITHOUT_VAT     => $this->translate('Without Value Added Tax'),
      self::TEMPLATE_FOREIGN_INV     => $this->translate('Foreign invoice'),
    ];
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_numeric_series" => [
        "type" => "lookup",
        "title" => $this->translate("Numeric series"),
        "model" => "Widgets/Finances/Models/InvoiceNumericSeries",
      ],

      "accounting_year" => [
        "type" => "int",
        "title" => $this->translate("Accounting Year"),
        "readonly" => TRUE,
        "description" => $this->translate("Will be generated automaticaly"),
      ],

      "serial_number" => [
        "type" => "int",
        "title" => $this->translate("Serial number"),
        "readonly" => TRUE,
        "description" => $this->translate("Will be generated automaticaly"),
      ],

      "number" => [
        "type" => "varchar",
        "title" => $this->translate("Number"),
        "show_column" => TRUE,
      ],

      "id_customer" => [
        "type" => "lookup",
        "title" => $this->translate("Client"),
        "model" => "Widgets/Customers/Models/Customer",
        "show_column" => TRUE,
      ],

      "id_order" => [
        "type" => "lookup",
        "title" => $this->translate("Order"),
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => FALSE,
      ],

      // CUSTOMER

        "customer_name" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Name on the Invoice"),
          "show_column" => TRUE,
        ],

        "customer_street_1" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Street, 1st line"),
        ],

        "customer_street_2" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Street, 2nd line"),
        ],

        "customer_city" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("City"),
        ],

        "customer_zip" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("ZIP"),
        ],

        "customer_country" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Country"),
        ],

        "customer_company_id" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Company ID"),
          "show_column" => TRUE,
        ],

        "customer_company_tax_id" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Company TAX ID"),
        ],

        "customer_company_vat_id" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Company VAT ID"),
        ],

        "customer_email" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("E-mail"),
        ],

        "customer_phone" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Phone number"),
        ],

        "customer_www" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Web"),
        ],

        "customer_iban" => [
          "type" => "varchar",
          "title" => $this->translate("Customer, ").$this->translate("Bank account IBAN"),
        ],

      // DODAVATEL

        "supplier_name" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Name on the Invoice"),
        ],

        "supplier_street_1" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Street, 1st line"),
        ],

        "supplier_street_2" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Street, 2nd line"),
        ],

        "supplier_city" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("City"),
        ],

        "supplier_zip" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("ZIP"),
        ],

        "supplier_country" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Country"),
        ],

        "supplier_company_id" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Company ID"),
        ],

        "supplier_company_tax_id" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Company TAX ID"),
        ],

        "supplier_company_vat_id" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Company VAT ID"),
        ],

        "supplier_email" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("E-mail"),
        ],

        "supplier_phone" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Phone number"),
        ],

        "supplier_www" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Web"),
        ],

        "supplier_iban" => [
          "type" => "varchar",
          "title" => $this->translate("Supplier, ").$this->translate("Bank account IBAN"),
        ],

      // DATUMY

        "issue_time" => [
          "type" => "datetime",
          "title" => $this->translate("Issued"),
          "show_column" => TRUE,
        ],

        "delivery_time" => [
          "type" => "datetime",
          "title" => $this->translate("Delivered"),
          "show_column" => TRUE,
        ],

        "payment_due_time" => [
          "type" => "datetime",
          "title" => $this->translate("Payment Due"),
          "show_column" => TRUE,
        ],

        // "datum_uhrady" => [
        //   "type" => "date",
        //   "title" => "Dátum úhrady",
        //   "show_column" => TRUE,
        // ],

      // OSTATNE

        "variable_symbol" => [
          "type" => "varchar",
          "title" => $this->translate("Variable symbol"),
          "show_column" => TRUE,
        ],

        "specific_symbol" => [
          "type" => "varchar",
          "title" => $this->translate("Specific symbol"),
        ],

        "constant_symbol" => [
          "type" => "varchar",
          "title" => $this->translate("Constant symbol"),
        ],

        "payment_method" => [
          "type" => "int",
          "enum_values" => $this->enumInvoicePaymentMethods,
          "title" => $this->translate("Forma úhrady"),
          "show_column" => FALSE,
        ],

        "order_number" => [
          "type" => "varchar",
          "title" => $this->translate("Order number"),
        ],

        "price_total_excl_vat" => [
          "type" => "float",
          "title" => $this->translate("Total price excl. VAT"),
          "unit" => $this->adios->locale->currencySymbol(),
          "readonly" => TRUE,
          "show_column" => TRUE,
        ],

        "price_total_incl_vat" => [
          "type" => "float",
          "title" => $this->translate("Total price incl. VAT"),
          "unit" => $this->adios->locale->currencySymbol(),
          "readonly" => TRUE,
          "show_column" => TRUE,
        ],

        "notes" => [
          "type" => "varchar",
          "byte_size" => 255,
          "title" => $this->translate("Notes"),
          "show_column" => TRUE,
        ],

        "state" => [
          "type" => "int",
          "byte_size" => 8,
          "enum_values" => $this->enumInvoiceStates,
          "title" => $this->translate("State"),
          "show_column" => TRUE,
        ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "invoice___accounting_year___serial_number" => [
        "type" => "unique",
        "columns" => ["accounting_year", "serial_number"],
      ],
      "invoice___number" => [
        "type" => "unique",
        "columns" => ["number"],
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Invoices\/(\d+)\/PrintInvoice$/' => [
        "action" => "Finances/Invoices/PrintInvoice",
        "params" => [
          "id" => '$1',
        ]
      ],
      '/^Invoices\/(\d+)\/TlacitDodaciList$/' => [
        "action" => "Finances/Invoices/TlacitDodaciList",
        "params" => [
          "id" => '$1',
        ]
      ],
    ]);
  }

  public function getById($id) {
    $id = (int) $id;

    $invoice = reset($this->adios->db->get_all_rows_query("
      select
        fv.*
      from {$this->table} fv
      where fv.id = {$id}
    "));

    return $this->getExtendedData($invoice);
  }

  public function getByNumber($number) {
    $invoice = reset($this->adios->db->get_all_rows_query("
      select
        inv.*
      from {$this->table} inv
      where inv.number = '".$this->adios->db->escape($number)."'
    "));

    return $this->getExtendedData($invoice);
  }

  public function getExtendedData($invoice) {
    if ($invoice['id'] > 0) {
      $orderModel = $this->adios->getModel("Widgets/Orders/Models/Order");
      $invoiceItemModel = $this->adios->getModel("Widgets/Finances/Models/InvoiceItem");

      $invoice['ORDER'] = reset($this->adios->db->get_all_rows_query("
        select
          o.*
        from {$orderModel->table} o
        where o.id_invoice = {$invoice['id']}
      "));

      $invoice['ITEMS'] = $this->adios->db->get_all_rows_query("
        select
          p.*
        from {$invoiceItemModel->table} p
        where p.id_invoice = {$invoice['id']}
      ");

      $invoice['ITEMS'] = (new \ADIOS\Widgets\Finances($this->adios))
        ->calculatePricesForInvoice($invoice['ITEMS'])
      ;

      // $sumaCelkomBezDPH = 0;
      // $sumaCelkomSDPH = 0;
      // $sumaCelkomDPH = 0;

      // foreach ($invoice['ITEMS'] as $key => $item) {
      //   $tmpMnozstvo = $invoice['ITEMS'][$key]['quantity'];
      //   $tmpSadzbaDPH = $invoice['ITEMS'][$key]['vat_percent'];

      //   $tmpJednotkovaCenaBezDPH = $invoice['ITEMS'][$key]['unit_price'];
      //   $tmpSumaBezDPH = round($tmpJednotkovaCenaBezDPH * $tmpMnozstvo, 2);

      //   $tmpJednotkovaCenaDPH = round($tmpJednotkovaCenaBezDPH * ($tmpSadzbaDPH / 100), 2);
      //   $tmpSumaDPH = round($tmpJednotkovaCenaDPH * $tmpMnozstvo, 2);

      //   $tmpJednotkovaCenaSDPH = $tmpJednotkovaCenaBezDPH + $tmpJednotkovaCenaDPH;
      //   $tmpSumaSDPH = $tmpSumaBezDPH + $tmpSumaDPH;


      //   $invoice['ITEMS'][$key]['sadzba_dph'] = $tmpSadzbaDPH;

      //   // REVIEW: poprekladat
      //   $invoice['ITEMS'][$key]['jednotkova_cena_bez_dph'] = $tmpJednotkovaCenaBezDPH;
      //   $invoice['ITEMS'][$key]['jednotkova_cena_dph'] = $tmpJednotkovaCenaDPH;
      //   $invoice['ITEMS'][$key]['jednotkova_cena_s_dph'] = $tmpJednotkovaCenaSDPH;

      //   $invoice['ITEMS'][$key]['suma_bez_dph'] = $tmpSumaBezDPH;
      //   $invoice['ITEMS'][$key]['suma_dph'] = $tmpSumaDPH;
      //   $invoice['ITEMS'][$key]['suma_s_dph'] = $tmpSumaSDPH;

      //   if (!is_array($invoice['SADZBY_DPH'][$tmpSadzbaDPH])) {
      //     $invoice['SADZBY_DPH'][$tmpSadzbaDPH] = [
      //       "sadzba" => $tmpSadzbaDPH,
      //       "zaklad_celkom" => 0,
      //       "suma_celkom" => 0,
      //     ];
      //   }

      //   $invoice['SADZBY_DPH'][$tmpSadzbaDPH]['zaklad_celkom'] += $invoice['ITEMS'][$key]['suma_bez_dph'];
      //   $invoice['SADZBY_DPH'][$tmpSadzbaDPH]['suma_celkom'] += $tmpSumaDPH;

      //   $sumaCelkomDPH += $tmpSumaDPH;
      //   $sumaCelkomBezDPH += $tmpSumaBezDPH;
      //   $sumaCelkomSDPH += $tmpSumaSDPH;
      // }

      $invoice['SUMMARY'] = $this->calculateSummaryInfo($invoice);
    }

    return $invoice;
  }

  public function deleteById($idInvoice) {
    $idInvoice = (int) $idInvoice;

    $orderModel = $this->adios->getModel("Widgets/Orders/Models/Order");
    $invoiceItemModel = $this->adios->getModel("Widgets/Finances/Models/InvoiceItem");

    $invoice = $this->getById($idInvoice);

    $tmp = reset($this->adios->db->get_all_rows_query("
      select
        i.*
      from {$this->table} i
      where
        i.accounting_year = ".(int) $invoice['accounting_year']."
        and i.serial_number > ".(int) $invoice['serial_number']."
      limit 1
    "));

    if ($tmp['id'] > 0) {
      return "Unable to delete the invoice. Invoice with higher serial number have been issued already.";
    } else {
      $this->adios->db->query("delete from `".$invoiceItemModel->getFullTableSqlName()."` where `id_invoice` = {$idInvoice}");
      $this->adios->db->query("delete from `{$this->table}` where `id` = {$idInvoice}");
      $this->adios->db->query("
        update `".$orderModel->getFullTableSqlName()."` set
          `id_invoice` = 0,
          `state` = ".\ADIOS\Widgets\Orders\Models\Order::STATE_NEW."
        where `id_invoice` = {$idInvoice}
      ");

      return TRUE;
    }
  }

  public function calculateSummaryInfo($invoice) {
    $summary = [
      "vat_total" => 0,
      'price_total_excl_vat' => 0,
      'price_total_incl_vat' => 0,
    ];

    $invoice['ITEMS'] = (new \ADIOS\Widgets\Finances($this->adios))
      ->calculatePricesForInvoice($invoice['ITEMS'])
    ;

    foreach ($invoice['ITEMS'] as $item) {
      $summary['vat_total'] += $item['PRICES_FOR_INVOICE']['totalVAT'];
      $summary['price_total_excl_vat'] += $item['PRICES_FOR_INVOICE']['totalPriceExclVAT'];
      $summary['price_total_incl_vat'] += $item['PRICES_FOR_INVOICE']['totalPriceInclVAT'];
    }

    return $summary;

  }

  public function updateSummaryInfo($idInvoice, $summary) {
    $this->updateRow([
      'price_total_excl_vat' => $summary['price_total_excl_vat'],
      'price_total_incl_vat' => $summary['price_total_incl_vat'],
    ], $idInvoice);
  }

  public function onAfterSave($data, $returnValue) {
    if ($data['id'] > 0) {
      $invoice = $this->getById($data['id']);
      $summary = $this->calculateSummaryInfo($invoice);
      $this->updateSummaryInfo($data['id'], $summary);
    }
  }

  public function tableParams($params) {
    $params['order_by'] = "number DESC";

    return $params;
  }

  public function formParams($data, $params) {
    if ($data['id'] <= 0) {
      $params['title'] = $this->translate("New invoice");

      $params["template"] = [
        "columns" => [
          [
            "tabs" => [
              $this->translate("Header") => [
                "id_customer",
              ],
            ],
          ],
        ],
      ];

    } else {
      $params['title'] = "Invoice nr. ".hsc($data['number']);

      $btnPrintInvoiceHtml = $this->adios->ui->button([
        "text"    => $this->translate("Print invoice"),
        "onclick" => "
          var invoiceLanguage = $('#".$params["uid"]."_invoiceLanguage').val();
          var invoiceTemplate = $('#".$params["uid"]."_enumInvoiceTemplates').val();
          window.open(_APP_URL + '/Invoices/".(int) $data['id']."/PrintInvoice?invoiceLanguage='+invoiceLanguage+'&invoiceTemplate='+invoiceTemplate);",
        "class"   => "btn-primary mb-2 w-100",
      ])->render();

      $btnRefreshInvoiceWindow = $this->adios->ui->button([
        "text"    => $this->translate("Refresh window"),
        "onclick" => "
          let tmp_window_id = $(this).closest('.adios.ui.Window').attr('id');
          // refresh invoice window
          window_refresh(tmp_window_id);
        ",
        "class"   => "btn-primary mb-2 w-100",
      ])->render();

      $btnSelectLanguageHtml = $this->adios->ui->Input([
        "type"    => "varchar",
        "enum_values" => $this->enumInvoiceLanguages,
        "uid" => $params["uid"]."_invoiceLanguage",
        "class"   => "mb-2 w-100",
      ])->render();

      $btnSelectTemplateHtml = $this->adios->ui->Input([
        "type"    => "varchar",
        "enum_values" => $this->enumInvoiceTemplates,
        "uid" => $params["uid"]."_enumInvoiceTemplates",
        "class"   => "mb-2 w-100",
      ])->render();

      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-9 pl-0",
            "tabs" => [
              $this->translate("General") => [
                "number",
                "variable_symbol",
                "constant_symbol",
                "specific_symbol",
                ["html" => "<hr>"],
                "delivery_time",
                "issue_time",
                "payment_due_time",
                "datum_uhrady",
                ["html" => "<hr>"],
                "payment_method",
                "order_number",
                "notes",
                "state",
              ],
              $this->translate("Items") => [
                "action" => "UI/Table",
                "params" => [
                  "model"      => "Widgets/Finances/Models/InvoiceItem",
                  "id_invoice" => (int) $data['id'],
                ]
              ],
              $this->translate("Customer") => [
                "customer_name",
                "customer_street_1",
                "customer_street_2",
                "customer_city",
                "customer_zip",
                "customer_country",
                "customer_company_id",
                "customer_company_tax_id",
                "customer_company_vat_id",
                "customer_email",
                "customer_phone",
                "customer_www",
                "customer_iban",
              ],
              $this->translate("Supplier") => [
                "supplier_name",
                "supplier_street_1",
                "supplier_street_2",
                "supplier_city",
                "supplier_zip",
                "supplier_country",
                "supplier_company_id",
                "supplier_company_tax_id",
                "supplier_company_vat_id",
                "supplier_email",
                "supplier_phone",
                "supplier_www",
                "supplier_iban",
              ],
              // "Úhrady" => [
              //   "action" => "Finances/Invoices/TabUhrady",
              //   "params" => [
              //     "id_invoice" => $data['id'],
              //   ]
              // ],
            ],
          ],
          [
            "class" => "col-md-3 pr-0",
            "html" => "
              {$btnRefreshInvoiceWindow}
              <div class='card shadow mb-2'>
                <div class='card-header py-3'>
                  ".$this->translate('Invoice summary')."
                </div>
                <div class='card-body'>
                  <div class='table-responsive'>
                    <table class='table' width='100%' cellspacing='0'>
                      <tbody>
                        <tr>
                          <td>
                            ".$this->translate('Customer')."
                          </td>
                          <td>
                            ".hsc($data['customer_name'])."</br>
                            ".hsc($data['customer_street_1'])."</br>
                            ".hsc($data['customer_zip'])." ".hsc($data['customer_city'])."</br>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            ".$this->translate('Total price excl. VAT')."
                          </td>
                          <td class='text-right'>
                            ".number_format($data['SUMMARY']['price_total_excl_vat'], 2, ",", " ")."
                            ".$this->adios->locale->currencySymbol()."
                          </td>
                        </tr>
                        <tr>
                          <td>
                            ".$this->translate('Total price incl. VAT')."
                          </td>
                          <td class='text-right'>
                            ".number_format($data['SUMMARY']['price_total_incl_vat'], 2, ",", " ")."
                            ".$this->adios->locale->currencySymbol()."
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class='card shadow mb-2'>
                <div class='card-header py-3'>
                  ".$this->translate('Print invoice')."
                </div>
                <div class='card-body'>
                  {$btnSelectLanguageHtml}
                  {$btnSelectTemplateHtml}
                  {$btnPrintInvoiceHtml}
                </div>
              </div>
            ",
          ],
        ],
      ];

    }

    return $params;
  }



  public function issueInvoice($data) {
    // $data = [
    //   "HEADER" => [
    //     "variable_symbol", "constant_symbol", "specific_symbol"
    //     "payment_method", "order_number", "notes",
    //   ],
    //   "TIMESTAMPS" => [
    //     "issue_time", "delivery_time", "payment_due_time"
    //   ],
    //   "CUSTOMER" => [
    //     "id", "name", "ulica_1", "ulica_2", "mesto", "psc", "stat",
    //     "company_id", "company_tax_id", "company_vat_id",
    //     "email", "phone_number",
    //     "cislo_uctu", "cislo_uctu_iban",
    //   ],
    //   "SUPPLIER" => [
    //     "name", "ulica_1", "ulica_2", "mesto", "psc", "stat",
    //     "company_id", "company_tax_id", "company_vat_id",
    //     "email", "phone_number",
    //     "cislo_uctu", "cislo_uctu_iban",
    //   ],
    //   "ITEMS" => [
    //     "item", "unit_price", "quantity", "merna_jednotka", "dph"
    //   ],
    // ];

    $invoiceItemModel = new \ADIOS\Widgets\Finances\Models\InvoiceItem($this->adios);
    $numberingPattern = "ymd";

    $issueTime = (empty($data["TIMESTAMPS"]["issue_time"])
      ? date("Y-m-d H:i:s")
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["issue_time"]))
    );

    $deliveryTime = (empty($data["TIMESTAMPS"]["delivery_time"])
      ? date("Y-m-d H:i:s", strtotime($issueTime))
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["delivery_time"]))
    );

    $paymentDueTime = (empty($data["TIMESTAMPS"]["payment_due_time"])
      ? date("Y-m-d H:i:s")
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["payment_due_time"], "+14 days"))
    );

    $idInvoice = $this->insertRow([
      "accounting_year" => ["sql" => "year('{$issueTime}')"],
      "serial_number" => ["sql" => "
        @serial_number := (ifnull(
          (
            select
              ifnull(max(`i`.`serial_number`), 0)
            from `{$this->table}` `i`
            where year(`i`.`issue_time`) = year('{$issueTime}')
          ),
          0
        ) + 1)
      "],
      "number" => ["sql" => "
        @number := concat('".date($numberingPattern, strtotime($issueTime))."', lpad(@serial_number, 4, '0'))
      "],
      "state" => self::STATE_ISSUED,
      "variable_symbol" => (empty($data["HEADER"]["variable_symbol"])
        ? ["sql" => "@number"]
        : $data["HEADER"]["variable_symbol"]
      ),
      "specific_symbol" => $data["HEADER"]["specific_symbol"],
      "constant_symbol" => $data["HEADER"]["constant_symbol"],

      "issue_time" => $issueTime,
      "delivery_time" => $deliveryTime,
      "payment_due_time" => $paymentDueTime,

      "id_order" => (int) $data["HEADER"]["id_order"],
      "id_customer" => (int) $data["CUSTOMER"]["id"],

      "customer_name" => $data["CUSTOMER"]["name"],
      "customer_street_1" => $data["CUSTOMER"]["street_1"],
      "customer_street_2" => $data["CUSTOMER"]["street_2"],
      "customer_city" => $data["CUSTOMER"]["city"],
      "customer_zip" => $data["CUSTOMER"]["zip"],
      "customer_country" => $data["CUSTOMER"]["country"],
      "customer_company_id" => $data["CUSTOMER"]["company_id"],
      "customer_company_tax_id" => $data["CUSTOMER"]["company_tax_id"],
      "customer_company_vat_id" => $data["CUSTOMER"]["company_vat_id"],
      "customer_email" => $data["CUSTOMER"]["email"],
      "customer_phone" => $data["CUSTOMER"]["phone_number"],
      "customer_iban" => $data["CUSTOMER"]["iban"],

      "supplier_name" => $data["SUPPLIER"]["name"],
      "supplier_street_1" => $data["SUPPLIER"]["ulica_1"],
      "supplier_street_2" => $data["SUPPLIER"]["ulica_2"],
      "supplier_city" => $data["SUPPLIER"]["mesto"],
      "supplier_zip" => $data["SUPPLIER"]["psc"],
      "supplier_country" => $data["SUPPLIER"]["stat"],
      "supplier_company_id" => $data["SUPPLIER"]["company_id"],
      "supplier_company_tax_id" => $data["SUPPLIER"]["company_tax_id"],
      "supplier_company_vat_id" => $data["SUPPLIER"]["company_vat_id"],
      "supplier_email" => $data["SUPPLIER"]["email"],
      "supplier_phone" => $data["SUPPLIER"]["phone_number"],
      "supplier_iban" => $data["SUPPLIER"]["cislo_uctu"],

      "payment_method" => $data["HEADER"]["payment_method"],
      "order_number" => $data["HEADER"]["order_number"],
      "notes" => $data["HEADER"]["notes"],
    ]);

    if (is_array($data["ITEMS"])) {
      foreach ($data["ITEMS"] as $item) {
        $invoiceItemModel->insertRow([
          "id_invoice" => $idInvoice,
          "item" => $item['item'],
          "quantity" => $item['quantity'],
          "id_delivery_unit" => $item['id_delivery_unit'],
          "unit_price" => $item['unit_price'],
          "vat_percent" => $item['vat_percent'],
        ]);
      }
    }

    $issuedInvoiceData = $this->getById($idInvoice);

    $summary = $this->calculateSummaryInfo($issuedInvoiceData);
    $this->updateSummaryInfo($idInvoice, $summary);

    $this->sendNotificationForIssuedInvoice($issuedInvoiceData);

    return $this->getById($idInvoice);
  }

  public function sendNotificationForIssuedInvoice($invoiceData) {

    if ($this->disableNotifications) return;

    $domain = $invoiceData["ORDER"]["domain"] ?? "";

    $subject = $this->adios->config["settings"]["web"][$domain]["emails"]['after_regular_invoice_issue_SUBJECT'];
    $body = $this->adios->config["settings"]["web"][$domain]["emails"]['after_regular_invoice_issue_BODY'];
    $signature = $this->adios->config["settings"]["web"][$domain]["emails"]['signature'];

    $invoiceHtml = print_r($invoiceData, TRUE);

    $this->adios->sendEmail([
      "to" => $invoiceData['customer_email'],
      "subject" => str_replace("{#}", $invoiceData['number'], $subject),
      "bodyHtml" => "
        <div style='font-family:Verdana;font-size:10pt'>
          ".str_replace("{#}", $invoiceHtml, $body)."
        </div>
        <div style='font-family:Verdana;font-size:10pt;padding-top:10px;margin-top:10px;border-top:1px solid #AAAAAA'>{$signature}</div>
      "
    ]);
  }

}