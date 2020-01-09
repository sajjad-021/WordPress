(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/* global YoastSEO */
/* global wpseoLocalL10n */

/* global YoastSEO: true, wpseoLocalL10n */

if (typeof YoastSEO !== 'undefined' && typeof YoastSEO.analysisWorker !== 'undefined') {
	new YoastLocalSEOPagesPlugin();
} else {
	jQuery(window).on('YoastSEO:ready', function () {
		return new YoastLocalSEOPagesPlugin();
	});
}

var YoastLocalSEOPagesPlugin = function () {
	function YoastLocalSEOPagesPlugin() {
		_classCallCheck(this, YoastLocalSEOPagesPlugin);

		this.bindEventListener();
		this.loadWorkerScripts();
	}

	_createClass(YoastLocalSEOPagesPlugin, [{
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

			YoastSEO.analysis.worker.loadScript(wpseoLocalL10n.pages_script_url).then(function () {
				return YoastSEO.analysis.worker.sendMessage('initializePages', wpseoLocalL10n, 'YoastLocalSEO');
			});
		}
	}]);

	return YoastLocalSEOPagesPlugin;
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWFuYWx5c2lzLXBhZ2VzLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7O0FDQUE7QUFDQTs7QUFFQTs7QUFHQSxJQUFLLE9BQU8sUUFBUCxLQUFvQixXQUFwQixJQUFtQyxPQUFPLFNBQVMsY0FBaEIsS0FBbUMsV0FBM0UsRUFBeUY7QUFDeEYsS0FBSSx3QkFBSjtBQUNBLENBRkQsTUFHSztBQUNKLFFBQVEsTUFBUixFQUFpQixFQUFqQixDQUFxQixnQkFBckIsRUFBdUM7QUFBQSxTQUFNLElBQUksd0JBQUosRUFBTjtBQUFBLEVBQXZDO0FBQ0E7O0lBRUssd0I7QUFDTCxxQ0FBYztBQUFBOztBQUNiLE9BQUssaUJBQUw7QUFDQSxPQUFLLGlCQUFMO0FBQ0E7Ozs7c0NBRW1CO0FBQ25CLE9BQU0sT0FBTyxTQUFTLGNBQVQsQ0FBeUIscUJBQXpCLENBQWI7QUFDQSxPQUFJLFNBQVMsSUFBYixFQUFrQjtBQUNqQixTQUFLLGdCQUFMLENBQXVCLFFBQXZCLEVBQWlDLFNBQVMsR0FBVCxDQUFhLE9BQTlDO0FBQ0E7QUFDRDs7O3NDQUVtQjtBQUNuQixPQUFLLE9BQU8sUUFBUCxLQUFvQixXQUFwQixJQUFtQyxPQUFPLFNBQVMsUUFBaEIsS0FBNkIsV0FBaEUsSUFBK0UsT0FBTyxTQUFTLFFBQVQsQ0FBa0IsTUFBekIsS0FBb0MsV0FBeEgsRUFBc0k7QUFDckk7QUFDQTs7QUFFRCxZQUFTLFFBQVQsQ0FBa0IsTUFBbEIsQ0FBeUIsVUFBekIsQ0FBcUMsZUFBZSxnQkFBcEQsRUFDRSxJQURGLENBQ1E7QUFBQSxXQUFNLFNBQVMsUUFBVCxDQUFrQixNQUFsQixDQUF5QixXQUF6QixDQUFzQyxpQkFBdEMsRUFBeUQsY0FBekQsRUFBeUUsZUFBekUsQ0FBTjtBQUFBLElBRFI7QUFFQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qIGdsb2JhbCBZb2FzdFNFTyAqL1xuLyogZ2xvYmFsIHdwc2VvTG9jYWxMMTBuICovXG5cbi8qIGdsb2JhbCBZb2FzdFNFTzogdHJ1ZSwgd3BzZW9Mb2NhbEwxMG4gKi9cblxuXG5pZiAoIHR5cGVvZiBZb2FzdFNFTyAhPT0gJ3VuZGVmaW5lZCcgJiYgdHlwZW9mIFlvYXN0U0VPLmFuYWx5c2lzV29ya2VyICE9PSAndW5kZWZpbmVkJyApIHtcblx0bmV3IFlvYXN0TG9jYWxTRU9QYWdlc1BsdWdpbigpO1xufVxuZWxzZSB7XG5cdGpRdWVyeSggd2luZG93ICkub24oICdZb2FzdFNFTzpyZWFkeScsICgpID0+IG5ldyBZb2FzdExvY2FsU0VPUGFnZXNQbHVnaW4oKSApO1xufVxuXG5jbGFzcyBZb2FzdExvY2FsU0VPUGFnZXNQbHVnaW4ge1xuXHRjb25zdHJ1Y3RvcigpIHtcblx0XHR0aGlzLmJpbmRFdmVudExpc3RlbmVyKCk7XG5cdFx0dGhpcy5sb2FkV29ya2VyU2NyaXB0cygpO1xuXHR9XG5cblx0YmluZEV2ZW50TGlzdGVuZXIoKSB7XG5cdFx0Y29uc3QgZWxlbSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnd3BzZW9fYnVzaW5lc3NfY2l0eScgKTtcblx0XHRpZiggZWxlbSAhPT0gbnVsbCl7XG5cdFx0XHRlbGVtLmFkZEV2ZW50TGlzdGVuZXIoICdjaGFuZ2UnLCBZb2FzdFNFTy5hcHAucmVmcmVzaCApO1xuXHRcdH1cblx0fVxuXG5cdGxvYWRXb3JrZXJTY3JpcHRzKCkge1xuXHRcdGlmICggdHlwZW9mIFlvYXN0U0VPID09PSAndW5kZWZpbmVkJyB8fCB0eXBlb2YgWW9hc3RTRU8uYW5hbHlzaXMgPT09IFwidW5kZWZpbmVkXCIgfHwgdHlwZW9mIFlvYXN0U0VPLmFuYWx5c2lzLndvcmtlciA9PT0gXCJ1bmRlZmluZWRcIiApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRZb2FzdFNFTy5hbmFseXNpcy53b3JrZXIubG9hZFNjcmlwdCggd3BzZW9Mb2NhbEwxMG4ucGFnZXNfc2NyaXB0X3VybCApXG5cdFx0XHQudGhlbiggKCkgPT4gWW9hc3RTRU8uYW5hbHlzaXMud29ya2VyLnNlbmRNZXNzYWdlKCAnaW5pdGlhbGl6ZVBhZ2VzJywgd3BzZW9Mb2NhbEwxMG4sICdZb2FzdExvY2FsU0VPJyApICk7XG5cdH1cbn1cbiJdfQ==
