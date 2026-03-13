'use strict';

class AJAX{

	url = ''; // Data to send
	data = null;
	// Optional params
	button       = null;
	spinner      = null;
	progressbar  = null;
	obj          = null;
	context      = this;
	type         = 'POST';
	dataType     = 'json';
	timeout      = 30000;
	
	constructor( params ) {
		for( let key in params ){
			if( typeof this[key] !== 'undefined' ){
				this[key] = params[key];
			}
		}
	}

	successCallback( response, ajax ){
		alert( response.message );
	}

	errorCallback( response, ajax ){
		alert( response.message );
	}
	
	/**
	 *
	 * @param response
	 */
	success( response ){
		if( ! response.status ){
			this.errorCallback( response, this );
		}else{
			this.successCallback( response, this );
		}
	}

	disableInput(){
		if( this.button ){
			this.button.attr('disabled', 'disabled');
			this.button.css('cursor', 'not-allowed');
		}
	}

	enableInput(){
		if( this.button ){
			this.button.removeAttr('disabled');
			this.button.css('cursor', 'pointer');
		}
	}

	showSpinner(){
		if( this.spinner && typeof this.spinner === 'function' ) this.spinner();
		if( this.spinner && typeof this.spinner === 'object' )   this.spinner.css('display', 'inline');
	}

	hideSpinner(){
		if( this.spinner && typeof this.spinner === 'function' ) this.spinner();
		if( this.spinner && typeof this.spinner === 'object' )   this.spinner.css('display', 'none');
	}

	/**
	 * Request is complete
	 */
	complete(){
		this.enableInput();
		this.hideSpinner();
	}

	error( xhr, status, error ){

		console.log( '%c AJAX_ERROR', 'color: red;' );
		console.log( status );
		console.log( error );

		if( xhr.status === 200 ){
			if( status === 'parsererror' ){
				this.errorOutput( 'Unexpected response from server. See console for details.' );
				console.log( '%c ' + xhr.responseText, 'color: pink;' );
			}else {
				var errorString = 'Unexpected error: ' + status;
				if( typeof error !== 'undefined' ) {
					errorString += ' Additional info: ' + error;
				}
				this.errorOutput( errorString );
			}
		}else if(xhr.status === 500){
			this.errorOutput( 'Internal server error.');
		}else {
			this.errorOutput('Unexpected response code:' + xhr.status);
		}
		
		if( this.progressbar ) {
			this.progressbar.fadeOut('slow');
		}
	}

	errorOutput( msg ){
		jQuery('.alert-danger').show(300);
		jQuery('#error-msg').text( msg );
	}

	call(){
		
		this.disableInput(); // Disable button
		this.showSpinner();   // Show spinner

		jQuery.ajax({
			data: this.data,
			url     : this.url,
			type    : this.type,
			context : this.context,
			dataType: this.dataType,
			timeout : this.timeout,
			success : this.success,
			complete: this.complete,
			error   : this.error,
		});

	}
}

function doAJAX( params ){
	new AJAX( params ).call();
}