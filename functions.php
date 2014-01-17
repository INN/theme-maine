<?php
function maine_register_widgets() {
	register_widget( 'WP_Widget_RSS' );
}
add_action( 'widgets_init', 'maine_register_widgets', 11 );