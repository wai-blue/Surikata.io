var PluginWAICustomerCart = {
  addProduct: function (idProduct, qty, success) {
    Surikata.renderPluginJSON(
      'WAI/Customer/Cart',
      {
        'idProduct': idProduct,
        'qty': qty,
        'cartAction': 'addToCart',
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );
  },

  removeProduct: function (idProduct, success) {
    Surikata.renderPluginJSON(
      'WAI/Customer/Cart',
      {
        'idProduct': idProduct,
        'cartAction': 'removeFromCart',
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );
  },

  updateProductQty: function (idProduct, qty, success) {
    Surikata.renderPluginJSON(
      'WAI/Customer/Cart',
      {
        'idProduct': idProduct,
        'qty': qty,
        'cartAction': 'updateQty',
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );
  },
  
  updateProductPrices: function (success) {
    Surikata.renderPlugin(
      'WAI/Order/CartOverview',
      {},
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );
  },

  updateDetailedOverview: function(success) {
    Surikata.renderPlugin(
      'WAI/Order/CartOverview',
      {},
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );
  },

  serializeOrderData: function () {
    return {
      'orderData': Surikata.serializeForm('#orderDataForm'),
    };
  },

  placeOrder: function (success, fail) {
    let data = this.serializeOrderData();
    data['cartAction'] = 'placeOrder';
  
    Surikata.renderPluginJSON(
      'WAI/Customer/Cart',
      data,
      success,
      fail
    );
  }
}


