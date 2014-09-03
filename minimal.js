/**
 * @see  http://www.semrush.com/admin/{db_name}/products/edit/{product_id}
 */


define('app/admin/corporate-accounts', ['underscore', 'backbone', 'jquery'], function(_, Backbone, $) {
	'use strict';


	var self = null;
	return Backbone.View.extend({
		__defaultOptions: {
			container: $('.app-container'),
			selectors: {
				//
			},
			events: {
				//
			},
			locale: {
				//
			},
			silent: false,
			log: function() {
				if (!self.getOptions().silent && console && (typeof console.log === 'function')) {
					console.log.apply(window, arguments);
				}
			}
		},

		initialize: function(options) {
			self = this;
			self.setOptions(options);
			self.templates = self.setTemplates(self.getOptions().locale);
			self.bind();
			return self;
		},

		dispose: function() {
			self.unbind();
		},

		eventHandlers: {

		},

		bind: function() {
			var options = self.getOptions();

		},

		unbind: function() {
			var options = self.getOptions();

			_.each(options.events, function(event) {
				options.container.off(event);
			});
		},

		getOptions: function() {
			return self.__options;
		},

		setOptions: function(options, value) {
			if (value) {
				self.__options[options] = value;
			} else {
				self.__options = $.extend(true, {}, self.__defaultOptions, self.__options || {}, options || {});
			}
			return self;
		},

		getLogger: function () {
			var options = self.getOptions(),
				logger  = options.log;

			if (options.silent) {
				return logger;
			}

			return function() {};
		},

		setTemplates: function(item) {
			var result;

			if ($.isPlainObject(item) || $.isArray(item)) {
				result = {};
				_.each(item, function(value, key) {
					result[key] = self.setTemplates(value);
				});
			} else {
				result = _.template(item);
			}

			return result;
		}
	});
});
