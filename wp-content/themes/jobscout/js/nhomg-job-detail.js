/**
 * NhomG - Job Detail: nút Share và lightbox ảnh công ty.
 */
( function () {
	'use strict';

	var i18n = window.nhomgJob || { copied: 'Link copied!' };

	/* ---------- Share ---------- */
	var toggle = document.querySelector( '.nhomg-share__toggle' );
	var menu = document.getElementById( 'nhomg-share-menu' );

	function closeMenu() {
		if ( ! menu ) return;
		menu.hidden = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
	}

	if ( toggle && menu ) {
		toggle.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			var open = menu.hidden;
			menu.hidden = ! open;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! menu.contains( e.target ) ) closeMenu();
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) closeMenu();
		} );

		var copyBtn = menu.querySelector( '.nhomg-share__copy' );
		if ( copyBtn ) {
			copyBtn.addEventListener( 'click', function () {
				var url = copyBtn.getAttribute( 'data-url' );
				var label = copyBtn.textContent;
				var done = function () {
					copyBtn.textContent = i18n.copied;
					setTimeout( function () { copyBtn.textContent = label; closeMenu(); }, 1200 );
				};
				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( url ).then( done );
				} else {
					var input = document.createElement( 'input' );
					input.value = url;
					document.body.appendChild( input );
					input.select();
					document.execCommand( 'copy' );
					input.remove();
					done();
				}
			} );
		}
	}

	/* ---------- Lightbox ---------- */
	var items = Array.prototype.slice.call( document.querySelectorAll( '.nhomg-photos__item' ) );
	if ( ! items.length ) return;

	var urls = items.map( function ( a ) { return a.getAttribute( 'href' ); } );
	var box, img, count, current = 0;

	function show( index ) {
		current = ( index + urls.length ) % urls.length;
		img.src = urls[ current ];
		count.textContent = ( current + 1 ) + ' / ' + urls.length;
	}

	function onKey( e ) {
		if ( 'Escape' === e.key ) close();
		if ( 'ArrowLeft' === e.key ) show( current - 1 );
		if ( 'ArrowRight' === e.key ) show( current + 1 );
	}

	function close() {
		box.remove();
		document.removeEventListener( 'keydown', onKey );
	}

	function open( index ) {
		box = document.createElement( 'div' );
		box.className = 'nhomg-lightbox';
		box.setAttribute( 'role', 'dialog' );
		box.setAttribute( 'aria-modal', 'true' );
		box.innerHTML =
			'<img alt="" />' +
			'<button type="button" class="nhomg-lightbox__close" aria-label="Close">&times;</button>' +
			( urls.length > 1
				? '<button type="button" class="nhomg-lightbox__prev" aria-label="Previous">&#8249;</button>' +
				  '<button type="button" class="nhomg-lightbox__next" aria-label="Next">&#8250;</button>'
				: '' ) +
			'<span class="nhomg-lightbox__count"></span>';
		img = box.querySelector( 'img' );
		count = box.querySelector( '.nhomg-lightbox__count' );

		box.addEventListener( 'click', function ( e ) {
			var t = e.target;
			if ( t === box || t.classList.contains( 'nhomg-lightbox__close' ) ) close();
			else if ( t.classList.contains( 'nhomg-lightbox__prev' ) ) show( current - 1 );
			else if ( t.classList.contains( 'nhomg-lightbox__next' ) ) show( current + 1 );
		} );
		document.addEventListener( 'keydown', onKey );
		document.body.appendChild( box );
		show( index );
		box.querySelector( '.nhomg-lightbox__close' ).focus();
	}

	items.forEach( function ( a, i ) {
		a.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			open( i );
		} );
	} );
} )();
