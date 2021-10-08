<?php

namespace ADIOS\Widgets\Products\Models;

class ProductFeatureAssignment extends \ADIOS\Core\Model {
  var $sqlName = "products_features_assignment";
  var $urlBase = "Produkty/{{ id_product }}/Features";
  var $tableTitle = "Product features";

  public static $allItemsCache = NULL;

  public function init() {
    $this->formTitleForInserting = $this->translate("New product feature");
    $this->formTitleForEditing = $this->translate("Product feature");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => $this->translate("Product"),
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_feature" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductFeature",
        "title" => $this->translate("Feature"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "value_text" => [
        'type' => 'text',
        'title' => $this->translate('Value: Text'),
        'show_column' => TRUE,
      ],

      "value_number" => [
        'type' => 'float',
        'title' => $this->translate('Value: Number'),
        'show_column' => TRUE,
      ],

      "value_boolean" => [
        'type' => 'boolean',
        'title' => $this->translate('Value: Yes/No'),
        'show_column' => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "id_product___id_feature" => [
        "type" => "unique",
        "columns" => ["id_product", "id_feature"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => $params['id_product']];
    if ($data['id'] <= 0) {
      $params["template"] = [
        "columns" => [
          [
            "rows" => ["id_product", "id_feature"]
          ],
        ]
      ];
    } else {
      $featuresModel = new ProductFeature();
      if (isset($data['id_feature'])) {
        $params['columns']['id_feature']['disabled'] = TRUE;
        $feature = $featuresModel->getById($data["id_feature"]);
        $params['onload'] = "
          var featureType = Number({$feature["value_type"]}) - 1;
          let featureInputs = [
            uid + '_value_number',
            uid + '_value_text',
            uid + '_value_boolean'
          ];
          for (let i = 0; i < featureInputs.length; i++) {
            if (featureType !== i) {
              if (i == 0) {
                $('#'+featureInputs[i]).parent().parent().parent().css('display', 'none');
              }
              else {
                $('#'+featureInputs[i]).parent().parent().css('display', 'none');
              }
            }
          }  
        ";
      }

      $featuresAll = $featuresModel->all();
      $featuresVar = "var features = [";
      foreach ($featuresAll as $feature) {
        $featuresVar .= "[". $feature->attributes["id"]. ",". $feature->attributes["value_type"]. ",". $feature->attributes["entry_method"]."],";
      }
      $featuresVar .= "];";
      $params['columns']['id_feature']['onchange'] = "
        {$featuresVar}
        var id = $(this).attr('id');
        var uid = id.substring(0, id.indexOf('_id_feature'));
        var featureId = $(this).val();
        var valueType = 0;
        var entryMethod = 0;
        features.forEach(function(item, index) {
          if (item[0] == featureId) {
            valueType = item[1];    // Show value type
            entryMethod = item[2];
          }
        });
        let featureInputs = [
          uid + '_value_number',
          uid + '_value_text',
          uid + '_value_boolean'
        ];
        for (let i = 0; i < featureInputs.length; i++) {
          // Show all
          if (i == 0) {
            $('#'+featureInputs[i]).parent().parent().parent().css('display', 'block');
          }
          else {
            $('#'+featureInputs[i]).parent().parent().css('display', 'block');
          }
          // Hide all except the selected one
          if ((valueType - 1) != i) {
            console.log(i, valueType);
            if (i == 0) {
              $('#'+featureInputs[i]).parent().parent().parent().css('display', 'none');
            }
            else {
              $('#'+featureInputs[i]).parent().parent().css('display', 'none');
            }
          }
        }
      ";
    }

    return $params;
  }

}