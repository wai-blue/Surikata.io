class PluginWAICustomerCartAPIClass {

  addProduct(idProduct, qty, success) {
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
    )
  }

  removeProduct(idProduct, success) {
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
    )
  }

  updateProductQty(idProduct, qty, success) {
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
    )
  }
  
  updateProductPrices(success) {
    Surikata.renderPlugin(
      'WAI/Order/CartOverview',
      {},
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    )
  }

  updateDetailedOverview(success) {
    Surikata.renderPlugin(
      'WAI/Order/CartOverview',
      {},
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    )
  }

  serializeOrderData() {
    return {
      'orderData': Surikata.serializeForm('#orderDataForm'),
    };
  }

  placeOrder(success, fail) {
    let data = this.serializeOrderData();
    data['cartAction'] = 'placeOrder';
  
    Surikata.renderPluginJSON(
      'WAI/Customer/Cart',
      data,
      success,
      fail
    )
  }

  updateCheckoutOverview(success) {
    let data = this.serializeOrderData();
    data['renderOnly'] = 'orderOverview';
  
    Surikata.renderPlugin(
      'WAI/Order/Checkout',
      data,
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    )
  }

}
