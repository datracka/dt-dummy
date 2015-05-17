(function( $ ) {
	'use strict';

	$(function() {
		var xhr = false;

		var getDummyID = function( $container ) {
			var dummyID = new Array();
			$('input[type="checkbox"]:checked', $container).each(function(){
				dummyID.push( $(this).attr('name') );
			});

			var attachmentsIndex = dummyID.indexOf('attachments');
			if ( attachmentsIndex >= 0 ) {
				// move attachments to the end of array
				dummyID.splice(attachmentsIndex, 1);
				dummyID.push('attachments');
			}

			return dummyID.join(',');
		}

		var getDummyUser = function( $container ) {
			return $('.dt-dummy-content-user', $container).first().val();
		}

		var ajaxImportDummy = function( dummyID, options ) {
			if ( ! dummyID || dummyID.length <= 0 ) {

				if ( typeof(options.onLastCall) == 'function' ) {
					options.onLastCall();
				}

				return false;
			}

			var glue = ',';

			dummyID = dummyID.split(glue);
			var currentDummyId = dummyID.shift();
			dummyID = dummyID.join(glue);

			var ajaxData = {
				action: 'presscore_import_dummy',
				dummy: currentDummyId
			}

			if ( typeof( options.ajaxData ) == 'object' ) {
				$.extend( ajaxData, options.ajaxData );
			}

			var xhr;
			xhr = $.post(
				ajaxurl,
				ajaxData
			).success( function( response ) {

				if ( typeof(options.onSuccessResponse) == 'function' ) {
					options.onSuccessResponse(response, dummyID, options);
				}

			} );

			return xhr;
		}

		$('.dt-dummy-control-buttons .dt-dummy-button-import').on('click', function(event) {
			event.preventDefault();

			if ( xhr ) {
				return false;
			}

			var mesageContainerClass = 'dt-alertify-response';
			var dummySpinner = '<span class="dt-dummy-spinner"></span>';

			var $this = $(this);
			var $spinner = $this.siblings('.spinner').first();
			$spinner.show();

			var $alertContainer = $('#alertify');
			var $blockContainer = $this.parents('.dt-dummy-controls').first();
			var contentPartId = $blockContainer.attr( 'data-dt-dummy-content-part-id' ) || '0';

			xhr = ajaxImportDummy(
				getDummyID($blockContainer),
				{
					ajaxData: {
						imported_authors: ['admin'],
						user_map: [getDummyUser($blockContainer)],
						content_part_id: contentPartId
					},
					onLastCall: function() {
						$alertContainer.removeClass('dt-dummy-alertify-loading');
						$spinner.hide();
					},
					onSuccessResponse: function( response, dummyID, options ) {

						if ( $alertContainer.length <= 0 || $alertContainer.hasClass('alertify-hidden') ) {
							alertify.alert('<div class="' + mesageContainerClass + '">' + response + '</div>' + dummySpinner);
							$alertContainer = $('#alertify').addClass('dt-dummy-alertify-loading');
						} else {
							$alertContainer.find('.' + mesageContainerClass).prepend( response );
						}

						xhr = ajaxImportDummy(dummyID, options);
					}
				}
			);

			return false;
		});

	});

})( jQuery );
