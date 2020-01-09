(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
	value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var _yoast$analysis = yoast.analysis,
    Paper = _yoast$analysis.Paper,
    Researcher = _yoast$analysis.Researcher,
    AssessmentResult = _yoast$analysis.AssessmentResult,
    Assessment = _yoast$analysis.Assessment;

var LocalStoreLocatorContentAssessment = function (_Assessment) {
	_inherits(LocalStoreLocatorContentAssessment, _Assessment);

	function LocalStoreLocatorContentAssessment(settings) {
		_classCallCheck(this, LocalStoreLocatorContentAssessment);

		var _this = _possibleConstructorReturn(this, (LocalStoreLocatorContentAssessment.__proto__ || Object.getPrototypeOf(LocalStoreLocatorContentAssessment)).call(this));

		_this.settings = settings;
		return _this;
	}

	/**
  * Runs an assessment for scoring the presence of a store locator in the content.
  *
  * @param {Paper} paper The paper to run this assessment on.
  * @param {Researcher} researcher The researcher used for the assessment.
  * @param {Object} i18n The i18n-object used for parsing translations.
  *
  * @returns {object} an assessment result with the score and formatted text.
  */


	_createClass(LocalStoreLocatorContentAssessment, [{
		key: 'getResult',
		value: function getResult(paper, researcher, i18n) {
			var assessmentResult = new AssessmentResult();

			var storeLocator = new RegExp('<form action=["\']#wpseo-storelocator-form["\'] method=["\']post["\'] id=["\']wpseo-storelocator-form["\']>((.|[\n|\r|\r\n])*?)<div id=["\']wpseo-storelocator-results["\']>', 'ig');
			var contains = storeLocator.test(paper.getText());
			storeLocator.lastIndex = 0;
			var content = paper.getText().replace(storeLocator, '');
			var result = this.score(content, contains);

			assessmentResult.setScore(result.score);
			assessmentResult.setText(result.text);

			return assessmentResult;
		}

		/**
   * Scores the url based on the matches of the store locator.
   *
   * @param {string} content  The content outside of the store locator.
   * @param {bool}   contains Whether or not the content contains a store locator.
   *
   * @returns {{score: number, text: *}} An object containing the score and text
   */

	}, {
		key: 'score',
		value: function score(content, contains) {
			if (contains && content.length <= 200) {
				return {
					score: 6,
					text: this.settings.storelocator_content
				};
			}

			return {
				score: 9,
				text: ''
			};
		}
	}]);

	return LocalStoreLocatorContentAssessment;
}(Assessment);

exports.default = LocalStoreLocatorContentAssessment;

},{}],2:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global analysisWorker */


var _localStoreLocatorContentAssessment = require('./assessments/local-store-locator-content-assessment');

var _localStoreLocatorContentAssessment2 = _interopRequireDefault(_localStoreLocatorContentAssessment);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var LocalPagesWorker = function () {
	function LocalPagesWorker() {
		_classCallCheck(this, LocalPagesWorker);
	}

	_createClass(LocalPagesWorker, [{
		key: 'register',
		value: function register() {
			analysisWorker.registerMessageHandler('initializePages', this.initialize.bind(this), 'YoastLocalSEO');
		}
	}, {
		key: 'initialize',
		value: function initialize(settings) {
			this.storeLocatorContentAssessment = new _localStoreLocatorContentAssessment2.default(settings);

			analysisWorker.registerAssessment('localStorelocatorContent', this.storeLocatorContentAssessment, 'YoastLocalSEO');
		}
	}]);

	return LocalPagesWorker;
}();

var localPagesWorker = new LocalPagesWorker();

localPagesWorker.register();

},{"./assessments/local-store-locator-content-assessment":1}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvYXNzZXNzbWVudHMvbG9jYWwtc3RvcmUtbG9jYXRvci1jb250ZW50LWFzc2Vzc21lbnQuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLXdvcmtlci1wYWdlcy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7O3NCQ0E0RCxNQUFNLFE7SUFBMUQsSyxtQkFBQSxLO0lBQU8sVSxtQkFBQSxVO0lBQVksZ0IsbUJBQUEsZ0I7SUFBa0IsVSxtQkFBQSxVOztJQUV4QixrQzs7O0FBQ3BCLDZDQUFhLFFBQWIsRUFBd0I7QUFBQTs7QUFBQTs7QUFFdkIsUUFBSyxRQUFMLEdBQWdCLFFBQWhCO0FBRnVCO0FBR3ZCOztBQUVEOzs7Ozs7Ozs7Ozs7OzRCQVNXLEssRUFBTyxVLEVBQVksSSxFQUFPO0FBQ3BDLE9BQU0sbUJBQW1CLElBQUksZ0JBQUosRUFBekI7O0FBRUEsT0FBTSxlQUFlLElBQUksTUFBSixDQUFZLDhLQUFaLEVBQTRMLElBQTVMLENBQXJCO0FBQ0EsT0FBTSxXQUFXLGFBQWEsSUFBYixDQUFtQixNQUFNLE9BQU4sRUFBbkIsQ0FBakI7QUFDQSxnQkFBYSxTQUFiLEdBQXlCLENBQXpCO0FBQ0EsT0FBTSxVQUFVLE1BQU0sT0FBTixHQUFnQixPQUFoQixDQUF5QixZQUF6QixFQUF1QyxFQUF2QyxDQUFoQjtBQUNBLE9BQU0sU0FBUyxLQUFLLEtBQUwsQ0FBWSxPQUFaLEVBQXFCLFFBQXJCLENBQWY7O0FBRUEsb0JBQWlCLFFBQWpCLENBQTJCLE9BQU8sS0FBbEM7QUFDQSxvQkFBaUIsT0FBakIsQ0FBMEIsT0FBTyxJQUFqQzs7QUFFQSxVQUFPLGdCQUFQO0FBQ0E7O0FBRUQ7Ozs7Ozs7Ozs7O3dCQVFPLE8sRUFBUyxRLEVBQVc7QUFDMUIsT0FBSyxZQUFZLFFBQVEsTUFBUixJQUFrQixHQUFuQyxFQUF5QztBQUN4QyxXQUFPO0FBQ04sWUFBTyxDQUREO0FBRU4sV0FBTSxLQUFLLFFBQUwsQ0FBYztBQUZkLEtBQVA7QUFJQTs7QUFFRCxVQUFPO0FBQ04sV0FBTyxDQUREO0FBRU4sVUFBTTtBQUZBLElBQVA7QUFJQTs7OztFQWxEOEQsVTs7a0JBQTNDLGtDOzs7OztxakJDRnJCOzs7QUFDQTs7Ozs7Ozs7SUFFTSxnQjs7Ozs7Ozs2QkFDTTtBQUNWLGtCQUFlLHNCQUFmLENBQXVDLGlCQUF2QyxFQUEwRCxLQUFLLFVBQUwsQ0FBZ0IsSUFBaEIsQ0FBc0IsSUFBdEIsQ0FBMUQsRUFBd0YsZUFBeEY7QUFDQTs7OzZCQUVXLFEsRUFBVztBQUN0QixRQUFLLDZCQUFMLEdBQXFDLElBQUksNENBQUosQ0FBd0MsUUFBeEMsQ0FBckM7O0FBRUEsa0JBQWUsa0JBQWYsQ0FBbUMsMEJBQW5DLEVBQStELEtBQUssNkJBQXBFLEVBQW1HLGVBQW5HO0FBQ0E7Ozs7OztBQUdGLElBQU0sbUJBQW1CLElBQUksZ0JBQUosRUFBekI7O0FBRUEsaUJBQWlCLFFBQWpCIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiY29uc3QgeyBQYXBlciwgUmVzZWFyY2hlciwgQXNzZXNzbWVudFJlc3VsdCwgQXNzZXNzbWVudCB9ID0geW9hc3QuYW5hbHlzaXM7XG5cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIExvY2FsU3RvcmVMb2NhdG9yQ29udGVudEFzc2Vzc21lbnQgZXh0ZW5kcyBBc3Nlc3NtZW50IHtcblx0Y29uc3RydWN0b3IoIHNldHRpbmdzICkge1xuXHRcdHN1cGVyKCk7XG5cdFx0dGhpcy5zZXR0aW5ncyA9IHNldHRpbmdzO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJ1bnMgYW4gYXNzZXNzbWVudCBmb3Igc2NvcmluZyB0aGUgcHJlc2VuY2Ugb2YgYSBzdG9yZSBsb2NhdG9yIGluIHRoZSBjb250ZW50LlxuXHQgKlxuXHQgKiBAcGFyYW0ge1BhcGVyfSBwYXBlciBUaGUgcGFwZXIgdG8gcnVuIHRoaXMgYXNzZXNzbWVudCBvbi5cblx0ICogQHBhcmFtIHtSZXNlYXJjaGVyfSByZXNlYXJjaGVyIFRoZSByZXNlYXJjaGVyIHVzZWQgZm9yIHRoZSBhc3Nlc3NtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gaTE4biBUaGUgaTE4bi1vYmplY3QgdXNlZCBmb3IgcGFyc2luZyB0cmFuc2xhdGlvbnMuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHtvYmplY3R9IGFuIGFzc2Vzc21lbnQgcmVzdWx0IHdpdGggdGhlIHNjb3JlIGFuZCBmb3JtYXR0ZWQgdGV4dC5cblx0ICovXG5cdGdldFJlc3VsdCggcGFwZXIsIHJlc2VhcmNoZXIsIGkxOG4gKSB7XG5cdFx0Y29uc3QgYXNzZXNzbWVudFJlc3VsdCA9IG5ldyBBc3Nlc3NtZW50UmVzdWx0KCk7XG5cblx0XHRjb25zdCBzdG9yZUxvY2F0b3IgPSBuZXcgUmVnRXhwKCAnPGZvcm0gYWN0aW9uPVtcIlxcJ10jd3BzZW8tc3RvcmVsb2NhdG9yLWZvcm1bXCJcXCddIG1ldGhvZD1bXCJcXCddcG9zdFtcIlxcJ10gaWQ9W1wiXFwnXXdwc2VvLXN0b3JlbG9jYXRvci1mb3JtW1wiXFwnXT4oKC58W1xcbnxcXHJ8XFxyXFxuXSkqPyk8ZGl2IGlkPVtcIlxcJ113cHNlby1zdG9yZWxvY2F0b3ItcmVzdWx0c1tcIlxcJ10+JywgJ2lnJyApO1xuXHRcdGNvbnN0IGNvbnRhaW5zID0gc3RvcmVMb2NhdG9yLnRlc3QoIHBhcGVyLmdldFRleHQoKSApO1xuXHRcdHN0b3JlTG9jYXRvci5sYXN0SW5kZXggPSAwO1xuXHRcdGNvbnN0IGNvbnRlbnQgPSBwYXBlci5nZXRUZXh0KCkucmVwbGFjZSggc3RvcmVMb2NhdG9yLCAnJyApO1xuXHRcdGNvbnN0IHJlc3VsdCA9IHRoaXMuc2NvcmUoIGNvbnRlbnQsIGNvbnRhaW5zICk7XG5cblx0XHRhc3Nlc3NtZW50UmVzdWx0LnNldFNjb3JlKCByZXN1bHQuc2NvcmUgKTtcblx0XHRhc3Nlc3NtZW50UmVzdWx0LnNldFRleHQoIHJlc3VsdC50ZXh0ICk7XG5cblx0XHRyZXR1cm4gYXNzZXNzbWVudFJlc3VsdDtcblx0fVxuXG5cdC8qKlxuXHQgKiBTY29yZXMgdGhlIHVybCBiYXNlZCBvbiB0aGUgbWF0Y2hlcyBvZiB0aGUgc3RvcmUgbG9jYXRvci5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IGNvbnRlbnQgIFRoZSBjb250ZW50IG91dHNpZGUgb2YgdGhlIHN0b3JlIGxvY2F0b3IuXG5cdCAqIEBwYXJhbSB7Ym9vbH0gICBjb250YWlucyBXaGV0aGVyIG9yIG5vdCB0aGUgY29udGVudCBjb250YWlucyBhIHN0b3JlIGxvY2F0b3IuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHt7c2NvcmU6IG51bWJlciwgdGV4dDogKn19IEFuIG9iamVjdCBjb250YWluaW5nIHRoZSBzY29yZSBhbmQgdGV4dFxuXHQgKi9cblx0c2NvcmUoIGNvbnRlbnQsIGNvbnRhaW5zICkge1xuXHRcdGlmICggY29udGFpbnMgJiYgY29udGVudC5sZW5ndGggPD0gMjAwICkge1xuXHRcdFx0cmV0dXJuIHtcblx0XHRcdFx0c2NvcmU6IDYsXG5cdFx0XHRcdHRleHQ6IHRoaXMuc2V0dGluZ3Muc3RvcmVsb2NhdG9yX2NvbnRlbnRcblx0XHRcdH1cblx0XHR9XG5cblx0XHRyZXR1cm4ge1xuXHRcdFx0c2NvcmU6IDksXG5cdFx0XHR0ZXh0OiAnJ1xuXHRcdH1cblx0fVxufVxuIiwiLyogZ2xvYmFsIGFuYWx5c2lzV29ya2VyICovXG5pbXBvcnQgTG9jYWxTdG9yZUxvY2F0b3JDb250ZW50QXNzZXNzbWVudCBmcm9tICcuL2Fzc2Vzc21lbnRzL2xvY2FsLXN0b3JlLWxvY2F0b3ItY29udGVudC1hc3Nlc3NtZW50JztcblxuY2xhc3MgTG9jYWxQYWdlc1dvcmtlciB7XG5cdHJlZ2lzdGVyKCkge1xuXHRcdGFuYWx5c2lzV29ya2VyLnJlZ2lzdGVyTWVzc2FnZUhhbmRsZXIoICdpbml0aWFsaXplUGFnZXMnLCB0aGlzLmluaXRpYWxpemUuYmluZCggdGhpcyApLCAnWW9hc3RMb2NhbFNFTycgKTtcblx0fVxuXG5cdGluaXRpYWxpemUoIHNldHRpbmdzICkge1xuXHRcdHRoaXMuc3RvcmVMb2NhdG9yQ29udGVudEFzc2Vzc21lbnQgPSBuZXcgTG9jYWxTdG9yZUxvY2F0b3JDb250ZW50QXNzZXNzbWVudCggc2V0dGluZ3MgKTtcblxuXHRcdGFuYWx5c2lzV29ya2VyLnJlZ2lzdGVyQXNzZXNzbWVudCggJ2xvY2FsU3RvcmVsb2NhdG9yQ29udGVudCcsIHRoaXMuc3RvcmVMb2NhdG9yQ29udGVudEFzc2Vzc21lbnQsICdZb2FzdExvY2FsU0VPJyApO1xuXHR9XG59XG5cbmNvbnN0IGxvY2FsUGFnZXNXb3JrZXIgPSBuZXcgTG9jYWxQYWdlc1dvcmtlcigpO1xuXG5sb2NhbFBhZ2VzV29ya2VyLnJlZ2lzdGVyKCk7XG4iXX0=
