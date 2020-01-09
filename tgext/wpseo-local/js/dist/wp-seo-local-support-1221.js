(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

/* global wpseoAdminL10n */
(function () {
	"use strict";

	jQuery(document).ready(function () {

		if (typeof HS !== 'undefined') {
			jQuery(window).on('YoastSEO:ContactSupport', function (e, data) {
				if (data.usedQueries != undefined) {
					var identity = HS.beacon.get_helpscout_beacon_identity();
					identity['User searched for'] = usedQueriesWithHTML(data.usedQueries);
					HS.beacon.identify(identity);
				}
				jQuery('#wpseo-contact-support-popup').hide();
				HS.beacon.open();
			});
		}

		/**
   * Format the search queries done by the user in HTML.
   *
   * @param {array} usedQueries List of queries entered by the user.
   * @returns {string} Table containing link to posts.
   */
		function usedQueriesWithHTML(usedQueries) {
			var output = '';

			if (jQuery.isEmptyObject(usedQueries)) {
				return '<em>Search history is empty.</em>';
			}

			output += '<table><tr><th>Searched for</th><th>Opened article</th></tr>';

			jQuery.each(usedQueries, function (searchString, posts) {
				output += "<tr><td>" + searchString + "</td>";
				output += getPostsHTML(posts);
				output += "</tr>";
			});

			output = output + "</table>";

			return output;
		}

		/**
   * Format the posts looked at by the user in HTML.
   *
   * @param {array} posts List of posts opened by the user.
   * @returns {string} Table containing links to posts.
   */
		function getPostsHTML(posts) {
			var output = '';
			var first = true;

			if (jQuery.isEmptyObject(posts)) {
				return "<td><em>No articles were opened.</em></td>";
			}

			jQuery.each(posts, function (postId, post) {
				if (first === false) {
					output += "<td></td>";
				}
				output += "<td><a href='" + post.link + "'>" + post.title + "</a></td>";
				first = false;
			});

			return output;
		}

		// Get the used search strings from the algoliaSearcher React component for the active tab and fire an event with this data.
		jQuery(".contact-support").on("click", function () {
			jQuery(window).trigger("YoastSEO:ContactSupport", { usedQueries: {} });
		});
	});
})();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLXN1cHBvcnQuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7OztBQ0FBO0FBQ0EsQ0FBRSxZQUFXO0FBQ1o7O0FBRUEsUUFBUSxRQUFSLEVBQW1CLEtBQW5CLENBQTBCLFlBQVc7O0FBRXBDLE1BQUssT0FBUSxFQUFSLEtBQWlCLFdBQXRCLEVBQW9DO0FBQ25DLFVBQVEsTUFBUixFQUFpQixFQUFqQixDQUFxQix5QkFBckIsRUFBZ0QsVUFBVSxDQUFWLEVBQWEsSUFBYixFQUFvQjtBQUNuRSxRQUFLLEtBQUssV0FBTCxJQUFvQixTQUF6QixFQUFtQztBQUNsQyxTQUFJLFdBQVcsR0FBRyxNQUFILENBQVUsNkJBQVYsRUFBZjtBQUNBLGNBQVUsbUJBQVYsSUFBa0Msb0JBQXFCLEtBQUssV0FBMUIsQ0FBbEM7QUFDQSxRQUFHLE1BQUgsQ0FBVSxRQUFWLENBQW1CLFFBQW5CO0FBQ0E7QUFDRCxXQUFRLDhCQUFSLEVBQXlDLElBQXpDO0FBQ0EsT0FBRyxNQUFILENBQVUsSUFBVjtBQUNBLElBUkQ7QUFTQTs7QUFFRDs7Ozs7O0FBTUEsV0FBUyxtQkFBVCxDQUE4QixXQUE5QixFQUE0QztBQUMzQyxPQUFJLFNBQVMsRUFBYjs7QUFFQSxPQUFLLE9BQU8sYUFBUCxDQUFzQixXQUF0QixDQUFMLEVBQTJDO0FBQzFDLFdBQU8sbUNBQVA7QUFDQTs7QUFFRCxhQUFVLDhEQUFWOztBQUVBLFVBQU8sSUFBUCxDQUFhLFdBQWIsRUFBMEIsVUFBVSxZQUFWLEVBQXdCLEtBQXhCLEVBQWdDO0FBQ3pELGNBQVUsYUFBYSxZQUFiLEdBQTRCLE9BQXRDO0FBQ0EsY0FBVSxhQUFjLEtBQWQsQ0FBVjtBQUNBLGNBQVUsT0FBVjtBQUNBLElBSkQ7O0FBTUEsWUFBUyxTQUFTLFVBQWxCOztBQUVBLFVBQU8sTUFBUDtBQUNBOztBQUVEOzs7Ozs7QUFNQSxXQUFTLFlBQVQsQ0FBdUIsS0FBdkIsRUFBK0I7QUFDOUIsT0FBSSxTQUFTLEVBQWI7QUFDQSxPQUFJLFFBQVEsSUFBWjs7QUFFQSxPQUFLLE9BQU8sYUFBUCxDQUFzQixLQUF0QixDQUFMLEVBQXFDO0FBQ3BDLFdBQU8sNENBQVA7QUFDQTs7QUFFRCxVQUFPLElBQVAsQ0FBYSxLQUFiLEVBQW9CLFVBQVUsTUFBVixFQUFrQixJQUFsQixFQUF5QjtBQUM1QyxRQUFLLFVBQVUsS0FBZixFQUF1QjtBQUN0QixlQUFVLFdBQVY7QUFDQTtBQUNELGNBQVUsa0JBQWtCLEtBQUssSUFBdkIsR0FBOEIsSUFBOUIsR0FBcUMsS0FBSyxLQUExQyxHQUFrRCxXQUE1RDtBQUNBLFlBQVEsS0FBUjtBQUNBLElBTkQ7O0FBUUEsVUFBTyxNQUFQO0FBQ0E7O0FBRUQ7QUFDQSxTQUFRLGtCQUFSLEVBQTZCLEVBQTdCLENBQWlDLE9BQWpDLEVBQTBDLFlBQVk7QUFDckQsVUFBUSxNQUFSLEVBQWlCLE9BQWpCLENBQTBCLHlCQUExQixFQUFxRCxFQUFFLGFBQWEsRUFBZixFQUFyRDtBQUNBLEdBRkQ7QUFHQSxFQXJFRDtBQXNFQSxDQXpFRCIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qIGdsb2JhbCB3cHNlb0FkbWluTDEwbiAqL1xuKCBmdW5jdGlvbigpIHtcblx0XCJ1c2Ugc3RyaWN0XCI7XG5cblx0alF1ZXJ5KCBkb2N1bWVudCApLnJlYWR5KCBmdW5jdGlvbigpIHtcblxuXHRcdGlmICggdHlwZW9mKCBIUyApICE9PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdGpRdWVyeSggd2luZG93ICkub24oICdZb2FzdFNFTzpDb250YWN0U3VwcG9ydCcsIGZ1bmN0aW9uKCBlLCBkYXRhICkge1xuXHRcdFx0XHRpZiAoIGRhdGEudXNlZFF1ZXJpZXMgIT0gdW5kZWZpbmVkKXtcblx0XHRcdFx0XHR2YXIgaWRlbnRpdHkgPSBIUy5iZWFjb24uZ2V0X2hlbHBzY291dF9iZWFjb25faWRlbnRpdHkoKTtcblx0XHRcdFx0XHRpZGVudGl0eVsgJ1VzZXIgc2VhcmNoZWQgZm9yJyBdID0gdXNlZFF1ZXJpZXNXaXRoSFRNTCggZGF0YS51c2VkUXVlcmllcyApO1xuXHRcdFx0XHRcdEhTLmJlYWNvbi5pZGVudGlmeShpZGVudGl0eSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0alF1ZXJ5KCAnI3dwc2VvLWNvbnRhY3Qtc3VwcG9ydC1wb3B1cCcgKS5oaWRlKCk7XG5cdFx0XHRcdEhTLmJlYWNvbi5vcGVuKCk7XG5cdFx0XHR9KTtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBGb3JtYXQgdGhlIHNlYXJjaCBxdWVyaWVzIGRvbmUgYnkgdGhlIHVzZXIgaW4gSFRNTC5cblx0XHQgKlxuXHRcdCAqIEBwYXJhbSB7YXJyYXl9IHVzZWRRdWVyaWVzIExpc3Qgb2YgcXVlcmllcyBlbnRlcmVkIGJ5IHRoZSB1c2VyLlxuXHRcdCAqIEByZXR1cm5zIHtzdHJpbmd9IFRhYmxlIGNvbnRhaW5pbmcgbGluayB0byBwb3N0cy5cblx0XHQgKi9cblx0XHRmdW5jdGlvbiB1c2VkUXVlcmllc1dpdGhIVE1MKCB1c2VkUXVlcmllcyApIHtcblx0XHRcdHZhciBvdXRwdXQgPSAnJztcblxuXHRcdFx0aWYgKCBqUXVlcnkuaXNFbXB0eU9iamVjdCggdXNlZFF1ZXJpZXMgKSApIHtcblx0XHRcdFx0cmV0dXJuICc8ZW0+U2VhcmNoIGhpc3RvcnkgaXMgZW1wdHkuPC9lbT4nO1xuXHRcdFx0fVxuXG5cdFx0XHRvdXRwdXQgKz0gJzx0YWJsZT48dHI+PHRoPlNlYXJjaGVkIGZvcjwvdGg+PHRoPk9wZW5lZCBhcnRpY2xlPC90aD48L3RyPic7XG5cblx0XHRcdGpRdWVyeS5lYWNoKCB1c2VkUXVlcmllcywgZnVuY3Rpb24oIHNlYXJjaFN0cmluZywgcG9zdHMgKSB7XG5cdFx0XHRcdG91dHB1dCArPSBcIjx0cj48dGQ+XCIgKyBzZWFyY2hTdHJpbmcgKyBcIjwvdGQ+XCI7XG5cdFx0XHRcdG91dHB1dCArPSBnZXRQb3N0c0hUTUwoIHBvc3RzICk7XG5cdFx0XHRcdG91dHB1dCArPSBcIjwvdHI+XCI7XG5cdFx0XHR9KTtcblxuXHRcdFx0b3V0cHV0ID0gb3V0cHV0ICsgXCI8L3RhYmxlPlwiO1xuXG5cdFx0XHRyZXR1cm4gb3V0cHV0O1xuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEZvcm1hdCB0aGUgcG9zdHMgbG9va2VkIGF0IGJ5IHRoZSB1c2VyIGluIEhUTUwuXG5cdFx0ICpcblx0XHQgKiBAcGFyYW0ge2FycmF5fSBwb3N0cyBMaXN0IG9mIHBvc3RzIG9wZW5lZCBieSB0aGUgdXNlci5cblx0XHQgKiBAcmV0dXJucyB7c3RyaW5nfSBUYWJsZSBjb250YWluaW5nIGxpbmtzIHRvIHBvc3RzLlxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGdldFBvc3RzSFRNTCggcG9zdHMgKSB7XG5cdFx0XHR2YXIgb3V0cHV0ID0gJyc7XG5cdFx0XHR2YXIgZmlyc3QgPSB0cnVlO1xuXG5cdFx0XHRpZiAoIGpRdWVyeS5pc0VtcHR5T2JqZWN0KCBwb3N0cyApICkge1xuXHRcdFx0XHRyZXR1cm4gXCI8dGQ+PGVtPk5vIGFydGljbGVzIHdlcmUgb3BlbmVkLjwvZW0+PC90ZD5cIjtcblx0XHRcdH1cblxuXHRcdFx0alF1ZXJ5LmVhY2goIHBvc3RzLCBmdW5jdGlvbiggcG9zdElkLCBwb3N0ICkge1xuXHRcdFx0XHRpZiAoIGZpcnN0ID09PSBmYWxzZSApIHtcblx0XHRcdFx0XHRvdXRwdXQgKz0gXCI8dGQ+PC90ZD5cIjtcblx0XHRcdFx0fVxuXHRcdFx0XHRvdXRwdXQgKz0gXCI8dGQ+PGEgaHJlZj0nXCIgKyBwb3N0LmxpbmsgKyBcIic+XCIgKyBwb3N0LnRpdGxlICsgXCI8L2E+PC90ZD5cIjtcblx0XHRcdFx0Zmlyc3QgPSBmYWxzZTtcblx0XHRcdH0pO1xuXG5cdFx0XHRyZXR1cm4gb3V0cHV0O1xuXHRcdH1cblxuXHRcdC8vIEdldCB0aGUgdXNlZCBzZWFyY2ggc3RyaW5ncyBmcm9tIHRoZSBhbGdvbGlhU2VhcmNoZXIgUmVhY3QgY29tcG9uZW50IGZvciB0aGUgYWN0aXZlIHRhYiBhbmQgZmlyZSBhbiBldmVudCB3aXRoIHRoaXMgZGF0YS5cblx0XHRqUXVlcnkoIFwiLmNvbnRhY3Qtc3VwcG9ydFwiICkub24oIFwiY2xpY2tcIiwgZnVuY3Rpb24gKCkge1xuXHRcdFx0alF1ZXJ5KCB3aW5kb3cgKS50cmlnZ2VyKCBcIllvYXN0U0VPOkNvbnRhY3RTdXBwb3J0XCIsIHsgdXNlZFF1ZXJpZXM6IHt9IH0gKTtcblx0XHR9ICk7XG5cdH0gKTtcbn0pKCk7XG4iXX0=
