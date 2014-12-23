/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
(function(window, document, $, undefined) {

	var app = {};

	app.cache = function() {
		app.$ = {};
		app.$.wrap               = $( '.attached-posts-wrap' );
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
			drop: function(event, ui) {
				app.buildItems( ui.draggable );
			}
		}).sortable().disableSelection();

		// Add posts when the plus icon is clicked
		app.$.retrievedPosts.on('click', '.add-remove', app.addPostToColumn);

		// Remove posts when the minus icon is clicked
		app.$.attachedPosts.on('click', '.add-remove', app.removePostFromColumn);
	};

	// Clone our dragged item
	app.buildItems = function(item) {

		// Get the ID of the item being dragged
		// Start our array
		var itemID    = item[0].attributes[0].value,
			itemArray = [];

		// Don't add the item if an item with this ID exists already
		app.$.attachedPostsItem.each(function() {

			// Get our list item ID
			var listItemID = $(this).data( 'id' );

			// Add the ID to our array
			itemArray.push( listItemID );
		});

		// If our item is not in our post ID array, stop everything
		if($.inArray(itemID, itemArray) !== -1)
			return;

		// If we can continue, do so
		item.clone().appendTo(app.$.attachedPosts);
		app.attachedAddHiddenField( item );
	};

	// Add a hidden field when our posts are added.
	// This saves the ID as post meta
	app.attachedAddHiddenField = function(item){

		var $li = $( item );
		var $wrap = $li.parents( '.attached-posts-wrap' );
		// Get our dragged item ID
		var itemID = $li.data( 'id' );
		var fieldName = $wrap.data( 'fieldname' );

		console.log( 'itemID', itemID );
		console.log( 'fieldName', fieldName );
		if ( ! itemID ) {
			return;
		}

		// Add the 'added' class to our retrieved column when clicked
		app.$.retrievedPosts.find( '[data-id="'+ itemID +'"]' ).addClass('added');

		// Add our hidden input
		$wrap.append( '<input type="hidden" name="'+ fieldName +'[]" value="' + itemID + '">' );
	};

	// Add the items when the plus icon is clicked
	app.addPostToColumn = function(){

		var $this = $(this);
		var $li = $this.parent();
		var $wrap = $li.parents( '.attached-posts-wrap' );
		if ($li.hasClass('added'))
			return;

		// Add the 'added' class when clicked
		$li.addClass('added');

		var fieldName = $wrap.data( 'fieldname' );

		// Get the clicked item's ID
		// Start our array
		var itemID = $li.data( 'id' );
		var itemArray = [];

		// Don't add the item if an item with this ID exists already
		app.$.attachedPostsItem.each(function() {

			// Get our list item ID
			var listItemID = $this.data( 'id' );

			// Add the ID to our array
			itemArray.push(listItemID);
		});

		// If our item is not in our post ID array, stop everything
		if( $.inArray( itemID, itemArray ) !== -1 ) {
			return;
		}

		// Add the item to the right list
		$wrap.find( '.attached' ).append( $li.clone() );

		// Add our hidden input field
		$wrap.append( '<input type="hidden" name="'+ fieldName +'[]" value="' + itemID + '">' )

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();
	};

	// Remove items from our attached list when the minus icon is clicked
	app.removePostFromColumn = function(){

		// Get the clicked item's ID
		var $li = $(this).closest( 'li' );
		var itemID = $li.data( 'id' );
		var $wrap = $li.parents( '.attached-posts-wrap' );

		// Remove the list item
		$(this).parent().remove();

		// Remove the 'added' class from the retrieved column
		app.$.retrievedPosts.find( '[data-id="' + itemID +'"]' ).removeClass( 'added' );
		$wrap.find( '[value="'+ itemID +'"]' ).remove();
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function(){

		$( '.attached li .dashicons' ).removeClass( 'dashicons-plus' );
		$( '.attached li .dashicons' ).addClass( 'dashicons-minus' );

	};

	jQuery(document).ready( app.init );

	return app;

})(window, document, jQuery);
