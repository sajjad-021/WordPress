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

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 *
 */
var StoreLocator = function () {
	/**
  * Constructor for the StoreLocator JS class.
  * Here we assign fields to class constants and bind methods.
  */
	function StoreLocator() {
		_classCallCheck(this, StoreLocator);

		this.searchForm = document.querySelector("#wpseo-storelocator-form");
		this.searchInput = document.querySelector("#wpseo-sl-search");
		this.locationDetectionButton = document.querySelector(".wpseo_use_current_location");
		this.locationDetectionButtonImg = document.querySelector(".wpseo_use_current_location img");
		this.latField = document.querySelector("#wpseo-sl-lat");
		this.lngField = document.querySelector("#wpseo-sl-lng");

		this.latLng = "";

		this.locationDetection = this.locationDetection.bind(this);
		this.getLatLng = this.getLatLng.bind(this);
		this.maybeGeoCodeAddress = this.maybeGeoCodeAddress.bind(this);
		this.handleSubmitForm = this.handleSubmitForm.bind(this);
	}

	/**
  * Add event listeners to fire a function upon specified events.
  */


	_createClass(StoreLocator, [{
		key: "addEventListeners",
		value: function addEventListeners() {
			document.addEventListener("click", this.locationDetection);
			document.addEventListener("change", this.maybeGeoCodeAddress);
			document.addEventListener("submit", this.handleSubmitForm);
		}

		/**
   * Auto detect location based on browser information.
   *
   * @param e The event passed by the event listener.
   */

	}, {
		key: "locationDetection",
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(e) {
				var targetInputId, targetInputField, address;
				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								if (!(e.target === this.locationDetectionButton || e.target === this.locationDetectionButtonImg)) {
									_context.next = 25;
									break;
								}

								targetInputId = this.locationDetectionButton.dataset.target;
								targetInputField = document.querySelector("#" + targetInputId);

								// First try to get the lat and lng from the browser.

								_context.prev = 3;
								_context.next = 6;
								return this.getLatLng();

							case 6:
								this.latLng = _context.sent;


								this.latField.value = this.latLng.lat;
								this.lngField.value = this.latLng.lng;
								_context.next = 14;
								break;

							case 11:
								_context.prev = 11;
								_context.t0 = _context["catch"](3);

								console.log(_context.t0);

							case 14:
								if (!(false === this.latLng instanceof Error)) {
									_context.next = 25;
									break;
								}

								_context.prev = 15;
								_context.next = 18;
								return _wpSeoLocalGeocodingRepository2.default.geoCodeAddress({ "location": this.latLng });

							case 18:
								address = _context.sent;

								targetInputField.value = address[0].formatted_address;
								_context.next = 25;
								break;

							case 22:
								_context.prev = 22;
								_context.t1 = _context["catch"](15);

								console.log(_context.t1);

							case 25:
							case "end":
								return _context.stop();
						}
					}
				}, _callee, this, [[3, 11], [15, 22]]);
			}));

			function locationDetection(_x) {
				return _ref.apply(this, arguments);
			}

			return locationDetection;
		}()

		/**
   * Get the Lat and Lng position from the browser.
   *
   * @returns {Promise<*>}
   */

	}, {
		key: "getLatLng",
		value: function () {
			var _ref2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
				return regeneratorRuntime.wrap(function _callee2$(_context2) {
					while (1) {
						switch (_context2.prev = _context2.next) {
							case 0:
								return _context2.abrupt("return", new Promise(function (resolve, reject) {
									navigator.geolocation.getCurrentPosition(function (position) {
										return resolve({
											lat: parseFloat(position.coords.latitude),
											lng: parseFloat(position.coords.longitude)
										});
									}, function (error) {
										return reject(new Error("Location detection unsuccesfull"));
									});
								}));

							case 1:
							case "end":
								return _context2.stop();
						}
					}
				}, _callee2, this);
			}));

			function getLatLng() {
				return _ref2.apply(this, arguments);
			}

			return getLatLng;
		}()

		/**
   * Determine if an address should be geocoded. If so: do so.
   *
   * @param e The event passed by the event listener.
   *
   * @returns {Promise<void>}
   */

	}, {
		key: "maybeGeoCodeAddress",
		value: function () {
			var _ref3 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(e) {
				var results;
				return regeneratorRuntime.wrap(function _callee3$(_context3) {
					while (1) {
						switch (_context3.prev = _context3.next) {
							case 0:
								if (!(e.target === this.searchInput)) {
									_context3.next = 11;
									break;
								}

								_context3.prev = 1;
								_context3.next = 4;
								return _wpSeoLocalGeocodingRepository2.default.geoCodeAddress({ address: this.searchInput.value });

							case 4:
								results = _context3.sent;


								this.latField.value = results[0].geometry.location.lat();
								this.lngField.value = results[0].geometry.location.lng();

								_context3.next = 11;
								break;

							case 9:
								_context3.prev = 9;
								_context3.t0 = _context3["catch"](1);

							case 11:
							case "end":
								return _context3.stop();
						}
					}
				}, _callee3, this, [[1, 9]]);
			}));

			function maybeGeoCodeAddress(_x2) {
				return _ref3.apply(this, arguments);
			}

			return maybeGeoCodeAddress;
		}()

		/**
   * Catch the submit event and check wheter possibly the lat/lng data has to be calculated.
   *
   * @param e The event passed by the event listener.
   *
   * @returns {Promise<void>}
   */

	}, {
		key: "handleSubmitForm",
		value: function () {
			var _ref4 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5(e) {
				var _this = this;

				var promise, result;
				return regeneratorRuntime.wrap(function _callee5$(_context5) {
					while (1) {
						switch (_context5.prev = _context5.next) {
							case 0:
								if (!(e.target === this.searchForm)) {
									_context5.next = 12;
									break;
								}

								e.preventDefault();

								if (!(this.latField.value === "" || this.lngField.value === "")) {
									_context5.next = 11;
									break;
								}

								document.removeEventListener("submit", this.handleSubmitForm);
								promise = new Promise(function () {
									var _ref5 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4(resolve, reject) {
										var results;
										return regeneratorRuntime.wrap(function _callee4$(_context4) {
											while (1) {
												switch (_context4.prev = _context4.next) {
													case 0:
														_context4.next = 2;
														return _wpSeoLocalGeocodingRepository2.default.geoCodeAddress({ address: _this.searchInput.value });

													case 2:
														results = _context4.sent;


														_this.latField.value = results[0].geometry.location.lat();
														_this.lngField.value = results[0].geometry.location.lng();

														if (_this.latField.value !== "" && _this.lngField.value !== "") {
															resolve('success');
														}

													case 6:
													case "end":
														return _context4.stop();
												}
											}
										}, _callee4, _this);
									}));

									return function (_x4, _x5) {
										return _ref5.apply(this, arguments);
									};
								}());
								_context5.next = 7;
								return promise;

							case 7:
								result = _context5.sent;


								if (result === 'success') {
									this.searchForm.submit();
								}
								_context5.next = 12;
								break;

							case 11:
								this.searchForm.submit();

							case 12:
							case "end":
								return _context5.stop();
						}
					}
				}, _callee5, this);
			}));

			function handleSubmitForm(_x3) {
				return _ref4.apply(this, arguments);
			}

			return handleSubmitForm;
		}()
	}]);

	return StoreLocator;
}();

var storeLocatorInstance = new StoreLocator();

storeLocatorInstance.addEventListeners();

},{"./wp-seo-local-geocoding-repository.js":1}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWdlb2NvZGluZy1yZXBvc2l0b3J5LmpzIiwianMvc3JjL3dwLXNlby1sb2NhbC1zdG9yZS1sb2NhdG9yLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7QUNBQTs7O0lBR3FCLG1COzs7Ozs7OztBQUNwQjs7Ozs7O3VGQUs2QixROzs7Ozs7QUFDdEIsZ0IsR0FBVyxJQUFJLE9BQU8sSUFBUCxDQUFZLFFBQWhCLEU7O2NBRVosUUFBTyxRQUFQLHlDQUFPLFFBQVAsT0FBb0IsUTs7Ozs7eUNBQ2pCLElBQUksT0FBSixDQUFhLFVBQUUsT0FBRixFQUFXLE1BQVgsRUFBdUI7QUFDMUMsa0JBQVMsT0FBVCxDQUFrQixRQUFsQixFQUE0QixVQUFFLE9BQUYsRUFBVyxNQUFYLEVBQXVCO0FBQ2xELGNBQUssV0FBVyxJQUFoQixFQUF1QjtBQUN0QixrQkFBTyxRQUFTLE9BQVQsQ0FBUDtBQUNBOztBQUVELGlCQUFPLE9BQVEsTUFBUixDQUFQO0FBQ0EsVUFORDtBQU9BLFNBUk0sQzs7O2NBV0YsSUFBSSxLQUFKLENBQVcsOEJBQVgsQzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O2tCQXJCYSxtQjs7Ozs7OztBQ0hyQjs7Ozs7Ozs7OztBQUVBOzs7SUFHTSxZO0FBQ0w7Ozs7QUFJQSx5QkFBYztBQUFBOztBQUNiLE9BQUssVUFBTCxHQUFrQixTQUFTLGFBQVQsQ0FBd0IsMEJBQXhCLENBQWxCO0FBQ0EsT0FBSyxXQUFMLEdBQW1CLFNBQVMsYUFBVCxDQUF3QixrQkFBeEIsQ0FBbkI7QUFDQSxPQUFLLHVCQUFMLEdBQStCLFNBQVMsYUFBVCxDQUF3Qiw2QkFBeEIsQ0FBL0I7QUFDQSxPQUFLLDBCQUFMLEdBQWtDLFNBQVMsYUFBVCxDQUF3QixpQ0FBeEIsQ0FBbEM7QUFDQSxPQUFLLFFBQUwsR0FBZ0IsU0FBUyxhQUFULENBQXdCLGVB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QUFLLFFBQUwsQ0FBYyxLQUFkLEtBQXdCLEVBQXhCLElBQThCLEtBQUssUUFBTCxDQUFjLEtBQWQsS0FBd0IsRTs7Ozs7QUFDMUQsaUJBQVMsbUJBQVQsQ0FBOEIsUUFBOUIsRUFBd0MsS0FBSyxnQkFBN0M7QUFDTSxlLEdBQVUsSUFBSSxPQUFKO0FBQUEsNkVBQWEsa0JBQU8sT0FBUCxFQUFnQixNQUFoQjtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBLHFCQUNOLHdDQUFvQixjQUFwQixDQUFvQyxFQUFFLFNBQVMsTUFBSyxXQUFMLENBQWlCLEtBQTVCLEVBQXBDLENBRE07O0FBQUE7QUFDdEIscUJBRHNCOzs7QUFHNUIsb0JBQUssUUFBTCxDQUFjLEtBQWQsR0FBc0IsUUFBUyxDQUFULEVBQWEsUUFBYixDQUFzQixRQUF0QixDQUErQixHQUEvQixFQUF0QjtBQUNBLG9CQUFLLFFBQUwsQ0FBYyxLQUFkLEdBQXNCLFFBQVMsQ0FBVCxFQUFhLFFBQWIsQ0FBc0IsUUFBdEIsQ0FBK0IsR0FBL0IsRUFBdEI7O0FBRUEsa0JBQUssTUFBSyxRQUFMLENBQWMsS0FBZCxLQUF3QixFQUF4QixJQUE4QixNQUFLLFFBQUwsQ0FBYyxLQUFkLEtBQXdCLEVBQTNELEVBQWdFO0FBQy9ELHVCQUFTLFNBQVQ7QUFDQTs7QUFSMkI7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUEsVUFBYjs7QUFBQTtBQUFBO0FBQUE7QUFBQSxZOztlQVdLLE87OztBQUFmLGM7OztBQUVOLFlBQUksV0FBVyxTQUFmLEVBQTJCO0FBQzFCLGNBQUssVUFBTCxDQUFnQixNQUFoQjtBQUNBOzs7OztBQUVELGFBQUssVUFBTCxDQUFnQixNQUFoQjs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBTUosSUFBTSx1QkFBdUIsSUFBSSxZQUFKLEVBQTdCOztBQUVBLHFCQUFxQixpQkFBckIiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcbiAqIHdwc2VvTG9jYWxHZW9jb2RpbmdSZXBvc2l0b3J5IGNsYXNzIGZvciBnZW9jb2RpbmcgYWRkcmVzc2VzLlxuICovXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBHZW9jb2RpbmdSZXBvc2l0b3J5IHtcblx0LyoqXG5cdCAqIEdlb2NvZGUgdGhlIGFkZHJlc3MgYmFzZWQgdXNpbmcgdGhlIEdvb2dsZSBtYXBzIEphdmFTY3JpcHQgZ2VvY29kaW5nIEFQSVxuXHQgKlxuXHQgKiBAdmFyIG9iamVjdCBBbiBvYmplY3QgY29udGFpbmluZyBlaXRoZXIgeyBcImFkZHJlc3NcIjogPGFkZHJlc3MgYXMgYSBzdHJpbmc+IH0gb3IgeyBcImxvY2F0aW9uXCI6IDx0aGUgTGF0TG5nIGNvb3JkaW5hdGVzPn1cblx0ICovXG5cdHN0YXRpYyBhc3luYyBnZW9Db2RlQWRkcmVzcyggbG9jYXRpb24gKSB7XG5cdFx0Y29uc3QgZ2VvY29kZXIgPSBuZXcgZ29vZ2xlLm1hcHMuR2VvY29kZXIoKTtcblxuXHRcdGlmICggdHlwZW9mIGxvY2F0aW9uID09PSBcIm9iamVjdFwiICkge1xuXHRcdFx0cmV0dXJuIG5ldyBQcm9taXNlKCAoIHJlc29sdmUsIHJlamVjdCApID0+IHtcblx0XHRcdFx0Z2VvY29kZXIuZ2VvY29kZSggbG9jYXRpb24sICggcmVzdWx0cywgc3RhdHVzICkgPT4ge1xuXHRcdFx0XHRcdGlmICggc3RhdHVzID09PSBcIk9LXCIgKSB7XG5cdFx0XHRcdFx0XHRyZXR1cm4gcmVzb2x2ZSggcmVzdWx0cyApO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdHJldHVybiByZWplY3QoIHN0YXR1cyApO1xuXHRcdFx0XHR9ICk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXG5cdFx0dGhyb3cgbmV3IEVycm9yKCBcIkxvY2F0aW9uIHNob3VsZCBiZSBhbiBvYmplY3RcIiApO1xuXHR9XG59IiwiaW1wb3J0IEdlb0NvZGluZ1JlcG9zaXRvcnkgZnJvbSBcIi4vd3Atc2VvLWxvY2FsLWdlb2NvZGluZy1yZXBvc2l0b3J5LmpzXCI7XG5cbi8qKlxuICpcbiAqL1xuY2xhc3MgU3RvcmVMb2NhdG9yIHtcblx0LyoqXG5cdCAqIENvbnN0cnVjdG9yIGZvciB0aGUgU3RvcmVMb2NhdG9yIEpTIGNsYXNzLlxuXHQgKiBIZXJlIHdlIGFzc2lnbiBmaWVsZHMgdG8gY2xhc3MgY29uc3RhbnRzIGFuZCBiaW5kIG1ldGhvZHMuXG5cdCAqL1xuXHRjb25zdHJ1Y3RvcigpIHtcblx0XHR0aGlzLnNlYXJjaEZvcm0gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBcIiN3cHNlby1zdG9yZWxvY2F0b3ItZm9ybVwiICk7XG5cdFx0dGhpcy5zZWFyY2hJbnB1dCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwiI3dwc2VvLXNsLXNlYXJjaFwiICk7XG5cdFx0dGhpcy5sb2NhdGlvbkRldGVjdGlvbkJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIFwiLndwc2VvX3VzZV9jdXJyZW50X2xvY2F0aW9uXCIgKTtcblx0XHR0aGlzLmxvY2F0aW9uRGV0ZWN0aW9uQnV0dG9uSW1nID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIud3BzZW9fdXNlX2N1cnJlbnRfbG9jYXRpb24gaW1nXCIgKTtcblx0XHR0aGlzLmxhdEZpZWxkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIjd3BzZW8tc2wtbGF0XCIgKTtcblx0XHR0aGlzLmxuZ0ZpZWxkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggXCIjd3BzZW8tc2wtbG5nXCIgKTtcblxuXHRcdHRoaXMubGF0TG5nID0gXCJcIjtcblxuXHRcdHRoaXMubG9jYXRpb25EZXRlY3Rpb24gPSB0aGlzLmxvY2F0aW9uRGV0ZWN0aW9uLmJpbmQoIHRoaXMgKTtcblx0XHR0aGlzLmdldExhdExuZyA9IHRoaXMuZ2V0TGF0TG5nLmJpbmQoIHRoaXMgKTtcblx0XHR0aGlzLm1heWJlR2VvQ29kZUFkZHJlc3MgPSB0aGlzLm1heWJlR2VvQ29kZUFkZHJlc3MuYmluZCggdGhpcyApO1xuXHRcdHRoaXMuaGFuZGxlU3VibWl0Rm9ybSA9IHRoaXMuaGFuZGxlU3VibWl0Rm9ybS5iaW5kKCB0aGlzICk7XG5cdH1cblxuXHQvKipcblx0ICogQWRkIGV2ZW50IGxpc3RlbmVycyB0byBmaXJlIGEgZnVuY3Rpb24gdXBvbiBzcGVjaWZpZWQgZXZlbnRzLlxuXHQgKi9cblx0YWRkRXZlbnRMaXN0ZW5lcnMoKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggXCJjbGlja1wiLCB0aGlzLmxvY2F0aW9uRGV0ZWN0aW9uICk7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggXCJjaGFuZ2VcIiwgdGhpcy5tYXliZUdlb0NvZGVBZGRyZXNzICk7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggXCJzdWJtaXRcIiwgdGhpcy5oYW5kbGVTdWJtaXRGb3JtICk7XG5cdH1cblxuXHQvKipcblx0ICogQXV0byBkZXRlY3QgbG9jYXRpb24gYmFzZWQgb24gYnJvd3NlciBpbmZvcm1hdGlvbi5cblx0ICpcblx0ICogQHBhcmFtIGUgVGhlIGV2ZW50IHBhc3NlZCBieSB0aGUgZXZlbnQgbGlzdGVuZXIuXG5cdCAqL1xuXHRhc3luYyBsb2NhdGlvbkRldGVjdGlvbiggZSApIHtcblx0XHQvLyBDaGVjayBib3RoIHRoZSBidXR0b24gYW5kIHRoZSBpbWFnZSBpbiBpdCBmb3IgdGhlIGNsaWNrIGV2ZW50LlxuXHRcdGlmICggZS50YXJnZXQgPT09IHRoaXMubG9jYXRpb25EZXRlY3Rpb25CdXR0b24gfHwgZS50YXJnZXQgPT09IHRoaXMubG9jYXRpb25EZXRlY3Rpb25CdXR0b25JbWcgKSB7XG5cdFx0XHRjb25zdCB0YXJnZXRJbnB1dElkID0gdGhpcy5sb2NhdGlvbkRldGVjdGlvbkJ1dHRvbi5kYXRhc2V0LnRhcmdldDtcblx0XHRcdGNvbnN0IHRhcmdldElucHV0RmllbGQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBcIiNcIiArIHRhcmdldElucHV0SWQgKTtcblxuXHRcdFx0Ly8gRmlyc3QgdHJ5IHRvIGdldCB0aGUgbGF0IGFuZCBsbmcgZnJvbSB0aGUgYnJvd3Nlci5cblx0XHRcdHRyeSB7XG5cdFx0XHRcdHRoaXMubGF0TG5nID0gYXdhaXQgdGhpcy5nZXRMYXRMbmcoKTtcblxuXHRcdFx0XHR0aGlzLmxhdEZpZWxkLnZhbHVlID0gdGhpcy5sYXRMbmcubGF0O1xuXHRcdFx0XHR0aGlzLmxuZ0ZpZWxkLnZhbHVlID0gdGhpcy5sYXRMbmcubG5nO1xuXHRcdFx0fSBjYXRjaCAoIGVycm9yICkge1xuXHRcdFx0XHRjb25zb2xlLmxvZyggZXJyb3IgKTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gQ29udGludWUgdGhlIGdlb2NvZGluZyBpZiB0aGUgcmVxdWVzdGVkIGxhdC9sbmcgZnJvbSB0aGUgYnJvd3NlciBkaWQgbm90IHJlc3VsdCBpbiBhbiBlcnJvci5cblx0XHRcdGlmICggZmFsc2UgPT09IHRoaXMubGF0TG5nIGluc3RhbmNlb2YgRXJyb3IgKSB7XG5cdFx0XHRcdHRyeSB7XG5cdFx0XHRcdFx0Y29uc3QgYWRkcmVzcyA9IGF3YWl0IEdlb0NvZGluZ1JlcG9zaXRvcnkuZ2VvQ29kZUFkZHJlc3MoIHsgXCJsb2NhdGlvblwiOiB0aGlzLmxhdExuZyB9ICk7XG5cdFx0XHRcdFx0dGFyZ2V0SW5wdXRGaWVsZC52YWx1ZSA9IGFkZHJlc3NbIDAgXS5mb3JtYXR0ZWRfYWRkcmVzcztcblx0XHRcdFx0fSBjYXRjaCAoIGVycm9yICkge1xuXHRcdFx0XHRcdGNvbnNvbGUubG9nKCBlcnJvciApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIEdldCB0aGUgTGF0IGFuZCBMbmcgcG9zaXRpb24gZnJvbSB0aGUgYnJvd3Nlci5cblx0ICpcblx0ICogQHJldHVybnMge1Byb21pc2U8Kj59XG5cdCAqL1xuXHRhc3luYyBnZXRMYXRMbmcoKSB7XG5cdFx0cmV0dXJuIG5ldyBQcm9taXNlKCAoIHJlc29sdmUsIHJlamVjdCApID0+IHtcblx0XHRcdG5hdmlnYXRvci5nZW9sb2NhdGlvbi5nZXRDdXJyZW50UG9zaXRpb24oICggcG9zaXRpb24gKSA9PiB7XG5cdFx0XHRcdHJldHVybiByZXNvbHZlKCB7XG5cdFx0XHRcdFx0bGF0OiBwYXJzZUZsb2F0KCBwb3NpdGlvbi5jb29yZHMubGF0aXR1ZGUgKSxcblx0XHRcdFx0XHRsbmc6IHBhcnNlRmxvYXQoIHBvc2l0aW9uLmNvb3Jkcy5sb25naXR1ZGUgKSxcblx0XHRcdFx0fSApO1xuXHRcdFx0fSwgZnVuY3Rpb24oIGVycm9yICkge1xuXHRcdFx0XHRyZXR1cm4gcmVqZWN0KCBuZXcgRXJyb3IoIFwiTG9jYXRpb24gZGV0ZWN0aW9uIHVuc3VjY2VzZnVsbFwiICkgKTtcblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogRGV0ZXJtaW5lIGlmIGFuIGFkZHJlc3Mgc2hvdWxkIGJlIGdlb2NvZGVkLiBJZiBzbzogZG8gc28uXG5cdCAqXG5cdCAqIEBwYXJhbSBlIFRoZSBldmVudCBwYXNzZWQgYnkgdGhlIGV2ZW50IGxpc3RlbmVyLlxuXHQgKlxuXHQgKiBAcmV0dXJucyB7UHJvbWlzZTx2b2lkPn1cblx0ICovXG5cdGFzeW5jIG1heWJlR2VvQ29kZUFkZHJlc3MoIGUgKSB7XG5cdFx0aWYgKCBlLnRhcmdldCA9PT0gdGhpcy5zZWFyY2hJbnB1dCApIHtcblx0XHRcdHRyeSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdHMgPSBhd2FpdCBHZW9Db2RpbmdSZXBvc2l0b3J5Lmdlb0NvZGVBZGRyZXNzKCB7IGFkZHJlc3M6IHRoaXMuc2VhcmNoSW5wdXQudmFsdWUgfSApO1xuXG5cdFx0XHRcdHRoaXMubGF0RmllbGQudmFsdWUgPSByZXN1bHRzWyAwIF0uZ2VvbWV0cnkubG9jYXRpb24ubGF0KCk7XG5cdFx0XHRcdHRoaXMubG5nRmllbGQudmFsdWUgPSByZXN1bHRzWyAwIF0uZ2VvbWV0cnkubG9jYXRpb24ubG5nKCk7XG5cblx0XHRcdH0gY2F0Y2ggKCBlcnJvciApIHtcblxuXHRcdFx0fVxuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBDYXRjaCB0aGUgc3VibWl0IGV2ZW50IGFuZCBjaGVjayB3aGV0ZXIgcG9zc2libHkgdGhlIGxhdC9sbmcgZGF0YSBoYXMgdG8gYmUgY2FsY3VsYXRlZC5cblx0ICpcblx0ICogQHBhcmFtIGUgVGhlIGV2ZW50IHBhc3NlZCBieSB0aGUgZXZlbnQgbGlzdGVuZXIuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHtQcm9taXNlPHZvaWQ+fVxuXHQgKi9cblx0YXN5bmMgaGFuZGxlU3VibWl0Rm9ybSggZSApIHtcblx0XHRpZiAoIGUudGFyZ2V0ID09PSB0aGlzLnNlYXJjaEZvcm0gKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHRcdGlmICggdGhpcy5sYXRGaWVsZC52YWx1ZSA9PT0gXCJcIiB8fCB0aGlzLmxuZ0ZpZWxkLnZhbHVlID09PSBcIlwiICkge1xuXHRcdFx0XHRkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKCBcInN1Ym1pdFwiLCB0aGlzLmhhbmRsZVN1Ym1pdEZvcm0gKTtcblx0XHRcdFx0Y29uc3QgcHJvbWlzZSA9IG5ldyBQcm9taXNlKCBhc3luYyAocmVzb2x2ZSwgcmVqZWN0KSA9PiB7XG5cdFx0XHRcdFx0Y29uc3QgcmVzdWx0cyA9IGF3YWl0IEdlb0NvZGluZ1JlcG9zaXRvcnkuZ2VvQ29kZUFkZHJlc3MoIHsgYWRkcmVzczogdGhpcy5zZWFyY2hJbnB1dC52YWx1ZSB9ICk7XG5cblx0XHRcdFx0XHR0aGlzLmxhdEZpZWxkLnZhbHVlID0gcmVzdWx0c1sgMCBdLmdlb21ldHJ5LmxvY2F0aW9uLmxhdCgpO1xuXHRcdFx0XHRcdHRoaXMubG5nRmllbGQudmFsdWUgPSByZXN1bHRzWyAwIF0uZ2VvbWV0cnkubG9jYXRpb24ubG5nKCk7XG5cblx0XHRcdFx0XHRpZiAoIHRoaXMubGF0RmllbGQudmFsdWUgIT09IFwiXCIgJiYgdGhpcy5sbmdGaWVsZC52YWx1ZSAhPT0gXCJcIiApIHtcblx0XHRcdFx0XHRcdHJlc29sdmUoICdzdWNjZXNzJyApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0Y29uc3QgcmVzdWx0ID0gYXdhaXQgcHJvbWlzZTtcblxuXHRcdFx0XHRpZiggcmVzdWx0ID09PSAnc3VjY2VzcycgKSB7XG5cdFx0XHRcdFx0dGhpcy5zZWFyY2hGb3JtLnN1Ym1pdCgpO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR0aGlzLnNlYXJjaEZvcm0uc3VibWl0KCk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG59XG5cbmNvbnN0IHN0b3JlTG9jYXRvckluc3RhbmNlID0gbmV3IFN0b3JlTG9jYXRvcigpO1xuXG5zdG9yZUxvY2F0b3JJbnN0YW5jZS5hZGRFdmVudExpc3RlbmVycygpOyJdfQ==
