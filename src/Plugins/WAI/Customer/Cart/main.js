function SurikataCart() {
  //
}

SurikataCart.prototype.addProduct = function (idProduct, qty, success) {
  let _this = this;

  Surikata.renderPluginJSON(
    'CartDefault',
    {
      'idProduct': idProduct,
      'qty': qty,
      'cartAction': 'addToCart',
    },
    function (data) {
      _this.updateHeaderOverview(data.cartOverviewHtml);
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
}

SurikataCart.prototype.removeProduct = function (idProduct, success) {
  let _this = this;

  Surikata.renderPluginJSON(
    'CartDefault',
    {
      'idProduct': idProduct,
      'cartAction': 'removeFromCart',
    },
    function (data) {
      _this.updateHeaderOverview(data.cartOverviewHtml);
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
}

SurikataCart.prototype.updateProductQty = function (idProduct, qty, success) {
  let _this = this;

  Surikata.renderPluginJSON(
    'CartDefault',
    {
      'idProduct': idProduct,
      'qty': qty,
      'cartAction': 'updateQty',
    },
    function (data) {
      _this.updateHeaderOverview(data.cartOverviewHtml);
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
  
}

SurikataCart.prototype.serializeOrderData = function () {
  return {
    'orderData': Surikata.serializeForm('#orderDataForm'),
  };
}

SurikataCart.prototype.placeOrder = function (success, fail) {
  let data = this.serializeOrderData();
  data['cartAction'] = 'placeOrder';

  Surikata.renderPluginJSON(
    'CartDefault',
    data,
    success,
    fail
  );
}




