function SurikataCart() {
  //
}

SurikataCart.prototype.addProduct = function (idProduct, qty, success) {
  let _this = this;

  Surikata.renderPluginJSON(
    'WAI/Customer/Cart',
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
    'WAI/Customer/Cart',
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
    'WAI/Customer/Cart',
    {
      'idProduct': idProduct,
      'qty': qty,
      'cartAction': 'updateQty',
    },
    function (data) {
      //_this.updateHeaderOverview(data.cartOverviewHtml);
      //_this.updateDetailedOverview();
      //_this.initPlusMinusButtons();
      _this.updateProductPrices();
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
  
}

SurikataCart.prototype.updateProductPrices = function () {
  let _this = this;
  Surikata.renderPlugin(
    'WAI/Order/CartOverview',
    {},
    function (html) {
      $('.cart-main-area').replaceWith(function() {
        return $(html).hide().fadeIn(1000);
      });
      _this.initPlusMinusButtons();
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
    'WAI/Customer/Cart',
    data,
    success,
    fail
  );
}




