<?php

namespace ADIOS\Actions\Website;

class Emails extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["emails"][$this->params['domain']];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "emails/{$this->params['domain']}",
      "title" => "Website - {$this->params['domain']} - Emails",
      "template" => [
        "tabs" => [
          [
            "title" => "General",
            "items" => [
              [
                "title" => "Signature",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_signature",
                  "value" => $settings['signature'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Signature will be appended to the bottom of each email.",
              ],
            ],
          ],
          [
            "title" => "Customer accounts",
            "items" => [
              [
                "title" => "After creation of customer's account (after registration) - subject",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_registration_SUBJECT",
                  "value" => $settings['after_registration_SUBJECT'],
                ]),
                "description" => "
                  Available email variables:</br>
                  {% email %}
                "
              ],
              [
                "title" => "After creation of customer's account (after registration) - body",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_registration_BODY",
                  "value" => $settings['after_registration_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "
                  Email will be sent when a visitor creates the account.</br>
                  Available email variables:</br>
                  {% givenName %}, {% familyName %}, {% password %}, {% validationUrl %} 
                ",
              ],
              [
                "title" => "After verification of customer's account - subject",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_po_overeni_uctu_PREDMET",
                  "value" => $settings['po_overeni_uctu_PREDMET'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Email will be sent when a visitor verifies his account.",
              ],
              [
                "title" => "After verification of customer's account - body",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_po_overeni_uctu_TEXT",
                  "value" => $settings['po_overeni_uctu_TEXT'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Email will be sent when a visitor verifies his account.",
              ],
              [
                "title" => "Forgotten password - SUBJECT",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_forgotten_password_SUBJECT",
                  "value" => $settings['forgotten_password_SUBJECT'],
                ]),
              ],
              [
                "title" => "Forgotten password - BODY",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_forgotten_password_BODY",
                  "value" => $settings['forgotten_password_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Mail sa pošle, keď zákazník vyplní formulár so žiadosťou o obnovenie hesla.",
              ],
            ],
          ],
          [
            "title" => "Orders",
            "items" => [
              [
                "title" => "After order confirmation - SUBJECT",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_order_confirmation_SUBJECT",
                  "value" => $settings['after_order_confirmation_SUBJECT'],
                ]),
                "description" => "
                  Available email variables:</br>
                  {% number %}
                "
              ],
              [
                "title" => "After order confirmation - BODY",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_confirmation_BODY",
                  "value" => $settings['after_order_confirmation_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "
                  Mail will be sent when customer confirms the order.</br>
                  Available email variables:</br>
                  {% serialNumber %}, {% number %}, {% numberCustomer %}, </br>
                  {% notes %}, {% state %}, {% phoneNumber %}, {% email %}, </br>
                  {% delGivenName %}, {% delFamilyName %}, {% delCompanyName %}, {% delStreet1 %}, {% delStreet2 %}, </br>
                  {% delFloor %}, {% delCity %}, {% delZip %}, {% delRegion %}, {% delCountry %}, </br> 
                  {% invGivenName %}, {% invFamilyName %}, {% invCompanyName %}, {% invStreet1 %}, {% invStreet2 %}, </br> 
                  {% invFloor %}, {% invCity %}, {% invZip %}, {% invRegion %}, {% invCountry %}, {% confirmationTime %}, </br> 
                  {% deliveryService %}, {% requiredDeliveryTime %}, {% deliveryPrice %}, {% dodacieInfo %} 
                ",
              ],
              [
                "title" => "After order payment",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_payment",
                  "value" => $settings['after_order_payment'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Mail will be sent when the customer pays the order.",
              ],
              [
                "title" => "After order shipping",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_shipping",
                  "value" => $settings['after_order_shipping'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Mail will be sent when the order will be shipped out.",
              ],
            ],
          ],
          [
            "title" => "Invoices",
            "items" => [
              [
                "title" => "After advance invoice issue - SUBJECT",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_advance_invoice_issue_SUBJECT",
                  "value" => $settings['after_advance_invoice_issue_SUBJECT'],
                ]),
              ],
              [
                "title" => "After advance invoice issue - BODY",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_advance_invoice_issue_BODY",
                  "value" => $settings['after_advance_invoice_issue_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Mail will be sent when the advance invoice will be issued.",
              ],
              [
                "title" => "After regular invoice issue - SUBJECT",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_regular_invoice_issue_SUJBECT",
                  "value" => $settings['after_regular_invoice_issue_SUJBECT'],
                ]),
              ],
              [
                "title" => "After regular invoice issue - BODY",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_regular_invoice_issue_BODY",
                  "value" => $settings['after_regular_invoice_issue_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "Mail will be sent when the regular invoice will be issued.",
              ],
            ],
          ],
          [
            "title" => "Odosielací účet SMTP",
            "items" => [
              [
                "title" => "SMTP host",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_host",
                  "value" => $settings['smtp_host'],
                ]),
                "description" => "Príklad: mail.mojadomena.sk",
              ],
              [
                "title" => "SMTP port",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_port",
                  "value" => $settings['smtp_port'],
                ]),
                "description" => "
                  Príklad: 465
                  <div style='color:red'>
                    UPOZORNENIE: Pre odosielanie pošty je podporované iba zabezpečené pripojenie TLS alebo SSL.
                  </div>
                ",
              ],
              [
                "title" => "SMTP protocol",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_protocol",
                  "value" => $settings['smtp_protocol'],
                  "enum_values" => [
                    "tls" => "TLS",
                    "ssl" => "SSL",
                  ]
                ]),
              ],
              [
                "title" => "SMTP username",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_username",
                  "value" => $settings['smtp_username'],
                ]),
                "description" => "Príklad: info@mojadomena.sk",
              ],
              [
                "title" => "SMTP password",
                "input" => $this->adios->ui->Input([
                  "type" => "password",
                  "uid" => "{$this->uid}_smtp_password",
                  "value" => $settings['smtp_password'],
                ]),
              ],
              [
                "title" => "Sender address",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sender_address",
                  "value" => $settings['sender_address'],
                ]),
                "description" => "Mala by byť totožná s adresou SMTP.",
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}