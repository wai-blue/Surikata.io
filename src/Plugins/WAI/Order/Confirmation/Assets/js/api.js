class PluginWAIOrderConfirmationAPIClass {

  setOrderAsPaid(orderId, success) {
    Surikata.renderPluginJSON(
      'WAI/Order/Confirmation',
      {
        orderAction: 'setAsPaid',
        orderId: orderId
      },
      function () {
        if (typeof success == 'function') {
          success();
        }
      }
    )
  }

}