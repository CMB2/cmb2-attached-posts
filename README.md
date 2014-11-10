CMB2 Attached Posts Field
==================

Custom field for CMB2 (https://github.com/WebDevStudios/CMB2).

This requires jQuery UI for drag &amp; drop and sort functionality, which is included in the /js/lib/ directory.

The post IDs are saved in an array, which can be rearranged by dragging and dropping posts in the attached posts column.

<h2>Installation</h2>

Follow the example in `example-field-setup.php` for a demonstration.

<h2>Customization</h2>
In the example file <code>example-field-setup.php</code>, the field is added using the prefix <code>_attached_cmb2_</code> and the name <code>attached_posts</code> which results in a meta key of <code>_attached_cmb2_attached_posts</code>.  If you would like to use a different name and meta key for your field, you will need to replace instances of <code>_attached_cmb2_attached_posts</code> in the files.  Do a find and replace for <code>_attached_cmb2_attached_posts</code>, which you will find instances of in <code>/js/attached-posts.js</code> and <code>/attached-posts-field.php</code>.

<h2>Usage</h2>
You can retrieve the meta data using the following:

<code>$attached = get_post_meta( get_the_ID(), '_attached_cmb2_attached_posts', true );</code>

This will return an array of attached post IDs.  You can loop through those post IDs like the following example:

<code>
	foreach ( $attached as $attached_post ) {
		$post = get_post( $attached_post );
	}
</code>

Once you have the post data for the post ID, you can proceed with the desired functionality relating to each attached post.
