/**
 * NhomG - chọn ảnh công ty trong meta box Job Detail.
 */
jQuery( function ( $ ) {
	var frame;

	$( '.nhomg-gallery-select' ).on( 'click', function ( e ) {
		e.preventDefault();
		var $input = $( '.nhomg-gallery-input' );
		var $preview = $( '.nhomg-gallery-preview' );

		if ( ! frame ) {
			frame = wp.media( {
				title: 'Company photos',
				button: { text: 'Use these photos' },
				library: { type: 'image' },
				multiple: 'add'
			} );

			frame.on( 'open', function () {
				var selection = frame.state().get( 'selection' );
				$input.val().split( ',' ).forEach( function ( id ) {
					id = parseInt( id, 10 );
					if ( id ) selection.add( wp.media.attachment( id ) );
				} );
			} );

			frame.on( 'select', function () {
				var ids = [];
				$preview.empty();
				frame.state().get( 'selection' ).each( function ( att ) {
					var data = att.toJSON();
					var thumb = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
					ids.push( data.id );
					$preview.append( $( '<img width="60" height="60" />' ).attr( 'src', thumb ) );
				} );
				$input.val( ids.join( ',' ) );
			} );
		}
		frame.open();
	} );

	$( '.nhomg-gallery-clear' ).on( 'click', function ( e ) {
		e.preventDefault();
		$( '.nhomg-gallery-input' ).val( '' );
		$( '.nhomg-gallery-preview' ).empty();
	} );
} );
