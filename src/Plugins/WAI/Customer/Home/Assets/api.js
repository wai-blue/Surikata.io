class PluginWAICustomerHomeAPIClass {

  createAccount(success, fail) {
    let data = Surikata.serializeForm('#registrationForm');
    data['createAccount'] = true;

    Surikata.renderPluginJSON(
      'WAI/Customer/Registration',
      data,
      success,
      fail
    )
  }

  addAddress(success, fail) {
    let data = Surikata.serializeForm('#addAddressForm');
    data['createAddress'] = true;
    data['customerAction'] = "editAddress";

    Surikata.renderPluginJSON(
      'WAI/Customer/Home',
      data,
      success,
      fail
    )
  }

  removeAddress(data, success, fail) {
    Surikata.renderPluginJSON(
      'WAI/Customer/Home',
      data,
      success,
      fail
    )
  }

  forgotPassword(success, fail) {
    let data = Surikata.serializeForm('#forgotPasswordForm');
    Surikata.renderPluginJSON(
      'WAI/Customer/ForgotPassword',
      data,
      success,
      fail
    )
  }

}