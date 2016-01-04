CMB2 Attached Posts Field
==================

Custom field for [CMB2](https://github.com/WebDevStudios/CMB2).

The post IDs are saved in an array, which can be rearranged by dragging and dropping posts in the attached posts column.

If the ability to search for a post and attach it is more in line with what you need, you might consider [CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field) instead.

## Installation

Follow the example in [`example-field-setup.php`](https://github.com/WebDevStudios/cmb2-attached-posts/blob/master/example-field-setup.php) for a demonstration. The example assumes you have both CMB2 and this extension in your mu-plugins directory. If you're using CMB2 installed as a plugin, remove [lines 6-9 of the example](https://github.com/WebDevStudios/cmb2-attached-posts/blob/master/example-field-setup.php#L6-L9).

## Customization
The example demonstrates how to modify the `get_posts` query args, and allows you to toggle the thumbnails display as well as a filter search input.

## Usage
You can retrieve the meta data using the following:

```php
$attached = get_post_meta( get_the_ID(), '_attached_cmb2_attached_posts', true );
```

This will return an array of attached post IDs. You can loop through those post IDs like the following example:

```php
foreach ( $attached as $attached_post ) {
	$post = get_post( $attached_post );
}
```

Once you have the post data for the post ID, you can proceed with the desired functionality relating to each attached post.


## Screenshots

1. Field display  
![Field display](https://raw.githubusercontent.com/WebDevStudios/cmb2-attached-posts/master/attached-posts-field.png)`
