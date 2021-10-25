<?php

namespace ADIOS\Actions\Website;

class Emails extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["emails"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/emails",
      "title" => "{$this->params['domainName']} » ".$this->translate("Emails"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("General"),
            "items" => [
              [
                "title" => $this->translate("Signature"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_signature",
                  "value" => $settings['signature'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Signature will be appended to the bottom of each email."),
              ],
            ],
          ],
          [
            "title" => $this->translate("Customer accounts"),
            "items" => [
              [
                "title" => $this->translate("After creation of customer's account (after registration) - subject"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_registration_SUBJECT",
                  "value" => $settings['after_registration_SUBJECT'],
                ]),
                "description" => "
                  ".$this->translate("Available email variables").":</br>
                  {% email %}
                "
              ],
              [
                "title" => $this->translate("After creation of customer's account (after registration) - body"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_registration_BODY",
                  "value" => $settings['after_registration_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "
                  ".$this->translate("Email will be sent when a visitor creates the account.")."</br>
                  ".$this->translate("Available email variables").":</br>
                  {% givenName %}, {% familyName %}, {% password %}, {% validationUrl %} 
                ",
              ],
              [
                "title" => $this->translate("After verification of customer's account - subject"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_po_overeni_uctu_PREDMET",
                  "value" => $settings['po_overeni_uctu_PREDMET'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Email will be sent when a visitor verifies his account."),
              ],
              [
                "title" => $this->translate("After verification of customer's account - body"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_po_overeni_uctu_TEXT",
                  "value" => $settings['po_overeni_uctu_TEXT'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Email will be sent when a visitor verifies his account."),
              ],
              [
                "title" => $this->translate("Forgotten password - SUBJECT"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_forgotten_password_SUBJECT",
                  "value" => $settings['forgotten_password_SUBJECT'],
                ]),
              ],
              [
                "title" => $this->translate("Forgotten password - BODY"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_forgotten_password_BODY",
                  "value" => $settings['forgotten_password_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Mail will be sent when a customer fills out a password reset form."),
              ],
            ],
          ],
          [
            "title" => $this->translate("Orders"),
            "items" => [
              [
                "title" => $this->translate("After order confirmation - SUBJECT"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_order_confirmation_SUBJECT",
                  "value" => $settings['after_order_confirmation_SUBJECT'],
                ]),
                "description" => "
                  ".$this->translate("Available email variables").":</br>
                  {% number %}
                "
              ],
              [
                "title" => $this->translate("After order confirmation - BODY"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_confirmation_BODY",
                  "value" => $settings['after_order_confirmation_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => "
                  ".$this->translate("Mail will be sent when customer confirms the order.")."</br>
                  ".$this->translate("Available email variables").":</br>
                  {% serialNumber %}, {% number %}, {% numberCustomer %}, </br>
                  {% notes %}, {% state %}, {% phoneNumber %}, {% email %}, </br>
                  {% delGivenName %}, {% delFamilyName %}, {% delCompanyName %}, {% delStreet1 %}, {% delStreet2 %}, </br>
                  {% delFloor %}, {% delCity %}, {% delZip %}, {% delRegion %}, {% delCountry %}, </br> 
                  {% invGivenName %}, {% invFamilyName %}, {% invCompanyName %}, {% companyId %}, {% companyTaxId %}, {% companyVatId %}, </br>
                  {% invStreet1 %}, {% invStreet2 %}, {% invFloor %}, {% invCity %}, {% invZip %}, {% invRegion %}, {% invCountry %}, {% confirmationTime %}, </br> 
                  {% deliveryService %}, {% requiredDeliveryTime %}, {% deliveryPrice %}, {% dodacieInfo %} 
                ",
              ],
              [
                "title" => $this->translate("After order payment"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_payment",
                  "value" => $settings['after_order_payment'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Mail will be sent when the customer pays the order."),
              ],
              [
                "title" => $this->translate("After order shipping"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_order_shipping",
                  "value" => $settings['after_order_shipping'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Mail will be sent when the order will be shipped out."),
              ],
            ],
          ],
          [
            "title" => $this->translate("Invoices"),
            "items" => [
              [
                "title" => $this->translate("After advance invoice issue - SUBJECT"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_advance_invoice_issue_SUBJECT",
                  "value" => $settings['after_advance_invoice_issue_SUBJECT'],
                ]),
              ],
              [
                "title" => $this->translate("After advance invoice issue - BODY"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_advance_invoice_issue_BODY",
                  "value" => $settings['after_advance_invoice_issue_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Mail will be sent when the advance invoice will be issued."),
              ],
              [
                "title" => $this->translate("After regular invoice issue - SUBJECT"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_after_regular_invoice_issue_SUJBECT",
                  "value" => $settings['after_regular_invoice_issue_SUJBECT'],
                ]),
              ],
              [
                "title" => $this->translate("After regular invoice issue - BODY"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_after_regular_invoice_issue_BODY",
                  "value" => $settings['after_regular_invoice_issue_BODY'],
                  "interface" => "formatted_text",
                ]),
                "description" => $this->translate("Mail will be sent when the regular invoice will be issued."),
              ],
            ],
          ],
          [
            "title" => $this->translate("SMTP sending account"),
            "items" => [
              [
                "title" => "SMTP host",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_host",
                  "value" => $settings['smtp_host'],
                ]),
                "description" => $this->translate("Example: mail.mydomain.en"),
              ],
              [
                "title" => $this->translate("SMTP port"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_port",
                  "value" => $settings['smtp_port'],
                ]),
                "description" => "
                  ".$this->translate("Example: 465")."
                  <div style='color:red'>
                    ".$this->translate("WARNING: Only secure TLS or SSL connections are supported for sending mail.")."
                  </div>
                ",
              ],
              [
                "title" => $this->translate("SMTP protocol"),
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
                "title" => $this->translate("SMTP username"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_smtp_username",
                  "value" => $settings['smtp_username'],
                ]),
                "description" => $this->translate("Example: info@mydomain.en"),
              ],
              [
                "title" => $this->translate("SMTP password"),
                "input" => $this->adios->ui->Input([
                  "type" => "password",
                  "uid" => "{$this->uid}_smtp_password",
                  "value" => $settings['smtp_password'],
                ]),
              ],
              [
                "title" => $this->translate("Sender address"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sender_address",
                  "value" => $settings['sender_address'],
                ]),
                "description" => $this->translate("It should be the same as the SMTP address."),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}