/**
 * Add the drag and drop and sort functionality to the Tiered Template admin
 */
(function(window, document, $, undefined) {

	$(function() {
		// Allow the user to drag items from the left list
		$('#retrieved li').draggable({
			helper: 'clone',
			revert: 'invalid',
			stack: '#retrieved li'
		});

		// Allow the right list to be droppable and sortable
		$('#attached').droppable({
			accept: '#retrieved li',
			drop: function(event, ui) {
				buildItem( ui.draggable );
			},
			over: function(event, ui){
				attachedAddHiddenField( ui.draggable );
			}
		}).sortable().disableSelection();

		 // Clone our dragged item
		function buildItem($item) {

			// Get the ID of the item being dragged
			$itemID = $item[0].attributes[0].value;

			// Start our array
			$itemArray = [];

			// Don't add the item if an item with this ID exists already
			$('#attached li').each(function() {

				// Get our list item ID
				$listItemID = $(this).attr('id');

				// Add the ID to our array
				$itemArray.push($listItemID);
			});

			// If our item is not in our post ID array, stop everything
			if($.inArray($itemID, $itemArray) !== -1)
				return;

			// If we can continue, do so
			$item.clone().appendTo('#attached');
		}

		// Add the items when the plus icon is clicked
		$('body').on('click', '#retrieved .add-remove', function(){

			if ($(this).parent().hasClass('added'))
				return;

			// Add the 'added' class when clicked
			$(this).parent().addClass('added');

			// Get the clicked item's ID
			$itemID = $(this).parent().attr('id');

			// Start our array
			$itemArray = [];

			// Don't add the item if an item with this ID exists already
			$('#attached li').each(function() {

				// Get our list item ID
				$listItemID = $(this).attr('id');

				// Add the ID to our array
				$itemArray.push($listItemID);
			});

			// If our item is not in our post ID array, stop everything
			if($.inArray($itemID, $itemArray) !== -1)
				return;

			// Add the item to the right list
			$(this).parent().clone().appendTo($('#attached').not($(this).closest('ul')));

			// Add our hidden input field
			$('<input type="hidden" name="_attached_posts[]" value="' + $itemID + '">').appendTo('#attached #' + $itemID );
		});

		// Remove items from our attached list when the minus icon is clicked
		$('body').on('click', '#attached .add-remove', function(){

			// Get the clicked item's ID
			$itemID = $(this).parent().attr('id');

			// Remove the list item
			$(this).parent().remove();

			// Remove the 'added' class from the retrieved column
			$('#retrieved #' + $itemID).removeClass('added');
		});

		// Add a hidden field when our posts are added.
		// This saves the ID as post meta
		function attachedAddHiddenField($item){

			// Get our dragged item ID
			$itemID = $item['context'].id;

			// Add the 'added' class to our retrieved column when clicked
			$('#retrieved #' + $itemID).addClass('added');

			// Add our hidden input
			setTimeout(function(){
				$('<input type="hidden" name="_attached_posts[]" value="' + $itemID + '">').appendTo('#attached #' + $itemID );
			},1000);
		}

	});

})(window, document, jQuery);
