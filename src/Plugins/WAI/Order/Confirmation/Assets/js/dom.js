class PluginWAIOrderConfirmationDOMClass extends PluginWAIOrderConfirmationAPIClass {

  setOrderAsPaid(orderId) {
    super.setOrderAsPaid(orderId, function() {
      window.location.reload();
    })
  }

}