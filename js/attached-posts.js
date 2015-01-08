/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
(function(window, document, $, undefined) {

	var app = {
		postIds : []
	};

	app.cache = function() {
		app.$ = {};
		app.$.wrap               = $( '.attached-posts-wrap' );
		app.$.postIds            = $( '.attached-posts-ids' );
		app.$.retrievedPosts     = app.$.wrap.find( '.retrieved' );
		app.$.retrievedPostsItem = app.$.retrievedPosts.find( 'li' );
		app.$.attachedPosts      = app.$.wrap.find( '.attached' );
		app.$.attachedPostsItem  = app.$.attachedPosts.find( 'li' );
	};

	app.init = function() {
		app.cache();

		// Allow the user to drag items from the left list
		app.$.retrievedPostsItem.draggable({
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

		// Add posts when the plus icon is clicked
		app.$.retrievedPosts.on( 'click', '.add-remove', app.addPostToColumn);

		// Remove posts when the minus icon is clicked
		app.$.attachedPosts.on( 'click', '.add-remove', app.removePostFromColumn);
	};

	// Clone our dragged item
	app.buildItems = function( item ) {

		var $li       = $( item );
		var $wrap     = $li.parents( '.attached-posts-wrap' );
		// Get the ID of the item being dragged
		var itemID    = item[0].attributes[0].value;

		// If our item is in our post ID array, stop everything
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class to our retrieved column when clicked
		app.$.retrievedPosts.find( '[data-id="'+ itemID +'"]' ).addClass( 'added' );

		item.clone().appendTo( app.$.attachedPosts );

		app.resetWrapItems( $wrap );
	};

	// Add the items when the plus icon is clicked
	app.addPostToColumn = function() {

		var $this  = $(this);
		var $li    = $this.parent();
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.attached-posts-wrap' );

		if ( $li.hasClass( 'added' ) ) {
			return;
		}

		// If our item is in our post ID array, stop everything
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class when clicked
		$li.addClass( 'added' );

		// Add the item to the right list
		$wrap.find( '.attached' ).append( $li.clone() );

		app.resetWrapItems( $wrap );

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();
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
		app.$.retrievedPosts.find( '[data-id="' + itemID +'"]' ).removeClass( 'added' );

		app.resetWrapItems( $wrap );
	};

	app.inputHasId = function( $wrap, itemID ) {
		var $input  = app.getPostIdsInput( $wrap.data( 'fieldname' ) );
		// Get array
		var postIds = app.getPostIdsVal( $input );
		// Get the ID of the item being dragged

		// If our item is in our post ID array, stop everything
		return $.inArray( itemID, postIds) !== -1;
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function() {
		$( '.attached li .dashicons.dashicons-plus' ).removeClass( 'dashicons-plus' ).addClass( 'dashicons-minus' );
	};

	app.getPostIdsInput = function( fieldName ) {
		return app.$.postIds.filter( '[name="'+ fieldName +'"]' );
	};

	app.getPostIdsVal = function( $input ) {
		var val = $input.val();
		return val ? val.split( ',' ) : [];
	};

	app.resetWrapItems = function( $wrap ) {
		var $input = app.getPostIdsInput( $wrap.data( 'fieldname' ) );
		var newVal = [];

		$wrap.find( '.attached li' ).each( function( index ) {
			var zebraClass = 0 === index % 2 ? 'odd' : 'even';
			newVal.push( $(this).attr( 'class', zebraClass + ' ui-sortable-handle' ).data( 'id' ) );
		});

		$input.val( newVal.join( ',' ) );
	};

	// Re-order items when items are dragged
	app.resetItems = function( item ) {
		var $li = $( item );
		app.resetWrapItems( $li.parents( '.attached-posts-wrap' ) );
	};

	jQuery(document).ready( app.init );

	return app;

})(window, document, jQuery);
