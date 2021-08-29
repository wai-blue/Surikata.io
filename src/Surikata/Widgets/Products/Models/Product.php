<?php

namespace ADIOS\Widgets\Products\Models;

class Product extends \ADIOS\Core\Model {
  const SALE_PRICE_CALCULATION_METHOD_BASE       = 1;
  const SALE_PRICE_CALCULATION_METHOD_PRICE_LIST = 2;
  const SALE_PRICE_CALCULATION_METHOD_PLUGIN     = 3;
  
  var $sqlName = "products";
  var $lookupSqlValue = "concat({%TABLE%}.number, ' ', {%TABLE%}.name_lang_1)";
  var $urlBase = "Products";

  public function init() {
    $this->languageDictionary["sk"] = [
      //
    ];

    $this->tableTitle = $this->translate("Products");

    $this->enumValuesSalePriceCalculationMethod = [
      self::SALE_PRICE_CALCULATION_METHOD_BASE => "Base: Base sale price will be used",
      self::SALE_PRICE_CALCULATION_METHOD_PRICE_LIST => "Price list: Margins and discounts from price list will be applied",
      self::SALE_PRICE_CALCULATION_METHOD_PLUGIN => "Plugin: Plugin will be called to calculate price",
    ];
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." ({$languageName})",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["brief_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Short description")." ({$languageName})",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["description_lang_{$languageIndex}"] = [
        "type" => "text",
        "title" => $this->translate("Description")." ({$languageName})",
        "interface" => "formatted_text",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["gift_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Gift")." ({$languageName})",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    return parent::columns(array_merge(
      $translatedColumns,
      [
        "number" => [
          "type" => "varchar",
          "title" => $this->translate("Product number"),
          "required" => TRUE,
          "show_column" => TRUE,
        ],

        "ean" => [
          "type" => "varchar",
          "title" => $this->translate("EAN"),
        ],

        "weight" => [
          "type" => "float",
          "title" => $this->translate("Weight (per unit)"),
          "unit" => "kg",
          "show_column" => FALSE,
        ],

        "sale_price_calculation_method" => [
          "type" => "int",
          "title" => $this->translate("Sale price - Method for calculation"),
          "enum_values" => $this->enumValuesSalePriceCalculationMethod,
        ],

        "sale_price" => [
          "type" => "float",
          "sql_data_type" => "decimal",
          "decimals" => 4,
          "title" => $this->translate("Base sale price"),
          "unit" => $this->adios->locale->currencySymbol(),
          "show_column" => FALSE,
        ],

        "sale_price_calculated" => [
          "type" => "float",
          "title" => $this->translate("Calculated sale price"),
          "description" => "WARNING: Final sale price of the product will be updated after save.",
          "readonly" => TRUE,
          "show_column" => FALSE,
        ],

        "id_delivery_unit" => [
          "type" => "lookup",
          "input_style" => "select",
          "title" => $this->translate("Delivery unit"),
          "model" => "Widgets/Settings/Models/Unit",
        ],

        "stock_quantity" => [
          "type" => "float",
          "title" => "Stock quantity",
          "Description" => "Number of units of product available on stock and ready for immediate delivery.",
          "show_column" => TRUE,
        ],

        "delivery_day" => [
          "type" => "int",
          "title" => "Delivery day",
          "Description" => "Duration of delivery in days. '1' means that the product will be delivered in the next day after the order.",
        ],

        "delivery_time" => [
          "type" => "time",
          "title" => "Delivery time",
          "Description" => "Date and time of the delivery if the product is ordered before the order deadline. Leave empty for default value.",
        ],

        "order_deadline" => [
          "type" => "time",
          "title" => "Order deadline",
          "Description" => "Date and time of the delivery if the product is ordered before the order deadline. Leave empty for default value.",
        ],

        "extended_warranty" => [
          "type" => "int",
          "title" => "Extended warranty",
          "unit" => "month(s)",
        ],

        "vat_percent" => [
          "type" => "int",
          "title" => "VAT",
          "unit" => "%",
          "show_column" => TRUE,
        ],

        "id_category" => [
          "type" => "lookup",
          "model" => "Widgets/Products/Models/ProductCategory",
          "title" => "Main product category",
          "show_column" => TRUE,
        ],

        "id_brand" => [
          "type" => "lookup",
          "model" => "Widgets/Products/Models/Brand",
          "title" => $this->translate("Brand"),
          "show_column" => TRUE,
        ],

        "image" => [
          'type' => 'image',
          'title' => $this->translate("Main image"),
          'show_column' => TRUE,
          "subdir" => "products"
        ],

        "product_info" => [
          "type" => "file",
          "subdir" => "produkty/info",
          "title" => $this->translate("Product information PDF"),
        ],

        "is_on_sale" => [
          "type" => "boolean",
          "title" => $this->translate("On sale product"),
          "show_column" => TRUE,
        ],

        "is_sale_out" => [
          "type" => "boolean",
          "title" => $this->translate("Sale out"),
          "show_column" => TRUE,
        ],

        "is_new" => [
          "type" => "boolean",
          "title" => $this->translate("New product"),
          "show_column" => TRUE,
        ],

        "is_recommended" => [
          "type" => "boolean",
          "title" => $this->translate("Recommended product"),
          "show_column" => TRUE,
        ],

        "is_top" => [
          "type" => "boolean",
          "title" => $this->translate("Top product"),
          "show_column" => TRUE,
        ],

        "is_used" => [
          "type" => "boolean",
          "title" => $this->translate("Used product"),
          "show_column" => TRUE,
        ],

        "is_unpacked" => [
          "type" => "boolean",
          "title" => $this->translate("Unpacked product"),
          "show_column" => TRUE,
        ],

        "currently_viewed" => [
          "type" => "int",
          "title" => $this->translate("Currently viewed"),
        ],

        "count_sold" => [
          "type" => "int",
          "title" => $this->translate("Count of sold products"),
        ],

        "last_buy" => [
          "type" => "datetime",
          "title" => $this->translate("Time of last buy"),
        ],

        "end_of_sale" => [
          "type" => "date",
          "title" => $this->translate("Time of end of sale"),
        ],

        "order_index" => [
          "type" => "int",
          "title" => $this->translate("Order index"),
        ],

        "is_hidden" => [
          "type" => "boolean",
          "title" => $this->translate("Hide from list"),
          "show_column" => TRUE,
        ],

      ]
    ));
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "number" => [
        "type" => "unique",
        "columns" => ["number"],
      ],
    ]);
  }

  public function upgrades() : array {
    return [
      0 => [], // upgrade to version 0 is the same as installation
      1 => [
        "alter table `{$this->gtp}_products` add column `order_index` int(8) default 0 after `end_of_sale`",
      ],
      2 => [
        "alter table `{$this->gtp}_products` add column `is_hidden` boolean after `order_index`",
      ],
    ];
  }

  public function unit() {
    return $this->hasOne(\ADIOS\Widgets\Settings\Models\Unit::class, "id", "id_delivery_unit");
  }

  // Eloquent relationships
  public function gallery() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductGallery::class, 'id_product');
  }

  public function extensions() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductExtension::class, 'id_product');
  }

  public function nakupnaCena() {
    return $this->hasOne(\ADIOS\Widgets\Prices\Models\ProductPrice::class, 'id_product');
  }

  public function productDiscounts() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductDiscount::class, 'id_product');
  }

  public function productCategoryDiscounts() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductDiscount::class, 'id_product_category', 'id_category');
  }

  public function productBrandDiscounts() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductDiscount::class, 'id_brand', 'id_brand');
  }

  public function productSupplierDiscounts() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductDiscount::class, 'id_supplier', 'id_supplier');
  }

  public function productMargins() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductMargin::class, 'id_product');
  }

  public function productCategoryMargins() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductMargin::class, 'id_product_category', 'id_category');
  }

  public function productBrandMargins() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductMargin::class, 'id_brand', 'id_brand');
  }

  public function productSupplierMargins() {
    return $this->hasMany(\ADIOS\Widgets\Prices\Models\ProductMargin::class, 'id_supplier', 'id_supplier');
  }

  public function brand() {
    return $this->belongsTo(\ADIOS\Widgets\Products\Models\Brand::class, 'id_brand');
  }

  public function supplier() {
    return $this->belongsTo(\ADIOS\Widgets\Products\Models\Supplier::class, 'id_supplier');
  }

  public function features() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\ProductFeature::class,
        GTP."_products_features_assignment",
        'id_product',
        'id_feature'
      )
      ->withPivot('value_number', 'value_text', 'value_boolean')
    ;
  }

  public function accessories() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Product::class,
        GTP."_products_accessories",
        'id_product',
        'id_accessory'
      )
    ;
  }

  public function related() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Product::class,
        GTP."_products_related",
        'id_product',
        'id_related'
      )
    ;
  }

  public function services() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Service::class,
        GTP."_products_services_assignment",
        'id_product',
        'id_service'
      )
    ;
  }

  // routing
  public function routing(array $routing = []) {
    return parent::routing([
      '/^Products\/(All|New|OnSale|SellOut|Recommended|Used|Unpacked)$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Products/Models/Product",
          "filter_type" => '$1',
        ]
      ],
    ]);
  }

  public function tableParams($params) {
    $params['show_search_button'] = TRUE;

    switch ($params['filter_type']) {
      case "All":
        $params["title"] = $this->translate("All products");
      break;
      case "New":
        $params["title"] = $this->translate("Products - News");
        $params['where'] = $this->getFullTableSQLName().".is_new = 1";
      break;
      case "OnSale":
        $params["title"] = $this->translate("Products - On sale");
        $params['where'] = $this->getFullTableSQLName().".is_on_sale = 1";
      break;
      case "SellOut":
        $params["title"] = $this->translate("Products - Sale out");
        $params['where'] = $this->getFullTableSQLName().".is_sale_out = 1";
      break;
      case "Recommended":
        $params["title"] = $this->translate("Products - Recommended");
        $params['where'] = $this->getFullTableSQLName().".is_recommended = 1";
      break;
      case "Used":
        $params["title"] = $this->translate("Products - Used");
        $params['where'] = $this->getFullTableSQLName().".is_used = 1";
      break;
      case "Unpacked":
        $params["title"] = $this->translate("Products - Unpacked");
        $params['where'] = $this->getFullTableSQLName().".is_unpacked = 1";
      break;
    }
    return $params;
  }

  // $initiatingModel = model formulara, v ramci ktoreho je lookup generovany
  // $initiatingColumn = nazov stlpca, z ktoreho je lookup generovany
  // $formData = aktualne data formulara
  public function lookupSqlWhere($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = []) {
    if (
      $initiatingModel == "Widgets/Products/Models/ProductAccessory"
      && $initiatingColumn == "id_accessory"
    ) {
      return "`{$this->table}`.id != ".(int) $formData['id_product'];
    } else {
      return "TRUE";
    }
  }

  public function formParams($data, $params) {
    if ($data['id'] <= 0) {
      $params['title'] = "New product";
    } else {
      $params['title'] = "{$data['number']} {$data['name_lang_1']}";
      $params['subtitle'] = $this->translate("Product");
    }

    // $params['show_delete_button'] = FALSE;

    // $params["columns"]["sale_price"]["readonly"] = (
    //   $data['sale_price_calculation_method'] != self::SALE_PRICE_CALCULATION_METHOD_BASE
    // );

    $params["columns"]["table_categories_assignment"] = [
      "type" => "table",
      "table" => $this->adios->getModel("Widgets/Products/Models/ProductCategoryAssignment")->table,
      "input_style" => "autocomplete",
      "title" => $this->translate("Zaradenie do vedľajších kategórií"),
      "order" => "name_lang_1 asc"
    ];

    $tabTranslations = [];
    $domainAssignmentValues = [];

    $domains = $this->adios->config['widgets']['Website']['domains'];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $i = 1;
    foreach ($domainLanguages as $languageIndex => $languageName) {
      if ($i > 1) {
        $tabTranslations[] = ["html" => "<b style='color:var(--cl-main)'>".hsc($languageName)."</b>"];
        $tabTranslations[] = "name_lang_{$languageIndex}";
        $tabTranslations[] = "brief_lang_{$languageIndex}";
        $tabTranslations[] = "description_lang_{$languageIndex}";
        $tabTranslations[] = "gift_lang_{$languageIndex}";
      }
      $i++;
    }

    foreach ($domains as $domain => $domainInfo) {
      $domainAssignmentValues[$domain] = $domainInfo["name"];
    }

    if (count($tabTranslations) == 0) {
      $tabTranslations[] = ["html" => "No translations available."];
    }

    $templateTabs = [
      $this->translate("General") => [
        "number",
        "ean",
        "name_lang_1",
        "brief_lang_1",
        "description_lang_1",
        "vat_percent",
        "weight",
        "id_supplier",
        "id_brand",
        "product_info",
        "extended_warranty",
      ],
      $this->translate("Price") => [
        "sale_price_calculation_method",
        "sale_price",
        "sale_price_calculated",
      ],
      $this->translate("Stock & Delivery") => [
        "stock_quantity",
        "id_delivery_unit",
        "delivery_day",
        "delivery_time",
        "order_deadline",
      ],
      $this->translate("Visibility") => [
        [
          "title" => "Domain visibility",
          "description" => "If no domain is selected, product will be visible on all domains.",
          "input" => (new \ADIOS\Core\UI\Input\CheckboxField(
            $this->adios,
            "{$params['uid']}_ProductDomainAssignment",
            [
              "model" => "Widgets/Products/Models/ProductDomainAssignment",
              "key_column" => "id_product",
              "key_value" => $data['id'],
              "value_column" => "domain",
              "values" => $domainAssignmentValues,
              "columns" => 2,
            ]
          ))->render(),
        ],
        "end_of_sale",
        "order_index",
        "is_hidden",
      ],
      $this->translate("Translations") => $tabTranslations,
      "Categories" => [
        "id_category",
        [
          "title" => "Additional categories",
          "input" => (new \ADIOS\Core\UI\Input\ManyToMany(
            $this->adios,
            "{$params['uid']}_ProductCategoryAssignment",
            [
              "model" => "Widgets/Products/Models/ProductCategoryAssignment",
              "relation" => ["id_product", "id_category"],
              "constraints" => [
                "id_product" => $data['id'],
              ],
              "order" => "name_lang_1 asc"
            ]
          ))->render(),
        ],
      ],
      $this->translate("Filters") => [
        "is_on_sale",
        "is_sale_out",
        "is_new",
        "is_recommended",
        "is_top",
        "is_used",
        "is_unpacked",
      ],
    ];

    if ($data['id'] > 0) {
      $templateTabs[$this->translate("Services")] = [
        [
          "title" => "",
          "input" => (new \ADIOS\Core\UI\Input\ManyToMany(
            $this->adios,
            "{$params['uid']}_ProductServiceAssignment",
            [
              "formUid" => $params['uid'],
              "model" => "Widgets/Products/Models/ProductServiceAssignment",
              "relation" => ["id_product", "id_service"],
              "constraints" => [
                "id_product" => $data['id'],
              ],
              "order" => "name_lang_1 asc"
            ]
          ))->render(),
        ],
      ];
      $templateTabs[$this->translate("Gallery")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductGallery",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Features")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductFeatureAssignment",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Extensions")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductExtension",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Accessories")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductAccessory",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Related")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductRelated",
          "id_product" => $data['id'],
        ]
      ];
    }

    $sidebarHtml = $this->adios->dispatchEventToPlugins("onProductDetailSidebarButtons", [
      "model" => $this,
      "params" => $params,
      "data" => $data,
    ])["html"];

    $params["template"] = [
      "columns" => [
        [
          "class" => "col-md-9 pl-0",
          "tabs" => $templateTabs,
        ],
        [
          "class" => "col-md-3 pr-0",
          "rows" => [
            "image",
            ["html" => $sidebarHtml],
          ],
        ],
      ],
    ];

    return parent::formParams($data, $params);
  }

  public function addProductToOrder($productId) {
    $product = $this->getById($productId);
    if ($product["id_delivery_unit"] > 0) {
      $delivery_unit = (new \ADIOS\Widgets\Settings\Models\Unit($this->adios))->getById($product["id_delivery_unit"]);
      $product["DELIVERY_UNIT"] = $delivery_unit;
    }
    return $product;
  }

  public function recalculatePriceForSingleProduct($productOrIdProduct) {
    if (is_numeric($productOrIdProduct)) {
      $idProduct = $productOrIdProduct;
    } else {
      $idProduct = $productOrIdProduct['id'];
    }

    $tmp = $this->getPriceInfoForSingleProduct($productOrIdProduct);

    $this->where('id', $idProduct)->update(["sale_price_calculated" => $tmp['salePrice']]);
  }

  public function recalculateAllPrices() {
    $this->beginTransaction();
    $products = $this->getForDetail()->get()->toArray();
    foreach ($products as $product) {
      $this->recalculatePriceForSingleProduct($product);
    }
    $this->commit();
  }

  public function onAfterSave($data, $returnValue) {
    $this->adios->widgets['Website']->rebuildSitemapForAllDomains();
    
    if (!empty($data['ProductCategoryAssignment'])) {
      $categories = @json_decode($data['ProductCategoryAssignment'], TRUE);
      if (is_array($categories)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductCategoryAssignment");

        foreach ($categories as $idCategory) {
          $model->assign($data['id'], $idCategory);
        }

        $model->deleteUnassigned($data['id'], $categories);
      }
    }

    if (!empty($data['ProductDomainAssignment'])) {
      $domains = @json_decode($data['ProductDomainAssignment'], TRUE);
      if (is_array($domains)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductDomainAssignment");

        foreach ($domains as $domain) {
          $model->assign($data['id'], $domain);
        }

        $model->deleteUnassigned($data['id'], $domains);
      }
    }

    if (!empty($data['ProductServiceAssignment'])) {
      $services = @json_decode($data['ProductServiceAssignment'], TRUE);
      if (is_array($services)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductServiceAssignment");

        foreach ($services as $idService) {
          $model->assign($data['id'], $idService);
        }

        $model->deleteUnassigned($data['id'], $services);
      }
    }

    $this->recalculatePriceForSingleProduct($data['id']);

    return parent::onAfterSave($data, $returnValue);
  }

  public function convertKeysForLookupData($item) {
    $conversionTable = [
      "features" => "FEATURES",
      "services" => "SERVICES",
    ];

    foreach ($conversionTable as $src => $dst) {
      if (is_array($item[$src])) {
        $item[$dst] = $item[$src];
        unset($item[$src]);
      }
    }

    return $item;
  }

  public function getForPriceInfo() {
    return $this
      ->with('nakupnaCena')
      ->with('productDiscounts')
      ->with('productCategoryDiscounts')
      ->with('productBrandDiscounts')
      ->with('productSupplierDiscounts')
      ->with('productMargins')
      ->with('productCategoryMargins')
      ->with('productBrandMargins')
      ->with('productSupplierMargins')
    ;
  }

  public function getForDetail() {
    return $this->getForPriceInfo()
      ->with('gallery')
      ->with('extensions')
      ->with('brand')
      ->with('supplier')
      ->with('features')
      ->with('related')
      ->with('accessories')
      ->with('services')
    ;
  }

  public function getById($id) {
    return $this->convertKeysForLookupData(
      reset($this->getForDetail()
        ->where('id', $id)
        ->get()
        ->toArray()
      )
    );

  }

  public function getDetailedInfoForListOfProducts($idProducts) {
    $products = $this->getForDetail()
      ->whereIn('id', $idProducts)
      ->get()
      ->toArray()
    ;

    $keyConversionTable = [
      "brand" => "BRAND",
      "gallery" => "GALLERY",
      "extensions" => "EXTENSIONS",
      "supplier" => "SUPPLIER",
      "features" => "FEATURES",
      "related" => "RELATED",
      "accessories" => "ACCESSORIES",
      "services" => "SERVICES",
    ];

    foreach ($products as $key => $product) {
      $products[$key]['PRICE'] = $this->getPriceInfoForSingleProduct($product);

      foreach ($keyConversionTable as $from => $to) {
        $products[$key][$to] = $product[$from];
        unset($products[$key][$from]);
      }
    }


    return $products;

  }

  public function getPriceInfoForListOfProducts($idProducts) {
    $products = $this->getForPriceInfo()
      ->whereIn('id', $idProducts)
      ->get()
      ->toArray()
    ;

    $priceInfo = [];
    foreach ($products as $product) {
      $priceInfo[$product['id']] = $this->getPriceInfoForSingleProduct($product);
    }

    return $priceInfo;
  }

  public function getPriceInfoForSingleProduct($productOrIdProduct) {
    $priceInfo = [];
    
    if (is_array($productOrIdProduct)) {
      $product = $productOrIdProduct;
      $idProduct = $productOrIdProduct['id'];
    } else {
      $idProduct = $productOrIdProduct;
      $product = $this->getById($idProduct);
    }

    $method = (int) $product['sale_price_calculation_method'];
    if ($method === 0) {
      $method = self::SALE_PRICE_CALCULATION_METHOD_BASE;
    }

    switch ($method) {
      case self::SALE_PRICE_CALCULATION_METHOD_PRICE_LIST:
        $purchasePrice = (float) $product['nakupna_cena']['price_excl_vat'] ?? 0;
        $salePrice = $purchasePrice;
        $discountsTotal = 0;
        $calculationSteps = [ [ "Nákupná cena", $purchasePrice ] ];

        // aplikujem marze

        foreach ($product['product_margins'] as $margin) {
          $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          $calculationSteps[] = ["Marža na produkt {$margin['margin']} %", $salePrice];
        }
        foreach ($product['product_category_margins'] as $margin) {
          $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          $calculationSteps[] = ["Marža na kategóriu {$margin['margin']} %", $salePrice];
        }
        foreach ($product['product_brand_margins'] as $margin) {
          $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          $calculationSteps[] = ["Marža na výrobcu {$margin['margin']} %", $salePrice];
        }
        foreach ($product['product_supplier_margins'] as $margin) {
          $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          $calculationSteps[] = ["Marža na dodávateľa {$margin['margin']} %", $salePrice];
        }

        $fullPrice = $salePrice;

        // aplikujem zlavy

        foreach ($product['product_discounts'] as $discount) {
          $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          $discountsTotal += $discount['discount_percentage'];
          $calculationSteps[] = ["Zľava na produkt {$discount['discount_percentage']} %", $salePrice];
        }
        foreach ($product['product_category_discounts'] as $discount) {
          $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          $discountsTotal += $discount['discount_percentage'];
          $calculationSteps[] = ["Zľava na kategóriu {$discount['discount_percentage']} %", $salePrice];
        }
        foreach ($product['product_brand_discounts'] as $discount) {
          $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          $discountsTotal += $discount['discount_percentage'];
          $calculationSteps[] = ["Zľava na výrobcu {$discount['discount_percentage']} %", $salePrice];
        }
        foreach ($product['product_supplier_discounts'] as $discount) {
          $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          $discountsTotal += $discount['discount_percentage'];
          $calculationSteps[] = ["Zľava na dodávateľa {$discount['discount_percentage']} %", $salePrice];
        }

        $priceInfo = [
          "salePrice" => $salePrice,
          "fullPrice" => $fullPrice,
          "discountsTotal" => $discountsTotal,
          "calculationSteps" => $calculationSteps,
        ];
      break;

      case self::SALE_PRICE_CALCULATION_METHOD_PLUGIN:
        $priceInfo = $this->adios->dispatchEventToPlugins("onProductAfterPriceCalculation", [
          "model" => $this,
          "idProduct" => $idProduct,
          "priceInfo" => $priceInfo,
        ])["priceInfo"];
      break;

      case self::SALE_PRICE_CALCULATION_METHOD_BASE:
        $priceInfo = [
          "salePrice" => $product['sale_price'],
          "fullPrice" => $product['sale_price'],
          "discountsTotal" => 0,
          "calculationSteps" => [],
        ];
      break;

    }

    return $priceInfo;


  }

}