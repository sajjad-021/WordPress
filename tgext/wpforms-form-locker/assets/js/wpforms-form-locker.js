/* globals wpforms_settings, wpforms_form_locker */
/**
 * WPForms Form Locker function.
 *
 * @since 1.0.0
 */
var WPFormsFormLocker = window.WPFormsFormLocker || ( function( document, window, $ ) {

	'use strict';

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$(document).ready(app.ready);
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.validation();
		},

		/**
		 * Register custom validation.
		 *
		 * @since 1.0.0
		 */
		validation: function() {

			// Only load if jQuery validation library exists
			if ( typeof $.fn.validate === 'undefined' ) {
				return;
			}

			$.validator.addMethod( "unique", function ( value, element, param, method ) {
				// This code is copied from JQuery Validate 'remote' method with several changes:
				// - 'data' variable is not empty;
				// - 'url' and 'type' parameters are added to $.ajax() call.
				if ( this.optional( element ) ) {
					return 'dependency-mismatch';
				}

				method = typeof method === 'string' && method || 'unique';

				var previous = this.previousValue( element, method ),
					validator, data, optionDataString;

				if ( !this.settings.messages[ element.name ] ) {
					this.settings.messages[ element.name ] = {};
				}
				previous.originalMessage = previous.originalMessage || this.settings.messages[ element.name ][ method ];
				this.settings.messages[ element.name ][ method ] = previous.message;

				param = typeof param === 'string' && { url: param } || param;
				optionDataString = $.param( $.extend( { data: value }, param.data ) );
				if ( previous.old === optionDataString ) {
					return previous.valid;
				}

				previous.old = optionDataString;
				validator = this;
				this.startRequest( element );
				data = {
					'action': 'wpforms_form_locker_unique_answer',
					'form_id': $(element).closest('.wpforms-form').data('formid'),
					'field_id': $(element).closest('.wpforms-field').data('field-id')
				};
				data[ element.name ] = value;
				$.ajax( $.extend( true, {
					url: wpforms_form_locker.ajaxurl,
					type: 'post',
					mode: 'abort',
					port: 'validate' + element.name,
					dataType: 'json',
					data: data,
					context: validator.currentForm,
					success: function( response ) {
						var valid = response === true || response === 'true',
							errors, message, submitted;

						validator.settings.messages[ element.name ][ method ] = previous.originalMessage;
						if ( valid ) {
							submitted = validator.formSubmitted;
							validator.resetInternals();
							validator.toHide = validator.errorsFor( element );
							validator.formSubmitted = submitted;
							validator.successList.push( element );
							validator.invalid[ element.name ] = false;
							validator.showErrors();
						} else {
							errors = {};
							message = response || validator.defaultMessage( element, { method: method, parameters: value } );
							errors[ element.name ] = previous.message = message;
							validator.invalid[ element.name ] = true;
							validator.showErrors( errors );
						}
						previous.valid = valid;
						validator.stopRequest( element, valid );
					}
				}, param ) );
				return 'pending';
			}, wpforms_settings.val_unique );
		}
	};

	// Provide access to public functions/properties.
	return app;

})( document, window, jQuery );

// Initialize.
WPFormsFormLocker.init();