function SurikataCustomer() { }

SurikataCustomer.prototype.createAccount = function (success, fail) {
  let data = Surikata.serializeForm('#registrationForm');
  data['createAccount'] = true;

  Surikata.renderPluginJSON(
    'WAI/Customer/Registration',
    data,
    success,
    fail
  );
}

SurikataCustomer.prototype.removeAddress = function (data, success, fail) {
  Surikata.renderPluginJSON(
    'WAI/Customer/Home',
    data,
    success,
    fail
  );
}

SurikataCustomer.prototype.forgotPassword = function (success, fail) {
  let data = Surikata.serializeForm('#forgotPasswordForm');

  Surikata.renderPluginJSON(
    'WAI/Customer/ForgotPassword',
    data,
    success,
    fail
  );
}