(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * wpseoLocalGeocodingRepository class for geocoding addresses.
 */
var GeocodingRepository = function () {
	function GeocodingRepository() {
		_classCallCheck(this, GeocodingRepository);
	}

	_createClass(GeocodingRepository, null, [{
		key: "geoCodeAddress",

		/**
   * Geocode the address based using the Google maps JavaScript geocoding API
   *
   * @var object An object containing either { "address": <address as a string> } or { "location": <the LatLng coordinates>}
   */
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(location) {
				var geocoder;
				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								geocoder = new google.maps.Geocoder();

								if (!((typeof location === "undefined" ? "undefined" : _typeof(location)) === "object")) {
									_context.next = 3;
									break;
								}

								return _context.abrupt("return", new Promise(function (resolve, reject) {
									geocoder.geocode(location, function (results, status) {
										if (status === "OK") {
											return resolve(results);
										}

										return reject(status);
									});
								}));

							case 3:
								throw new Error("Location should be an object");

							case 4:
							case "end":
								return _context.stop();
						}
					}
				}, _callee, this);
			}));

			function geoCodeAddress(_x) {
				return _ref.apply(this, arguments);
			}

			return geoCodeAddress;
		}()
	}]);

	return GeocodingRepository;
}();

exports.default = GeocodingRepository;

},{}],2:[function(require,module,exports){
"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _wpSeoLocalGeocodingRepository = require("./wp-seo-local-geocoding-repository.js");

var _wpSeoLocalGeocodingRepository2 = _interopRequireDefault(_wpSeoLocalGeocodingRepository);

var _wpSeoLocalTimezoneRepository = require("./wp-seo-local-timezone-repository.js");

var _wpSeoLocalTimezoneRepository2 = _interopRequireDefault(_wpSeoLocalTimezoneRepository);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * A class to handle all address changes and maybe calculate new lat/long and timezone.
 */
var Locations = function () {
	/**
  * Constructor for the wpseoLocalGeocodingRepository.
  * Here we assign fields to class constants and bind methods.
  */
	function Locations() {
		_classCallCheck(this, Locations);

		this.addressFields = [document.querySelector(".wpseo_local_address_input"), document.querySelector(".wpseo_local_zipcode_input"), document.querySelector(".wpseo_local_city_input"), document.querySelector(".wpseo_local_state_input"), document.querySelector(".select[id*=\"_country\"]")];

		this.latField = document.querySelector(".wpseo_local_lat_input");
		this.lngField = document.querySelector(".wpseo_local_lng_input");

		this.timezoneField = document.querySelector("select[id*=\"_timezone\"]");

		this.apiKey = wpseoLocalLocations.apiKey;

		this.maybeGeoCodeAddress = this.maybeGeoCodeAddress.bind(this);
		this.setTimezone = this.setTimezone.bind(this);
		this.formatAddress = this.formatAddress.bind(this);
	}

	/**
  * Add event listeners to fire a function upon specified events.
  */


	_createClass(Locations, [{
		key: "addEventListeners",
		value: function addEventListeners() {
			document.addEventListener("change", this.maybeGeoCodeAddress);
		}

		/**
   * Check wheter a address should be geocoded.
   *
   * @param e The event passed by the event listener.
   */

	}, {
		key: "maybeGeoCodeAddress",
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(e) {
				var latOldValue, lngOldValue, formattedAddress, results, errorFieldElement;
				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								if (!(this.latField === null || this.lngField === null)) {
									_context.next = 2;
									break;
								}

								return _context.abrupt("return");

							case 2:
								if (!(this.addressFields.indexOf(e.target) !== -1)) {
									_context.next = 31;
									break;
								}

								this.errorField = document.querySelector(".wpseo_local_geocoding_error");
								latOldValue = this.latField.value;
								lngOldValue = this.lngField.value;
								formattedAddress = this.formatAddress();


								if (this.errorField !== null) {
									this.errorField.parentNode.removeChild(this.errorField);
								}

								/**
         * Empty the lat/lng fields. They will be recalculated and result in empty fields if geocoding failed.
         *
         * @type {string}
         */
								this.latField.value = "";
								this.lngField.value = "";

								/**
         * Try Geocoding of the given address. If it fails generate an error message based on the returned error.
         */
								_context.prev = 10;
								_context.next = 13;
								return _wpSeoLocalGeocodingRepository2.default.geoCodeAddress({ address: formattedAddress });

							case 13:
								results = _context.sent;


								this.latField.value = results[0].geometry.location.lat();
								this.lngField.value = results[0].geometry.location.lng();

								if (this.latField.value !== "" && this.lngField.value !== "" && this.latField.value !== latOldValue || this.lngField.value !== lngOldValue) {
									this.setTimezone();
								}

								_context.next = 31;
								break;

							case 19:
								_context.prev = 19;
								_context.t0 = _context["catch"](10);
								errorFieldElement = document.createElement("p");

								errorFieldElement.classList.add("wpseo_local_geocoding_error");

								_context.t1 = _context.t0;
								_context.next = _context.t1 === "ZERO_RESULTS" ? 26 : _context.t1 === "OVER_QUERY_LIMIT" ? 28 : _context.t1 === "REQUEST_DENIED" ? 30 : 31;
								break;

							case 26:
								errorFieldElement.appendChild(document.createTextNode("We could not retrieve coordinates for this address."));
								return _context.abrupt("break", 31);

							case 28:
								errorFieldElement.appendChild(document.createTextNode("You are over your query limit."));
								return _context.abrupt("break", 31);

							case 30:
								errorFieldElement.appendChild(document.createTextNode("Your API key is not entered or not valid."));

							case 31:
							case "end":
								return _context.stop();
						}
					}
				}, _callee, this, [[10, 19]]);
			}));

			function maybeGeoCodeAddress(_x) {
				return _ref.apply(this, arguments);
			}

			return maybeGeoCodeAddress;
		}()

		/**
   * Format an address the Google Geocoder can use based on the filled in address fields.
   *
   * @returns {string}
   */

	}, {
		key: "formatAddress",
		value: function formatAddress() {
			var address = [];

			this.addressFields.forEach(function (addressField) {
				if (addressField.value !== "") {
					address.push(addressField.value);
				}
			});

			return address.join(", ");
		}
	}, {
		key: "setTimezone",
		value: function () {
			var _ref2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
				var latLng, result;
				return regeneratorRuntime.wrap(function _callee2$(_context2) {
					while (1) {
						switch (_context2.prev = _context2.next) {
							case 0:
								/**
         * Check if either the lat or lng field has changed and if they are not empty
         */
								latLng = this.latField.value + ", " + this.lngField.value;
								_context2.prev = 1;
								_context2.next = 4;
								return _wpSeoLocalTimezoneRepository2.default.getTimezone(latLng, this.apiKey);

							case 4:
								result = _context2.sent;


								if (result !== "") {
									jQuery(this.timezoneField).val(result).trigger("change");
								}
								_context2.next = 11;
								break;

							case 8:
								_context2.prev = 8;
								_context2.t0 = _context2["catch"](1);

								console.log(_context2.t0);

							case 11:
							case "end":
								return _context2.stop();
						}
					}
				}, _callee2, this, [[1, 8]]);
			}));

			function setTimezone() {
				return _ref2.apply(this, arguments);
			}

			return setTimezone;
		}()
	}]);

	return Locations;
}();

var locationsInstance = new Locations();

locationsInstance.addEventListeners();

},{"./wp-seo-local-geocoding-repository.js":1,"./wp-seo-local-timezone-repository.js":3}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * TimezoneRepository class for retrieving the timezone based based on lat/lng coordinates.
 */
var TimezoneRepository = function () {
	function TimezoneRepository() {
		_classCallCheck(this, TimezoneRepository);
	}

	_createClass(TimezoneRepository, null, [{
		key: "getTimezone",

		/**
   * Get the timezone from Google's Timezone API
   *
   * @var object An object containing either { "address": <address as a string> } or { "location": <the LatLng coordinates>}
   */
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(latLng, apiKey) {
				var timestamp, searchParams, request;
				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								timestamp = Math.floor(Date.now() / 1000);
								searchParams = new URLSearchParams();


								searchParams.append("location", latLng);
								searchParams.append("timestamp", timestamp);
								searchParams.append("key", apiKey);

								request = "https://maps.googleapis.com/maps/api/timezone/json?" + searchParams;
								return _context.abrupt("return", new Promise(function (resolve, reject) {
									var xhr = new XMLHttpRequest();

									xhr.open("GET", request);
									xhr.onload = function () {
										if (xhr.status === 200) {
											var output = JSON.parse(xhr.responseText);

											if (output.status === 'OK') {
												return resolve(output.timeZoneId);
											}

											return reject(output);
										}

										return reject(xhr.status);
									};
									xhr.send();
								}));

							case 7:
							case "end":
								return _context.stop();
						}
					}
				}, _callee, this);
			}));

			function getTimezone(_x, _x2) {
				return _ref.apply(this, arguments);
			}

			return getTimezone;
		}()
	}]);

	return TimezoneRepository;
}();

exports.default = TimezoneRepository;

},{}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWdlb2NvZGluZy1yZXBvc2l0b3J5LmpzIiwianMvc3JjL3dwLXNlby1sb2NhbC1sb2NhdGlvbnMuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLXRpbWV6b25lLXJlcG9zaXRvcnkuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7OztBQ0FBOzs7SUFHcUIsbUI7Ozs7Ozs7O0FBQ3BCOzs7Ozs7dUZBSzZCLFE7Ozs7OztBQUN0QixnQixHQUFXLElBQUksT0FBTyxJQUFQLENBQVksUUFBaEIsRTs7Y0FFWixRQUFPLFFBQVAseUNBQU8sUUFBUCxPQUFvQixROzs7Ozt5Q0FDakIsSUFBSSxPQUFKLENBQWEsVUFBRSxPQUFGLEVBQVcsTUFBWCxFQUF1QjtBQUMxQyxrQkFBUyxPQUFULENBQWtCLFFBQWxCLEVBQTRCLFVBQUUsT0FBRixFQUFXLE1BQVgsRUFBdUI7QUFDbEQsY0FBSyxXQUFXLElBQWhCLEVBQXVCO0FBQ3RCLGtCQUFPLFFBQVMsT0FBVCxDQUFQO0FBQ0E7O0FBRUQsaUJBQU8sT0FBUSxNQUFSLENBQVA7QUFDQSxVQU5EO0FBT0EsU0FSTSxDOzs7Y0FXRixJQUFJLEtBQUosQ0FBVyw4QkFBWCxDOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7a0JBckJhLG1COzs7Ozs7O0FDSHJCOzs7O0FBQ0E7Ozs7Ozs7Ozs7QUFFQTs7O0lBR00sUztBQUNMOzs7O0FBSUEsc0JBQWM7QUFBQTs7QUFDYixPQUFLLGFBQUwsR0FBcUIsQ0FDcEIsU0FBUyxhQUFULENBQXdCLDRCQUF4QixDQURvQixFQUVwQixTQUFTLGFBQVQsQ0FBd0IsNEJBQXhCLENBRm9CLEVBR3BCLFNBQVMsYUFBVCxDQUF3Qix5QkFBeEIsQ0FIb0IsRUFJcEIsU0FBUyxhQUFULENBQXdCLDBCQUF4QixDQUpvQixFQUtwQixTQUFTLGFBQVQsQ0FBd0IsMkJBQXhCLENB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S0FBSyxLQUFMLENBQVcsSUFBSSxZQUFmLENBQWY7O0FBRUEsZUFBSSxPQUFPLE1BQVAsS0FBa0IsSUFBdEIsRUFBNkI7QUFDNUIsbUJBQU8sUUFBUyxPQUFPLFVBQWhCLENBQVA7QUFDQTs7QUFFRCxrQkFBTyxPQUFRLE1BQVIsQ0FBUDtBQUNBOztBQUVELGlCQUFPLE9BQVEsSUFBSSxNQUFaLENBQVA7QUFDQSxVQVpEO0FBYUEsYUFBSSxJQUFKO0FBQ0EsU0FsQk0sQzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O2tCQWpCWSxrQiIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogd3BzZW9Mb2NhbEdlb2NvZGluZ1JlcG9zaXRvcnkgY2xhc3MgZm9yIGdlb2NvZGluZyBhZGRyZXNzZXMuXG4gKi9cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIEdlb2NvZGluZ1JlcG9zaXRvcnkge1xuXHQvKipcblx0ICogR2VvY29kZSB0aGUgYWRkcmVzcyBiYXNlZCB1c2luZyB0aGUgR29vZ2xlIG1hcHMgSmF2YVNjcmlwdCBnZW9jb2RpbmcgQVBJXG5cdCAqXG5cdCAqIEB2YXIgb2JqZWN0IEFuIG9iamVjdCBjb250YWluaW5nIGVpdGhlciB7IFwiYWRkcmVzc1wiOiA8YWRkcmVzcyBhcyBhIHN0cmluZz4gfSBvciB7IFwibG9jYXRpb25cIjogPHRoZSBMYXRMbmcgY29vcmRpbmF0ZXM+fVxuXHQgKi9cblx0c3RhdGljIGFzeW5jIGdlb0NvZGVBZGRyZXNzKCBsb2NhdGlvbiApIHtcblx0XHRjb25zdCBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xuXG5cdFx0aWYgKCB0eXBlb2YgbG9jYXRpb24gPT09IFwib2JqZWN0XCIgKSB7XG5cdFx0XHRyZXR1cm4gbmV3IFByb21pc2UoICggcmVzb2x2ZSwgcmVqZWN0ICkgPT4ge1xuXHRcdFx0XHRnZW9jb2Rlci5nZW9jb2RlKCBsb2NhdGlvbiwgKCByZXN1bHRzLCBzdGF0dXMgKSA9PiB7XG5cdFx0XHRcdFx0aWYgKCBzdGF0dXMgPT09IFwiT0tcIiApIHtcblx0XHRcdFx0XHRcdHJldHVybiByZXNvbHZlKCByZXN1bHRzICk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0cmV0dXJuIHJlamVjdCggc3RhdHVzICk7XG5cdFx0XHRcdH0gKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHR0aHJvdyBuZXcgRXJyb3IoIFwiTG9jYXRpb24gc2hvdWxkIGJlIGFuIG9iamVjdFwiICk7XG5cdH1cbn0iLCJpbXBvcnQgR2VvQ29kaW5nUmVwb3NpdG9yeSBmcm9tIFwiLi93cC1zZW8tbG9jYWwtZ2VvY29kaW5nLXJlcG9zaXRvcnkuanNcIjtcbmltcG9ydCBUaW1lem9uZVJlcG9zaXRvcnkgZnJvbSBcIi4vd3Atc2VvLWxvY2FsLXRpbWV6b25lLXJlcG9zaXRvcnkuanNcIjtcblxuLyoqXG4gKiBBIGNsYXNzIHRvIGhhbmRsZSBhbGwgYWRkcmVzcyBjaGFuZ2VzIGFuZCBtYXliZSBjYWxjdWxhdGUgbmV3IGxhdC9sb25nIGFuZCB0aW1lem9uZS5cbiAqL1xuY2xhc3MgTG9jYXRpb25zIHtcblx0LyoqXG5cdCAqIENvbnN0cnVjdG9yIGZvciB0aGUgd3BzZW9Mb2NhbEdlb2NvZGluZ1JlcG9zaXRvcnkuXG5cdCAqIEhlcmUgd2UgYXNzaWduIGZpZWxkcyB0byBjbGFzcyBjb25zdGFudHMgYW5kIGJpbmQgbWV0aG9kcy5cblx0ICovXG5cdGNvbnN0cnVjdG9yKCkge1xuXHRcdHRoaXMuYWRkcmVzc0ZpZWxkcyA9IFtcblx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwiLndwc2VvX2xvY2FsX2FkZHJlc3NfaW5wdXRcIiApLFxuXHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIud3BzZW9fbG9jYWxfemlwY29kZV9pbnB1dFwiICksXG5cdFx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBcIi53cHNlb19sb2NhbF9jaXR5X2lucHV0XCIgKSxcblx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwiLndwc2VvX2xvY2FsX3N0YXRlX2lucHV0XCIgKSxcblx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwiLnNlbGVjdFtpZCo9XFxcIl9jb3VudHJ5XFxcIl1cIiApLFxuXHRcdF07XG5cblx0XHR0aGlzLmxhdEZpZWxkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIud3BzZW9fbG9jYWxfbGF0X2lucHV0XCIgKTtcblx0XHR0aGlzLmxuZ0ZpZWxkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIud3BzZW9fbG9jYWxfbG5nX2lucHV0XCIgKTtcblxuXHRcdHRoaXMudGltZXpvbmVGaWVsZCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwic2VsZWN0W2lkKj1cXFwiX3RpbWV6b25lXFxcIl1cIiApO1xuXG5cdFx0dGhpcy5hcGlLZXkgPSB3cHNlb0xvY2FsTG9jYXRpb25zLmFwaUtleTtcblxuXHRcdHRoaXMubWF5YmVHZW9Db2RlQWRkcmVzcyA9IHRoaXMubWF5YmVHZW9Db2RlQWRkcmVzcy5iaW5kKCB0aGlzICk7XG5cdFx0dGhpcy5zZXRUaW1lem9uZSA9IHRoaXMuc2V0VGltZXpvbmUuYmluZCggdGhpcyApO1xuXHRcdHRoaXMuZm9ybWF0QWRkcmVzcyA9IHRoaXMuZm9ybWF0QWRkcmVzcy5iaW5kKCB0aGlzICk7XG5cdH1cblxuXHQvKipcblx0ICogQWRkIGV2ZW50IGxpc3RlbmVycyB0byBmaXJlIGEgZnVuY3Rpb24gdXBvbiBzcGVjaWZpZWQgZXZlbnRzLlxuXHQgKi9cblx0YWRkRXZlbnRMaXN0ZW5lcnMoKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggXCJjaGFuZ2VcIiwgdGhpcy5tYXliZUdlb0NvZGVBZGRyZXNzICk7XG5cdH1cblxuXHQvKipcblx0ICogQ2hlY2sgd2hldGVyIGEgYWRkcmVzcyBzaG91bGQgYmUgZ2VvY29kZWQuXG5cdCAqXG5cdCAqIEBwYXJhbSBlIFRoZSBldmVudCBwYXNzZWQgYnkgdGhlIGV2ZW50IGxpc3RlbmVyLlxuXHQgKi9cblx0YXN5bmMgbWF5YmVHZW9Db2RlQWRkcmVzcyggZSApIHtcblx0XHQvKipcblx0XHQgKiBJZiBlaXRoZXIgdGhlIGxhdCBvciBsbmcgZmllbGQgZG9lcyBub3QgZXhpc3QgYmFpbCB0byBhdm9pZCAoY29uc29sZSllcnJvcnMuXG5cdFx0ICovXG5cdFx0aWYoIHRoaXMubGF0RmllbGQgPT09IG51bGwgfHwgdGhpcy5sbmdGaWVsZCA9PT0gbnVsbCApIHtcblx0XHRcdHJldHVyblxuXHRcdH1cblxuXHRcdGlmICggdGhpcy5hZGRyZXNzRmllbGRzLmluZGV4T2YoIGUudGFyZ2V0ICkgIT09IC0xICkge1xuXHRcdFx0dGhpcy5lcnJvckZpZWxkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIud3BzZW9fbG9jYWxfZ2VvY29kaW5nX2Vycm9yXCIgKTtcblx0XHRcdGNvbnN0IGxhdE9sZFZhbHVlID0gdGhpcy5sYXRGaWVsZC52YWx1ZTtcblx0XHRcdGNvbnN0IGxuZ09sZFZhbHVlID0gdGhpcy5sbmdGaWVsZC52YWx1ZTtcblxuXHRcdFx0Y29uc3QgZm9ybWF0dGVkQWRkcmVzcyA9IHRoaXMuZm9ybWF0QWRkcmVzcygpO1xuXG5cdFx0XHRpZiAoIHRoaXMuZXJyb3JGaWVsZCAhPT0gbnVsbCApIHtcblx0XHRcdFx0dGhpcy5lcnJvckZpZWxkLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoIHRoaXMuZXJyb3JGaWVsZCApO1xuXHRcdFx0fVxuXG5cdFx0XHQvKipcblx0XHRcdCAqIEVtcHR5IHRoZSBsYXQvbG5nIGZpZWxkcy4gVGhleSB3aWxsIGJlIHJlY2FsY3VsYXRlZCBhbmQgcmVzdWx0IGluIGVtcHR5IGZpZWxkcyBpZiBnZW9jb2RpbmcgZmFpbGVkLlxuXHRcdFx0ICpcblx0XHRcdCAqIEB0eXBlIHtzdHJpbmd9XG5cdFx0XHQgKi9cblx0XHRcdHRoaXMubGF0RmllbGQudmFsdWUgPSBcIlwiO1xuXHRcdFx0dGhpcy5sbmdGaWVsZC52YWx1ZSA9IFwiXCI7XG5cblx0XHRcdC8qKlxuXHRcdFx0ICogVHJ5IEdlb2NvZGluZyBvZiB0aGUgZ2l2ZW4gYWRkcmVzcy4gSWYgaXQgZmFpbHMgZ2VuZXJhdGUgYW4gZXJyb3IgbWVzc2FnZSBiYXNlZCBvbiB0aGUgcmV0dXJuZWQgZXJyb3IuXG5cdFx0XHQgKi9cblx0XHRcdHRyeSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdHMgPSBhd2FpdCBHZW9Db2RpbmdSZXBvc2l0b3J5Lmdlb0NvZGVBZGRyZXNzKCB7IGFkZHJlc3M6IGZvcm1hdHRlZEFkZHJlc3MgfSApO1xuXG5cdFx0XHRcdHRoaXMubGF0RmllbGQudmFsdWUgPSByZXN1bHRzWyAwIF0uZ2VvbWV0cnkubG9jYXRpb24ubGF0KCk7XG5cdFx0XHRcdHRoaXMubG5nRmllbGQudmFsdWUgPSByZXN1bHRzWyAwIF0uZ2VvbWV0cnkubG9jYXRpb24ubG5nKCk7XG5cblx0XHRcdFx0aWYgKCB0aGlzLmxhdEZpZWxkLnZhbHVlICE9PSBcIlwiICYmIHRoaXMubG5nRmllbGQudmFsdWUgIT09IFwiXCIgJiYgdGhpcy5sYXRGaWVsZC52YWx1ZSAhPT0gbGF0T2xkVmFsdWUgfHwgdGhpcy5sbmdGaWVsZC52YWx1ZSAhPT0gbG5nT2xkVmFsdWUgKSB7XG5cdFx0XHRcdFx0dGhpcy5zZXRUaW1lem9uZSgpO1xuXHRcdFx0XHR9XG5cblx0XHRcdH0gY2F0Y2ggKCBlcnJvciApIHtcblx0XHRcdFx0Y29uc3QgZXJyb3JGaWVsZEVsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCBcInBcIiApO1xuXHRcdFx0XHRlcnJvckZpZWxkRWxlbWVudC5jbGFzc0xpc3QuYWRkKCBcIndwc2VvX2xvY2FsX2dlb2NvZGluZ19lcnJvclwiICk7XG5cblx0XHRcdFx0c3dpdGNoICggZXJyb3IgKSB7XG5cdFx0XHRcdFx0Y2FzZSBcIlpFUk9fUkVTVUxUU1wiOlxuXHRcdFx0XHRcdFx0ZXJyb3JGaWVsZEVsZW1lbnQuYXBwZW5kQ2hpbGQoIGRvY3VtZW50LmNyZWF0ZVRleHROb2RlKCBcIldlIGNvdWxkIG5vdCByZXRyaWV2ZSBjb29yZGluYXRlcyBmb3IgdGhpcyBhZGRyZXNzLlwiICkgKTtcblx0XHRcdFx0XHRcdGJyZWFrO1xuXHRcdFx0XHRcdGNhc2UgXCJPVkVSX1FVRVJZX0xJTUlUXCI6XG5cdFx0XHRcdFx0XHRlcnJvckZpZWxkRWxlbWVudC5hcHBlbmRDaGlsZCggZG9jdW1lbnQuY3JlYXRlVGV4dE5vZGUoIFwiWW91IGFyZSBvdmVyIHlvdXIgcXVlcnkgbGltaXQuXCIgKSApO1xuXHRcdFx0XHRcdFx0YnJlYWs7XG5cdFx0XHRcdFx0Y2FzZSBcIlJFUVVFU1RfREVOSUVEXCI6XG5cdFx0XHRcdFx0XHRlcnJvckZpZWxkRWxlbWVudC5hcHBlbmRDaGlsZCggZG9jdW1lbnQuY3JlYXRlVGV4dE5vZGUoIFwiWW91ciBBUEkga2V5IGlzIG5vdCBlbnRlcmVkIG9yIG5vdCB2YWxpZC5cIiApICk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBEaXNwbGF5IHRoZSBlcnJvciBhZnRlciB0aGUgbG9uZ2l0dWRlIGZpZWxkLlxuXHRcdFx0XHQvLyBAbm90ZTogVGVtcG9yYXJpbHkgcmVtb3ZlZC4gV2lsbCBhZGQgdGhpcyBiYWNrIGxhdGVyLlxuXHRcdFx0XHQvLyB0aGlzLmxuZ0ZpZWxkLnBhcmVudE5vZGUuYWZ0ZXIoIGVycm9yRmllbGRFbGVtZW50ICk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIEZvcm1hdCBhbiBhZGRyZXNzIHRoZSBHb29nbGUgR2VvY29kZXIgY2FuIHVzZSBiYXNlZCBvbiB0aGUgZmlsbGVkIGluIGFkZHJlc3MgZmllbGRzLlxuXHQgKlxuXHQgKiBAcmV0dXJucyB7c3RyaW5nfVxuXHQgKi9cblx0Zm9ybWF0QWRkcmVzcygpIHtcblx0XHRsZXQgYWRkcmVzcyA9IFtdO1xuXG5cdFx0dGhpcy5hZGRyZXNzRmllbGRzLmZvckVhY2goIGZ1bmN0aW9uKCBhZGRyZXNzRmllbGQgKSB7XG5cdFx0XHRpZiAoIGFkZHJlc3NGaWVsZC52YWx1ZSAhPT0gXCJcIiApIHtcblx0XHRcdFx0YWRkcmVzcy5wdXNoKCBhZGRyZXNzRmllbGQudmFsdWUgKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cblx0XHRyZXR1cm4gYWRkcmVzcy5qb2luKCBcIiwgXCIgKTtcblx0fVxuXG5cdGFzeW5jIHNldFRpbWV6b25lKCkge1xuXHRcdC8qKlxuXHRcdCAqIENoZWNrIGlmIGVpdGhlciB0aGUgbGF0IG9yIGxuZyBmaWVsZCBoYXMgY2hhbmdlZCBhbmQgaWYgdGhleSBhcmUgbm90IGVtcHR5XG5cdFx0ICovXG5cdFx0Y29uc3QgbGF0TG5nID0gdGhpcy5sYXRGaWVsZC52YWx1ZSArIFwiLCBcIiArIHRoaXMubG5nRmllbGQudmFsdWU7XG5cblx0XHR0cnkge1xuXHRcdFx0Y29uc3QgcmVzdWx0ID0gYXdhaXQgVGltZXpvbmVSZXBvc2l0b3J5LmdldFRpbWV6b25lKCBsYXRMbmcsIHRoaXMuYXBpS2V5ICk7XG5cblx0XHRcdGlmICggcmVzdWx0ICE9PSBcIlwiICkge1xuXHRcdFx0XHRqUXVlcnkoIHRoaXMudGltZXpvbmVGaWVsZCApLnZhbCggcmVzdWx0ICkudHJpZ2dlciggXCJjaGFuZ2VcIiApO1xuXHRcdFx0fVxuXHRcdH0gY2F0Y2ggKCBlcnJvciApIHtcblx0XHRcdGNvbnNvbGUubG9nKCBlcnJvciApO1xuXHRcdH1cblx0fVxufVxuXG5jb25zdCBsb2NhdGlvbnNJbnN0YW5jZSA9IG5ldyBMb2NhdGlvbnMoKTtcblxubG9jYXRpb25zSW5zdGFuY2UuYWRkRXZlbnRMaXN0ZW5lcnMoKTsiLCIvKipcbiAqIFRpbWV6b25lUmVwb3NpdG9yeSBjbGFzcyBmb3IgcmV0cmlldmluZyB0aGUgdGltZXpvbmUgYmFzZWQgYmFzZWQgb24gbGF0L2xuZyBjb29yZGluYXRlcy5cbiAqL1xuZXhwb3J0IGRlZmF1bHQgY2xhc3MgVGltZXpvbmVSZXBvc2l0b3J5IHtcblx0LyoqXG5cdCAqIEdldCB0aGUgdGltZXpvbmUgZnJvbSBHb29nbGUncyBUaW1lem9uZSBBUElcblx0ICpcblx0ICogQHZhciBvYmplY3QgQW4gb2JqZWN0IGNvbnRhaW5pbmcgZWl0aGVyIHsgXCJhZGRyZXNzXCI6IDxhZGRyZXNzIGFzIGEgc3RyaW5nPiB9IG9yIHsgXCJsb2NhdGlvblwiOiA8dGhlIExhdExuZyBjb29yZGluYXRlcz59XG5cdCAqL1xuXHRzdGF0aWMgYXN5bmMgZ2V0VGltZXpvbmUoIGxhdExuZywgYXBpS2V5ICkge1xuXHRcdGNvbnN0IHRpbWVzdGFtcCA9IE1hdGguZmxvb3IoRGF0ZS5ub3coKSAvIDEwMDApO1xuXG5cdFx0Y29uc3Qgc2VhcmNoUGFyYW1zID0gbmV3IFVSTFNlYXJjaFBhcmFtcygpO1xuXG5cdFx0c2VhcmNoUGFyYW1zLmFwcGVuZCggXCJsb2NhdGlvblwiLCBsYXRMbmcgKTtcblx0XHRzZWFyY2hQYXJhbXMuYXBwZW5kKCBcInRpbWVzdGFtcFwiLCB0aW1lc3RhbXAgKTtcblx0XHRzZWFyY2hQYXJhbXMuYXBwZW5kKCBcImtleVwiLCBhcGlLZXkgKTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBcImh0dHBzOi8vbWFwcy5nb29nbGVhcGlzLmNvbS9tYXBzL2FwaS90aW1lem9uZS9qc29uP1wiICsgc2VhcmNoUGFyYW1zO1xuXG5cdFx0cmV0dXJuIG5ldyBQcm9taXNlKCAoIHJlc29sdmUsIHJlamVjdCApID0+IHtcblx0XHRcdGNvbnN0IHhociA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpO1xuXG5cdFx0XHR4aHIub3BlbiggXCJHRVRcIiwgcmVxdWVzdCApO1xuXHRcdFx0eGhyLm9ubG9hZCA9ICgpID0+IHtcblx0XHRcdFx0aWYoIHhoci5zdGF0dXMgPT09IDIwMCApIHtcblx0XHRcdFx0XHRjb25zdCBvdXRwdXQgPSBKU09OLnBhcnNlKHhoci5yZXNwb25zZVRleHQpO1xuXG5cdFx0XHRcdFx0aWYoIG91dHB1dC5zdGF0dXMgPT09ICdPSycgKSB7XG5cdFx0XHRcdFx0XHRyZXR1cm4gcmVzb2x2ZSggb3V0cHV0LnRpbWVab25lSWQgKTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRyZXR1cm4gcmVqZWN0KCBvdXRwdXQpO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0cmV0dXJuIHJlamVjdCggeGhyLnN0YXR1cyApO1xuXHRcdFx0fVxuXHRcdFx0eGhyLnNlbmQoKTtcblx0XHR9ICk7XG5cdH1cbn0iXX0=
