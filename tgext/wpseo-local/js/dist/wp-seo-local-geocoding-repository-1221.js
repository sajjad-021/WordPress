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

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWdlb2NvZGluZy1yZXBvc2l0b3J5LmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7QUNBQTs7O0lBR3FCLG1COzs7Ozs7OztBQUNwQjs7Ozs7O3VGQUs2QixROzs7Ozs7QUFDdEIsZ0IsR0FBVyxJQUFJLE9BQU8sSUFBUCxDQUFZLFFBQWhCLEU7O2NBRVosUUFBTyxRQUFQLHlDQUFPLFFBQVAsT0FBb0IsUTs7Ozs7eUNBQ2pCLElBQUksT0FBSixDQUFhLFVBQUUsT0FBRixFQUFXLE1BQVgsRUFBdUI7QUFDMUMsa0JBQVMsT0FBVCxDQUFrQixRQUFsQixFQUE0QixVQUFFLE9BQUYsRUFBVyxNQUFYLEVBQXVCO0FBQ2xELGNBQUssV0FBVyxJQUFoQixFQUF1QjtBQUN0QixrQkFBTyxRQUFTLE9BQVQsQ0FBUDtBQUNBOztBQUVELGlCQUFPLE9BQVEsTUFBUixDQUFQO0FBQ0EsVUFORDtBQU9BLFNBUk0sQzs7O2NBV0YsSUFBSSxLQUFKLENBQVcsOEJBQVgsQzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O2tCQXJCYSxtQiIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogd3BzZW9Mb2NhbEdlb2NvZGluZ1JlcG9zaXRvcnkgY2xhc3MgZm9yIGdlb2NvZGluZyBhZGRyZXNzZXMuXG4gKi9cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIEdlb2NvZGluZ1JlcG9zaXRvcnkge1xuXHQvKipcblx0ICogR2VvY29kZSB0aGUgYWRkcmVzcyBiYXNlZCB1c2luZyB0aGUgR29vZ2xlIG1hcHMgSmF2YVNjcmlwdCBnZW9jb2RpbmcgQVBJXG5cdCAqXG5cdCAqIEB2YXIgb2JqZWN0IEFuIG9iamVjdCBjb250YWluaW5nIGVpdGhlciB7IFwiYWRkcmVzc1wiOiA8YWRkcmVzcyBhcyBhIHN0cmluZz4gfSBvciB7IFwibG9jYXRpb25cIjogPHRoZSBMYXRMbmcgY29vcmRpbmF0ZXM+fVxuXHQgKi9cblx0c3RhdGljIGFzeW5jIGdlb0NvZGVBZGRyZXNzKCBsb2NhdGlvbiApIHtcblx0XHRjb25zdCBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xuXG5cdFx0aWYgKCB0eXBlb2YgbG9jYXRpb24gPT09IFwib2JqZWN0XCIgKSB7XG5cdFx0XHRyZXR1cm4gbmV3IFByb21pc2UoICggcmVzb2x2ZSwgcmVqZWN0ICkgPT4ge1xuXHRcdFx0XHRnZW9jb2Rlci5nZW9jb2RlKCBsb2NhdGlvbiwgKCByZXN1bHRzLCBzdGF0dXMgKSA9PiB7XG5cdFx0XHRcdFx0aWYgKCBzdGF0dXMgPT09IFwiT0tcIiApIHtcblx0XHRcdFx0XHRcdHJldHVybiByZXNvbHZlKCByZXN1bHRzICk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0cmV0dXJuIHJlamVjdCggc3RhdHVzICk7XG5cdFx0XHRcdH0gKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHR0aHJvdyBuZXcgRXJyb3IoIFwiTG9jYXRpb24gc2hvdWxkIGJlIGFuIG9iamVjdFwiICk7XG5cdH1cbn0iXX0=
