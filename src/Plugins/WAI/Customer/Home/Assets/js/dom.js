class PluginWAICustomerHomeDOMClass extends PluginWAICustomerHomeAPIClass {

  createAccount() {
    $('#registrationForm input').removeClass('required-empty');

    $('#emailIsEmptyOrInvalidErrorDiv').hide();
    $('#accountAlreadyExistsErrorDiv').hide();
    $('#unknownErrorDiv').hide();
    $('#newPasswordsDoNotMatchDiv').hide();
    $('#privacyPolicyTermsErrorText').hide();
  
    if (!$('#privacyPolicyTermsConfirmation').is(':checked')) {
      $('#privacyPolicyTermsErrorDiv').addClass("error");
      $('#privacyPolicyTermsErrorText').show();
    } else {
      $('#privacyPolicyTermsErrorDiv').removeClass("error");
      super.createAccount(
        function (dataSuccess) {
          if (dataSuccess.status == 'OK') {
            window.location.href = dataSuccess.registrationConfirmationUrl;
          }        
        },
        function (dataFail) {
          if (dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\EmptyRequiredFields') {
  
            let emptyFields = dataFail.error.split(',');
  
            for (var i in emptyFields) {
              $('#registrationForm input[name=' + emptyFields[i] + ']').addClass('required-empty');
            }
          } else if(dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsInvalid') {
            $("#registrationForm input[name=email]").addClass('required-empty');
            $('#emailIsEmptyOrInvalidErrorDiv').fadeIn();
          } else if (dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\AccountAlreadyExists') {
            $('#accountAlreadyExistsErrorDiv').fadeIn();
            $("#registrationForm input[name=email]").addClass('required-empty');
          } else if (dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\NewPasswordsDoNotMatch') {
            $('#newPasswordsDoNotMatchDiv').fadeIn();
          } else {
            $('#unknownErrorDiv').fadeIn();
          }
        }
      );
    }
  }
  
  addAddress() {
    super.addAddress(
      function(dataSuccess) {
        if (dataSuccess.status == 'OK') {
          location.reload();
        }
      },
      function(dataFail) {
        console.log(dataFail);
      }
    );
  }
  
  removeAddress(idAddress) {
    super.removeAddress(
      {
        'idAddress': idAddress,
        'customerAction': 'removeAddress'
      },
      function(dataSuccess) {
        if (dataSuccess.status == 'OK') {
          location.reload();
        } 
      },
      function(dataFail) {
        console.log(dataFail);
      }
    );
  }
  
  forgotPassword() {
    $('#unknownAccount').hide();
    $('#emailIsEmpty').hide();
    $('#emailIsInvalid').hide();
    $('#accountIsNotValidated').hide();
    $('#forgotPasswordDiv input[name=email]').removeClass('required-empty');
    super.forgotPassword(
      function(dataSuccess) {
        if (dataSuccess.status == 'OK') {
          $("#forgotPasswordDiv").hide();
          $("#recoveryPasswordSent").fadeIn();
        } 
      },
      function(dataFail) {
        $('#forgotPasswordDiv input[name=email]').addClass('required-empty');
        if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\UnknownAccount") {
          $('#unknownAccount').show();
        } else if(dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsEmpty") {
          $('#emailIsEmpty').show();
        } else if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsInvalid") {
          $('#emailIsInvalid').show();
        } else if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\AccountIsNotValidated") {
          $('#accountIsNotValidated').show();
        } else {
          console.log(dataFail);
        }
      }
    );
  }

}
