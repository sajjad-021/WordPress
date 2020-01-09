(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/* global YoastSEO */
/* global wpseoLocalL10n */

if (typeof YoastSEO !== 'undefined' && typeof YoastSEO.analysisWorker !== 'undefined') {
	new YoastLocalSEOLocationsPlugin();
} else {
	jQuery(window).on('YoastSEO:ready', function () {
		return new YoastLocalSEOLocationsPlugin();
	});
}

var YoastLocalSEOLocationsPlugin = function () {
	function YoastLocalSEOLocationsPlugin() {
		_classCallCheck(this, YoastLocalSEOLocationsPlugin);

		this.bindEventListener();
		this.loadWorkerScripts();
	}

	_createClass(YoastLocalSEOLocationsPlugin, [{
		key: 'bindEventListener',
		value: function bindEventListener() {
			var elem = document.getElementById('wpseo_business_city');
			if (elem !== null) {
				elem.addEventListener('change', YoastSEO.app.refresh);
			}
		}
	}, {
		key: 'loadWorkerScripts',
		value: function loadWorkerScripts() {
			if (typeof YoastSEO === 'undefined' || typeof YoastSEO.analysis === "undefined" || typeof YoastSEO.analysis.worker === "undefined") {
				return;
			}

			YoastSEO.analysis.worker.loadScript(wpseoLocalL10n.locations_script_url).then(function () {
				return YoastSEO.analysis.worker.sendMessage('initializeLocations', wpseoLocalL10n, 'YoastLocalSEO');
			});
		}
	}]);

	return YoastLocalSEOLocationsPlugin;
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWFuYWx5c2lzLWxvY2F0aW9ucy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7OztBQ0FBO0FBQ0E7O0FBRUEsSUFBSyxPQUFPLFFBQVAsS0FBb0IsV0FBcEIsSUFBbUMsT0FBTyxTQUFTLGNBQWhCLEtBQW1DLFdBQTNFLEVBQXlGO0FBQ3hGLEtBQUksNEJBQUo7QUFDQSxDQUZELE1BR0s7QUFDSixRQUFRLE1BQVIsRUFBaUIsRUFBakIsQ0FBcUIsZ0JBQXJCLEVBQXVDO0FBQUEsU0FBTSxJQUFJLDRCQUFKLEVBQU47QUFBQSxFQUF2QztBQUNBOztJQUVLLDRCO0FBQ0wseUNBQWM7QUFBQTs7QUFDYixPQUFLLGlCQUFMO0FBQ0EsT0FBSyxpQkFBTDtBQUNBOzs7O3NDQUVtQjtBQUNuQixPQUFNLE9BQU8sU0FBUyxjQUFULENBQXlCLHFCQUF6QixDQUFiO0FBQ0EsT0FBSSxTQUFTLElBQWIsRUFBa0I7QUFDakIsU0FBSyxnQkFBTCxDQUF1QixRQUF2QixFQUFpQyxTQUFTLEdBQVQsQ0FBYSxPQUE5QztBQUNBO0FBQ0Q7OztzQ0FFbUI7QUFDbkIsT0FBSyxPQUFPLFFBQVAsS0FBb0IsV0FBcEIsSUFBbUMsT0FBTyxTQUFTLFFBQWhCLEtBQTZCLFdBQWhFLElBQStFLE9BQU8sU0FBUyxRQUFULENBQWtCLE1BQXpCLEtBQW9DLFdBQXhILEVBQXNJO0FBQ3JJO0FBQ0E7O0FBRUQsWUFBUyxRQUFULENBQWtCLE1BQWxCLENBQXlCLFVBQXpCLENBQXFDLGVBQWUsb0JBQXBELEVBQ0UsSUFERixDQUNRO0FBQUEsV0FBTSxTQUFTLFFBQVQsQ0FBa0IsTUFBbEIsQ0FBeUIsV0FBekIsQ0FBc0MscUJBQXRDLEVBQTZELGNBQTdELEVBQTZFLGVBQTdFLENBQU47QUFBQSxJQURSO0FBRUEiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKiBnbG9iYWwgWW9hc3RTRU8gKi9cbi8qIGdsb2JhbCB3cHNlb0xvY2FsTDEwbiAqL1xuXG5pZiAoIHR5cGVvZiBZb2FzdFNFTyAhPT0gJ3VuZGVmaW5lZCcgJiYgdHlwZW9mIFlvYXN0U0VPLmFuYWx5c2lzV29ya2VyICE9PSAndW5kZWZpbmVkJyApIHtcblx0bmV3IFlvYXN0TG9jYWxTRU9Mb2NhdGlvbnNQbHVnaW4oKTtcbn1cbmVsc2Uge1xuXHRqUXVlcnkoIHdpbmRvdyApLm9uKCAnWW9hc3RTRU86cmVhZHknLCAoKSA9PiBuZXcgWW9hc3RMb2NhbFNFT0xvY2F0aW9uc1BsdWdpbigpICk7XG59XG5cbmNsYXNzIFlvYXN0TG9jYWxTRU9Mb2NhdGlvbnNQbHVnaW4ge1xuXHRjb25zdHJ1Y3RvcigpIHtcblx0XHR0aGlzLmJpbmRFdmVudExpc3RlbmVyKCk7XG5cdFx0dGhpcy5sb2FkV29ya2VyU2NyaXB0cygpO1xuXHR9XG5cblx0YmluZEV2ZW50TGlzdGVuZXIoKSB7XG5cdFx0Y29uc3QgZWxlbSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnd3BzZW9fYnVzaW5lc3NfY2l0eScgKTtcblx0XHRpZiggZWxlbSAhPT0gbnVsbCl7XG5cdFx0XHRlbGVtLmFkZEV2ZW50TGlzdGVuZXIoICdjaGFuZ2UnLCBZb2FzdFNFTy5hcHAucmVmcmVzaCApO1xuXHRcdH1cblx0fVxuXG5cdGxvYWRXb3JrZXJTY3JpcHRzKCkge1xuXHRcdGlmICggdHlwZW9mIFlvYXN0U0VPID09PSAndW5kZWZpbmVkJyB8fCB0eXBlb2YgWW9hc3RTRU8uYW5hbHlzaXMgPT09IFwidW5kZWZpbmVkXCIgfHwgdHlwZW9mIFlvYXN0U0VPLmFuYWx5c2lzLndvcmtlciA9PT0gXCJ1bmRlZmluZWRcIiApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRZb2FzdFNFTy5hbmFseXNpcy53b3JrZXIubG9hZFNjcmlwdCggd3BzZW9Mb2NhbEwxMG4ubG9jYXRpb25zX3NjcmlwdF91cmwgKVxuXHRcdFx0LnRoZW4oICgpID0+IFlvYXN0U0VPLmFuYWx5c2lzLndvcmtlci5zZW5kTWVzc2FnZSggJ2luaXRpYWxpemVMb2NhdGlvbnMnLCB3cHNlb0xvY2FsTDEwbiwgJ1lvYXN0TG9jYWxTRU8nICkgKTtcblx0fVxufVxuIl19
