<?php

/**
 * Loads Political Party Time widget
 */
require_once( get_stylesheet_directory(). '/inc/widgets/political-party-time.php');

/**
 * Registers widgets
 */
function maine_register_widgets() {
	register_widget( 'WP_Widget_RSS' );
	register_widget( 'party_time_widget' );
}
add_action( 'widgets_init', 'maine_register_widgets', 11 );

/**
 * Custom post meta for Political Party Time
 */
require_once( get_template_directory() . '/largo-apis.php' );
largo_add_meta_box(
	'ppt_event_id',
	'Political Party Time Event Details',
	'ppt_event_id_box',
	'post',
	'normal',
	'default'
);
function ppt_event_id_box() {
	global $post;
	$value = get_post_meta( $post->ID, 'ppt_event_id', true );
	wp_nonce_field( 'largo_meta_box_nonce', 'meta_box_nonce' );
	?>
	<p><label for="ppt_event_id"><?php _e('Event ID, as listed in the Sunlight Foundation Political Party Time API. Find the event ID by clicking on the name of the event in the Political Party Time widget on your site.', 'largo'); ?></label></p>
	<input type="text" id="ppt_event_id" name="ppt_event_id" value="<?php echo $value; ?>" />
	<?php
	largo_register_meta_input( 'ppt_event_id' );
}