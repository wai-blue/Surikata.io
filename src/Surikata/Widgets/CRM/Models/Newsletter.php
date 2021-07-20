<?php

namespace ADIOS\Widgets\CRM\Models;

class Newsletter extends \ADIOS\Core\Model {
  var $sqlName = "newsletter";
  var $urlBase = "CRM/Newsletter";
  var $tableTitle = "Prihlásení do newsletteru";
  var $formTitleForEditing = "Prihlásený do newsletteru";
  var $formTitleForInserting = "Prihlásený do newsletteru";

  public function columns(array $columns = []) {
    return parent::columns([
      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Email"),
        "show_column" => TRUE,
      ],

      "pridany" => [
        "type" => "datetime",
        "title" => $this->translate("Prijaté"),
        "show_column" => TRUE,
      ],
    ]);
  }

  // public function install() {
  //   if (!parent::install()) return FALSE;

  //   for ($i = 0; $i < 30; $i++) {
  //     $this->insertRandomRow();
  //   }

  //   return TRUE;
  // }
}