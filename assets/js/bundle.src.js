cardValidator = require('card-validator');

cardValidator.supportedBrand = function(brand) {

  var types = {
    visa: "VISA",
    mastercard: "MASTERCARD",
    "american-express": "AMEX",
    "diners-club": "DINERS",
    discover: "DISCOVER",
    jcb: "JCB",
    unionpay: "",
    maestro: "MAESTROCARD",
    elo: "",
    mir: ""
  };

  if (typeof types[brand] != "undefined") {
    return types[brand];
  }

  return "";

};

/*
creditCardType.types = {
  VISA: VISA,
  MASTERCARD: MASTERCARD,
  AMERICAN_EXPRESS: AMERICAN_EXPRESS,
  DINERS_CLUB: DINERS_CLUB,
  DISCOVER: DISCOVER,
  JCB: JCB,
  UNIONPAY: UNIONPAY,
  MAESTRO: MAESTRO,
  ELO: ELO,
  MIR: MIR
};
*/