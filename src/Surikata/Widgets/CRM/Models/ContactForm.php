<?php

namespace ADIOS\Widgets\CRM\Models;

class ContactForm extends \ADIOS\Core\Model {
  var $sqlName = "contact_form";
  var $urlBase = "CRM/ContactForm";
  var $tableTitle = "Messages from contact form";
  var $formTitleForEditing = "Message from contact form";

  public function columns(array $columns = []) {
    return parent::columns([
      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Email"),
        "show_column" => TRUE,
      ],

      "phone_number" => [
        "type" => "varchar",
        "title" => $this->translate("Phone number"),
        "show_column" => TRUE,
      ],

      "message" => [
        "type" => "text",
        "title" => $this->translate("Message"),
        "show_column" => TRUE,
      ],

      "received" => [
        "type" => "datetime",
        "title" => $this->translate("Received"),
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