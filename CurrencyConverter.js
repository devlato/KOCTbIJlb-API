var CurrencyConverter = (function () {

    var __instance = null;

    CurrencyConverter.getInstance = function (options) {
        if (__instance === null) {
            __instance = new CurrencyConverter(options);
        } else {
            __instance.setOptions(options);
        }
        return __instance;
    };

    function CurrencyConverter(options) {

        CurrencyConverter.prototype.defaultOptions = {
            rates: {
                RUB: 1.0,
                UAH: 1.0,
                USD: 1.0,
                EUR: 1.0
            },
            base: 'RUB'
        };

        var __self, __options;
        __self = this;

        CurrencyConverter.prototype.init = function (options) {
            __self.setOptions(options);
            return __self;
        };

        CurrencyConverter.prototype.setOptions = function (options) {
            var rates, key;

            if (typeof options.rates === 'object') {
                rates = {};
                for (key in options.rates) {
                    if (!options.rates.hasOwnProperty(key)) {
                        continue;
                    }
                    rates[key.toUpperCase()] = options.rates[key];
                }
                options.rates = rates;
            }
            __options = $.extend(true, {}, CurrencyConverter.prototype.defaultOptions, __options, options);
            return __self;
        };

        CurrencyConverter.prototype.getOptions = function () {
            return __options;
        };

        CurrencyConverter.prototype.convert = function (value, to, from) {
            var options, rateFrom, rateTo, rate;

            options = __self.getOptions();
            from = from || options.base;
            to = to.toUpperCase();

            rateFrom = options.rates[from] || null;
            rateTo = options.rates[to] || null;

            if ((rateFrom === null) || (rateTo === null)) {
                throw new Error('Отсутствует курс для указанной валюты');
            }
            rate = rateFrom / rateTo;
            return value * rate;
        };

        return __self.init(options);
    }

    return CurrencyConverter;
})();
