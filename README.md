CMB2 Attached Posts Field
==================

Custom field for [CMB2](https://github.com/WebDevStudios/CMB2).

The post IDs are saved in an array, which can be rearranged by dragging and dropping posts in the attached posts column. Now incorporates the same search field as the [CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field).

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

1. Post search  
![Post search](https://raw.githubusercontent.com/WebDevStudios/cmb2-attached-posts/master/attached-posts-search.gif)`

## Changelog

### 1.2.6
* Add post type label next to post title when multiple post-types are specified in the query args.

### 1.2.5
* Combined the best bits from the [CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field) type and this field type, so now you can search for additional posts/pages/etc to be attached. User search is not currently supported. ([#7](https://github.com/WebDevStudios/cmb2-attached-posts/pull/7)).

### 1.2.4
* Add support for attaching Users instead of Posts/Custom Posts. Props [mckernanin](https://github.com/mckernanin) ([#27](https://github.com/WebDevStudios/cmb2-attached-posts/pull/27)).

### 1.2.3
* Add loader to manage loading the most recent version of this lib.

### 1.2.2
* Allow array of post-types. Props [@mmcachran](https://github.com/mmcachran).

### 1.2.1
* Add Search Filter Boxes to Lists. Props [@owenconti](https://github.com/owenconti).

### 1.2.0
* Add plugin support. Props [@yelly](https://github.com/yelly).
