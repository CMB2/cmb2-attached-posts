/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
window.CMBAP = window.CMBAP || (function(window, document, $, undefined) {

	var app = { $ : {} };

	app.cache = function() {
		var $wrap                = $( '.attached-posts-wrap' );
		app.$.retrievedPosts     = $wrap.find( '.retrieved' );
		app.$.attachedPosts      = $wrap.find( '.attached' );
	};

	app.init = function() {
		app.cache();

		// Allow the user to drag items from the left list
		app.$.retrievedPosts.find( 'li' ).draggable({
			helper: 'clone',
			revert: 'invalid',
			stack: '.retrieved li',
			stop: app.replacePlusIcon,
		});

		// Allow the right list to be droppable and sortable
		app.$.attachedPosts.droppable({
			accept: '.retrieved li',
			drop: function(evt, ui) {
				app.buildItems( ui.draggable );
			}
		}).sortable({
			stop: function( evt, ui ) {
				app.resetItems( ui.item );
			}
		}).disableSelection();

		$( '.cmb2-wrap > .cmb2-metabox' )
			// Add posts when the plus icon is clicked
			.on( 'click', '.attached-posts-wrap .retrieved .add-remove', app.addPostToColumn )
			// Remove posts when the minus icon is clicked
			.on( 'click', '.attached-posts-wrap .attached .add-remove', app.removePostFromColumn )
			// Listen for search events
			.on( 'keyup', '.attached-posts-wrap input.search', app.handleSearch );

	};

	// Clone our dragged item
	app.buildItems = function( item ) {

		var $wrap  = $( item ).parents( '.attached-posts-wrap' );
		// Get the ID of the item being dragged
		var itemID = item[0].attributes[0].value;

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class to our retrieved column when clicked
		$wrap.find( '.retrieved li[data-id="'+ itemID +'"]' ).addClass( 'added' );

		item.clone().appendTo( $wrap.find( '.attached' ) );

		app.resetAttachedListItems( $wrap );
	};

	// Add the items when the plus icon is clicked
	app.addPostToColumn = function() {

		var $li    = $( this ).parent();
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.attached-posts-wrap' );

		if ( $li.hasClass( 'added' ) ) {
			return;
		}

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class when clicked
		$li.addClass( 'added' );

		// Add the item to the right list
		$wrap.find( '.attached' ).append( $li.clone() );

		app.resetAttachedListItems( $wrap );
	};

	// Remove items from our attached list when the minus icon is clicked
	app.removePostFromColumn = function() {

		// Get the clicked item's ID
		var $li    = $(this).closest( 'li' );
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.attached-posts-wrap' );

		// Remove the list item
		$(this).parent().remove();

		// Remove the 'added' class from the retrieved column
		$wrap.find('.retrieved li[data-id="' + itemID +'"]').removeClass('added');

		app.resetAttachedListItems( $wrap );
	};

	app.inputHasId = function( $wrap, itemID ) {
		var $input  = app.getPostIdsInput( $wrap );
		// Get array
		var postIds = app.getPostIdsVal( $input );
		// If our item is in our post ID array, stop everything
		return $.inArray( itemID, postIds) !== -1;
	};

	app.getPostIdsInput = function( $wrap ) {
		return $wrap.find('.attached-posts-ids');
	};

	app.getPostIdsVal = function( $input ) {
		var val = $input.val();
		return val ? val.split( ',' ) : [];
	};

	app.resetAttachedListItems = function( $wrap ) {
		var $input = app.getPostIdsInput( $wrap );
		var newVal = [];

		$wrap.find( '.attached li' ).each( function( index ) {
			var zebraClass = 0 === index % 2 ? 'odd' : 'even';
			newVal.push( $(this).attr( 'class', zebraClass + ' ui-sortable-handle' ).data( 'id' ) );
		});

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();

		$input.val( newVal.join( ',' ) );
	};

	// Re-order items when items are dragged
	app.resetItems = function( item ) {
		var $li = $( item );
		app.resetAttachedListItems( $li.parents( '.attached-posts-wrap' ) );
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function() {
		$( '.attached li .dashicons.dashicons-plus' ).removeClass( 'dashicons-plus' ).addClass( 'dashicons-minus' );
	};

	// Handle searching available list
	app.handleSearch = function( evt ) {

		var $this = $( evt.target );
		var searchQuery = $this.val() ? $this.val().toLowerCase() : '';

		$this.closest( '.column-wrap' ).find( 'ul.connected li' ).each( function() {
			var $el = $(this);

			if ( $el.text().toLowerCase().search( searchQuery ) > -1 ) {
				$el.show();
			} else {
				$el.hide();
			}
		} );

	};

	jQuery(document).ready( app.init );

	return app;

})(window, document, jQuery);
