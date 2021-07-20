<?php

namespace ADIOS\Widgets\Orders\Models;

define('CLAIM_STATE_OPEN',   1);
define('CLAIM_STATE_CLOSED', 2);

class Claim extends \ADIOS\Core\Model {
  var $sqlName = "claims";
  var $urlBase = "Claims";
  var $tableTitle = "Claims";
  var $formTitleForInserting = "New claim";
  var $formTitleForEditing = "Claim";
  
  public function init() {
    $this->enumClaimStates = [
      CLAIM_STATE_OPEN   => 'Open',
      CLAIM_STATE_CLOSED => 'Closed',
    ];

    $this->enumClaimStateColors = [
      CLAIM_STATE_OPEN   => 'blue',
      CLAIM_STATE_CLOSED => 'green',
    ];
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_order" => [
        "type" => "lookup",
        "title" => "Order",
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => TRUE,
      ],

      "cas" => [
        "type" => "date",
        "title" => "Date",
        "show_column" => true
      ],

      "notes" => [
        "type" => "varchar",
        "title" => "Notes",
        "show_column" => TRUE,
      ],

      "state" => [
        "type" => "int",
        "enum_values" => $this->enumClaimStates,
        "title" => "State",
        "show_column" => true
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Claims\/([Otvorene|Uzavrete]+)$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Orders/Models/Claim",
          "filter_type" => '$1',
        ]
      ],
    ]);
  }

  public function tableParams($params) {
    switch ($params['filter_type']) {
      case "Vsetky":
        $params["title"] = "Claims &raquo; All";
      break;
      case "Otvorene":
        $params["title"] = "Claims &raquo; Open";
        $params['where'] = "{$this->table}.state in (".CLAIM_STATE_OPEN.")";
      break;
      case "Uzavrete":
        $params["title"] = "Claims &raquo; Closed";
        $params['where'] = "not {$this->table}.state in (".CLAIM_STATE_OPEN.")";
      break;
    }
    
    return $params;
  }

}