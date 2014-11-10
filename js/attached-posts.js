/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
(function(window, document, $, undefined) {

	var app = {};

	app.cache = function() {
		app.$ = {};
		app.$.retrievedPosts     = $( document.getElementById( 'retrieved' ) );
		app.$.retrievedPostsItem = app.$.retrievedPosts.find( 'li' );
		app.$.attachedPosts      = $( document.getElementById( 'attached' ) );
		app.$.attachedPostsItem  = app.$.attachedPosts.find( 'li' );
	};

	app.init = function() {
		app.cache();

		// Allow the user to drag items from the left list
		app.$.retrievedPostsItem.draggable({
			helper: 'clone',
			revert: 'invalid',
			stack: '#retrieved li',
			stop: app.replacePlusIcon,
		});

		// Allow the right list to be droppable and sortable
		app.$.attachedPosts.droppable({
			accept: '#retrieved li',
			drop: function(event, ui) {
				app.buildItems( ui.draggable );
			},
			over: function(event, ui){
				app.attachedAddHiddenField( ui.draggable );
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
			var listItemID = $(this).attr('id');

			// Add the ID to our array
			itemArray.push(listItemID);
		});

		// If our item is not in our post ID array, stop everything
		if($.inArray(itemID, itemArray) !== -1)
			return;

		// If we can continue, do so
		item.clone().appendTo(app.$.attachedPosts);
	};

	// Add a hidden field when our posts are added.
	// This saves the ID as post meta
	app.attachedAddHiddenField = function(item){

		// Get our dragged item ID
		var itemID = item.context && item.context.id ? item.context.id : null;

		// Add the 'added' class to our retrieved column when clicked
		app.$.attachedPosts.find( '#' + itemID ).addClass('added');

		// Add our hidden input
		setTimeout(function(){
			$('<input type="hidden" name="_attached_cmb2_attached_posts[]" value="' + itemID + '">').appendTo( app.$.attachedPosts.find( '#' + itemID ) );
		},1000);
	};

	// Add the items when the plus icon is clicked
	app.addPostToColumn = function(){

		if ($(this).parent().hasClass('added'))
			return;

		// Add the 'added' class when clicked
		$(this).parent().addClass('added');

		// Get the clicked item's ID
		// Start our array
		var itemID    = $(this).parent().attr('id'),
			itemArray = [];

		// Don't add the item if an item with this ID exists already
		app.$.attachedPostsItem.each(function() {

			// Get our list item ID
			var listItemID = $(this).attr('id');

			// Add the ID to our array
			itemArray.push(listItemID);
		});

		// If our item is not in our post ID array, stop everything
		if($.inArray(itemID, itemArray) !== -1)
			return;

		// Add the item to the right list
		$(this).parent().clone().appendTo( app.$.attachedPosts.not( $(this).closest('ul') ) );

		// Add our hidden input field
		$('<input type="hidden" name="_attached_cmb2_attached_posts[]" value="' + itemID + '">').appendTo( app.$.attachedPosts.find( '#' + itemID ) );

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();
	};

	// Remove items from our attached list when the minus icon is clicked
	app.removePostFromColumn = function(){

		// Get the clicked item's ID
		var itemID = $(this).parent().attr('id');

		// Remove the list item
		$(this).parent().remove();

		// Remove the 'added' class from the retrieved column
		app.$.attachedPosts.find( '#' + itemID ).removeClass('added');
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function(){

		$( '#attached li .dashicons' ).removeClass( 'dashicons-plus' );
		$( '#attached li .dashicons' ).addClass( 'dashicons-minus' );

	};

	jQuery(document).ready( app.init );

	return app;

})(window, document, jQuery);
