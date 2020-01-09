/* globals wpforms_admin_builder_form_locker */
/**
 * WPForms Builder Form Locker function.
 *
 * @since 1.0.0
 */
var WPFormsBuilderFormLocker = window.WPFormsBuilderFormLocker || ( function( document, window, $ ) {

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

			$( document ).ready( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {
			app.events();
			app.conditionals();
			app.dateTimePicker();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.0.0
		 */
		events: function() {
			$( '.wpforms-panel-field-datetime .wpforms-clear-datetime-field' ).click( function() {
				var $input = $( this ).siblings( 'input' );
				if ( $input.prop( '_flatpickr' ) ) {
					$input.prop( '_flatpickr' ).clear();
				} else {
					$input.val( '' );
				}
				$( this ).hide();
			} );
		},

		/**
		 * Register and load conditionals.
		 *
		 * @since 1.0.0
		 */
		conditionals: function() {

			var elements = [
				{
					id: '#wpforms-panel-field-settings-form_locker_password_enable',
					hides: '#wpforms-panel-field-settings-form_locker_password-wrap,#wpforms-panel-field-settings-form_locker_password_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_schedule_enable',
					hides: '#wpforms-form-locker-schedule-datetime-block,#wpforms-panel-field-settings-form_locker_schedule_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_entry_limit_enable',
					hides: '#wpforms-panel-field-settings-form_locker_entry_limit-wrap,#wpforms-panel-field-settings-form_locker_entry_limit_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_user_enable',
					hides: '#wpforms-panel-field-settings-form_locker_user_message-wrap',
				},
			];

			if ( typeof $.fn.conditions !== 'undefined' ) {
				$.each( elements, function( index, element ) {
					$( element.id ).conditions( {
						conditions: {
							element : element.id,
							type    : 'checked',
							operator: 'is',
						},
						actions   : {
							if  : {
								element: element.hides,
								action : 'show',
							},
							else: {
								element: element.hides,
								action : 'hide',
							},
						},
						effect    : 'appear',
					} );
				} );
			}
		},

		dateTimePicker: function() {
			app.datePicker();
			app.timePicker();
			app.datePair();
			app.dateTimeClearBtnsInit();
		},

		datePicker: function() {
			if ( 'undefined' === typeof $.fn.flatpickr ) {
				return;
			}

			function getArgs( field ) {
				var $tpicker = $( '#wpforms-panel-field-settings-form_locker_schedule_' + field + '_time' ),
					$dpicker = $( '#wpforms-panel-field-settings-form_locker_schedule_' + field + '_date' ),
					args = {
						altInput: true,
						altFormat: wpforms_admin_builder_form_locker.date_format,
						dateFormat: 'Y-m-d',
					};

				args.onChange = function( dateObj, dateStr ) {
					if ( '' === $tpicker.val() && '' !== $dpicker.val() ) {
						$tpicker
							.timepicker( 'setTime', new Date( dateObj ) )
							.nextAll( 'button.wpforms-clear-datetime-field' )
							.show();
					}
					if ( '' !== $dpicker.val() ) {
						$dpicker
							.nextAll( 'button.wpforms-clear-datetime-field' )
							.show();
					}
				};

				return args;
			}

			$( '#wpforms-panel-field-settings-form_locker_schedule_start_date' ).flatpickr( getArgs( 'start' ) );
			$( '#wpforms-panel-field-settings-form_locker_schedule_end_date' ).flatpickr( getArgs( 'end' ) );
		},

		timePicker: function() {
			if ( 'undefined' === typeof $.fn.timepicker ) {
				return;
			}

			var args = {
					appendTo: $( '#wpforms-builder' ),
					disableTextInput: true,
					timeFormat: wpforms_admin_builder_form_locker.time_format,
				},
				$startTime = $( '#wpforms-panel-field-settings-form_locker_schedule_start_time' ),
				$endTime   = $( '#wpforms-panel-field-settings-form_locker_schedule_end_time' );

			function onSelectTime() {
				if ( '' !== $( this ).val() ) {
					$( this ).nextAll( '.wpforms-clear-datetime-field' ).show();
				}
			}

			$startTime.timepicker( args ).on( 'selectTime', onSelectTime );
			$endTime.timepicker( args ).on( 'selectTime', onSelectTime );
		},

		datePair: function() {
			var args = {
				anchor: null,
				defaultDateDelta: null,
				defaultTimeDelta: null,
				dateClass: 'wpforms-datepair-date',
				timeClass: 'wpforms-datepair-time',
				startClass: 'wpforms-datepair-start',
				endClass: 'wpforms-datepair-end',
				parseDate: function( input ) {
					return $( input ).prop( '_flatpickr' ).selectedDates[0];
				},
				updateDate: function( input, dateObj ) {
					return $( input ).prop( '_flatpickr' ).setDate( dateObj );
				},
			};
			$( '#wpforms-form-locker-schedule-datetime-block' ).datepair( args );
		},

		dateTimeClearBtnsInit: function() {
			$( '#wpforms-form-locker-schedule-datetime-block .wpforms-clear-datetime-field' )
				.each( function() {
					var $t = $( this );
					if ( '' === $t.siblings( '[id^="wpforms-panel-field-settings-form_locker_schedule_"]' ).val() ) {
						$t.hide();
					}
				} );
		},
	};

	// Provide access to public functions/properties.
	return app;

} )( document, window, jQuery );

// Initialize.
WPFormsBuilderFormLocker.init();
