function PluginWAICustomerHome() { }

PluginWAICustomerHome.prototype.createAccount = function (success, fail) {
  let data = Surikata.serializeForm('#registrationForm');
  data['createAccount'] = true;

  Surikata.renderPluginJSON(
    'WAI/Customer/Registration',
    data,
    success,
    fail
  );
}

PluginWAICustomerHome.prototype.addAddress = function (success, fail) {
  let data = Surikata.serializeForm('#addAddressForm');
  data['createAddress'] = true;
  data['customerAction'] = "editAddress";

  Surikata.renderPluginJSON(
      'WAI/Customer/Home',
      data,
      success,
      fail
  );
}

PluginWAICustomerHome.prototype.removeAddress = function (data, success, fail) {
  Surikata.renderPluginJSON(
    'WAI/Customer/Home',
    data,
    success,
    fail
  );
}

PluginWAICustomerHome.prototype.forgotPassword = function (success, fail) {
  let data = Surikata.serializeForm('#forgotPasswordForm');

  Surikata.renderPluginJSON(
    'WAI/Customer/ForgotPassword',
    data,
    success,
    fail
  );
}