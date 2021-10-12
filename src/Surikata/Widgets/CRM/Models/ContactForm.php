<?php

namespace ADIOS\Widgets\CRM\Models;

class ContactForm extends \ADIOS\Core\Model {
  var $sqlName = "contact_form";
  var $urlBase = "CRM/ContactForm";

  public function init() {

    $this->tableTitle = $this->translate("Messages from contact form");
    $this->formTitleForInserting = $this->translate("Message from contact form");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Email"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],
      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Name"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "phone_number" => [
        "type" => "varchar",
        "title" => $this->translate("Phone number"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "message" => [
        "type" => "text",
        "title" => $this->translate("Message"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "received" => [
        "type" => "datetime",
        "title" => $this->translate("Received"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "recipient" => [
        "type" => "varchar",
        "title" => $this->translate("Recipient"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params['show_add_button'] = FALSE;
    $params['show_search_button'] = FALSE;

    return $params;
  }

}