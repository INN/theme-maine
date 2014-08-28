<?php
/**
 * Widget that fetches and caches API results for Congresspersons,
 * and displays their events in a list with per-event tags.
 */
class party_time_widget extends WP_Widget {

	/**
	 * Sets up the widget name and description
	 *
	 * // code formatting based on Largo widgets
	 */
	function party_time_widget() {
		$widget_ops = array(
			'classname'   => 'party-time',
			'description' => __('Fetches events for selected politicians from the Sunlight Foundation Political Party Time API', 'largo')
		);
		$this->WP_Widget( 'party-time-widget', __('Political Party Time Widget'), $widget_ops);

	/**
	 * Queries the API via curl or file_get_contents
	 *
	 * @param string $state Two-letter state abbreviation
	 * @param array $instance The widget options
	 * @return JSON Sunlight Foundations Political Party Time API JSON list of events in that state.
	 */
	}
	function get_curl($state, $instance){
		$key = $instance['key'];
		$api = "http://politicalpartytime.org/api/v1/event/"."?beneficiaries__state=".$state."&format=json&apikey=".$key;
		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$api);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($ch);
			echo curl_error($ch);
			curl_close($ch);
			return $output;
		} else {
			return file_get_contents($api);
		}
	}

	/**
	 * Caches the widget html
	 *
	 * @param string $html The widget html
	 * @param array $instance The widget options
	 */
	function cache_widget($html, $instance) {
		if (isset($instance['cache'])) {
			$cache = $instance['cache'];
		} else {
			$cache = "wp-content/cache/" . $instance['state'] . '.widget.html';
		}
		if (!file_exists("wp-content/cache")) {
			mkdir("wp-content/cache/");
		}
		file_put_contents($cache, $html);
		$instance['cache'] = $cache;
	}

	/**
	 * Creates HTML list for widget
	 *
	 * @param array $events The events list
	 * @param array $instance The widget options
	 * @return string The widget HTML
	 */
	function widget_html($events, $instance) {
		$table_before = '<div class="ppt-table"><table><tbody>';
		$table_after = '</tbody></table></div>';

		$table_inner = '';
		$table_rows = array();

		// parses array
		foreach ($events as $event) {
			$event_tag = $instance['state']."_event_".$event->id ; // e.g. ME_event_37352

			$event_row_before = '<tr>';
			$event_name_before = '<td class="ppt-event-name">';
			$event_name_after = '</td>';
			$event_details_before = '<td class="ppt-event-details">';
			$event_details_after = $event_name_after;
			$event_beneficiaries_before = '<td class="ppt-event-beneficiaries">';
			$event_beneficiaries_after = $event_name_after;
			$event_coverage_before = '<td class="ppt-event-coverage">';
			$event_coverage_after = $event_name_after;
			$event_row_after = '</tr>';

			// Generates event name and link
			$event_name = '<a href="http://politicalpartytime.org/party/'. $event->id . '">' . $event->entertainment . '</a>'; // assuming that first beneficiary is most noteworthy

			// Generates event details
			$event_details_date = '<p class="ppt-date">';
			if ( $event->end_date != '' ) {
				$event_details_date .= date('D, M. j', strtotime($event->start_date)) . ' to ' . date('D, M. j, Y', strtotime($event->end_date));
			} elseif ( $event->start_time != '' ) {
				$event_details_date .= date('D, M. j, Y, g:i a', strtotime($event->start_date . ' ' . $event->start_time));
			} else {
				$event_details_date .= date('D, M. j, Y', strtotime($event->start_date));
			}
			$event_details_date .= '</p>';

			$event_details_venue = '';
			if ( count($event->venue) > 1 ) {
				$venues = array();
				foreach ($event->venue as $venue) {
					$venue_string = $venue->venue_name;
					if ( $venue->city != '' ) {
						$venue_string .= ', ' . $venue->city;
					}
					if ( $venue->state != '' ) {
						$venue_string .= ', ' . $venue->state;
					}
					$venues[] = $venue_string;
				}
				$event_details_venue = '<p class="ppt-venue"><span class="label-venues">Locations:</span><ul class="venues"><li>'. implode('</li><li>', $venues) . '</li></ul></p>';
				unset($venues);
			} elseif ( count($event->venue) > 0 ) {
				$venue = $event->venue;
				$venue_string = $venue->venue_name;
				if ( $venue->city != '' ) {
					$venue_string .= ', ' . $venue->city;
				}
				if ( $venue->state != '' ) {
					$venue_string .= ', ' . $venue->state;
				}
				$event_details_venue = '<p class="ppt-venue"><span class="label-venues">Location:</span> ' . $venue_string . '</p>';
				unset($venues);
			} else {
				$event_details_venue = '';
			}

			$event_details = $event_details_date . $event_details_venue; //placeholder



			// Generate list of names with links
			foreach ($event->beneficiaries as $beneficiary) { // not currently needed in Maine, but better to be bulletproof. Is needed in California.

				// Make sure there's a tag for the user
					$link = term_exists( $beneficiary->name ) ? TRUE : FALSE;

					$beneficiary_tag = strtolower( str_replace( ' ', '-', $beneficiary->name ) );

					if ( $link ) {
						// Open the <a>
						$beneficiary_string = '<a href="' . site_url() . '?tag=' . $beneficiary_tag .'">';
					} else {
						$beneficiary_string = '';
					}

					// Add a title if they have one listed
					if ( isset($beneficiary->title ) ) {
						$beneficiary_string .= $beneficiary->title . ' ';
					}

					// Their name
					$beneficiary_string .= $beneficiary->name;

					// Their political affiliation, if applicable. AP style is Title Name (Party-State)
					if ( isset($beneficiary->party ) && isset( $beneficiary->state ) && isset( $beneficiary->district ) && $beneficiary->district != '') {
						$beneficiary_string .= ' ('. $beneficiary->party . ' ' . $beneficiary->state . '-' . $beneficiary->district .')';
					} elseif ( isset( $beneficiary->party ) && isset( $beneficiary->state ) ) {
						$beneficiary_string .= ' ('. $beneficiary->party . ' ' . $beneficiary->state .')';
					} elseif ( isset( $beneficiary->party ) ) {
						$beneficiary_string .= ' ('. $beneficiary->party .')';
					}

					if ( $link ) {
						// Close the <a>
						$beneficiary_string .= '</a>';
					}

					$event_beneficiaries_array[] = $beneficiary_string;

					if ( $beneficiary->affiliate != '' ) {
						$affiliate = $beneficiary->affiliate;
						$event_beneficiaries_array[] = '<a href="' . site_url() . '?tag=' . $beneficiary_tag .'">' . $affiliate . '</a>';
					}

			}
			if ( count( $event_beneficiaries_array ) > 1 ) {
				$event_beneficiaries = '<p><span class="label-beneficiaries">Beneficiaries:</span><ul><li>' . implode('</li><li>', $event_beneficiaries_array) . '</li></ul></p>';
			} elseif ( count( $event_beneficiaries_array ) == 1 ) {
				$event_beneficiaries = '<p><span class="label-beneficiaries">Beneficiary:</span><ul class="single-beneficiary"><li>' . implode('</li><li>', $event_beneficiaries_array) . '</li></ul></p>';
			}

			unset($event_beneficiaries_array);

			// Generate list of coverage
			$coverage = new WP_Query( array(
				'meta_key' => 'ppt_event_id',
				'meta_value' => $event->id
			));
			if ( $coverage->have_posts() ) {
				$event_coverage = '<p><span class="label-coverage">Coverage:</span><ul class="coverage">';
				while ( $coverage->have_posts() ) : $coverage->the_post();
					$event_coverage .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
				endwhile;
				$event_coverage .= '</ul></p>';
			} else {
				$event_coverage = '';
			}
			unset($coverage);
			wp_reset_postdata();
			// Creates the row
			$table_rows[] = $event_row_before .
				$event_name_before . $event_name . $event_name_after .
				$event_details_before . $event_details . $event_details_after . // placeholder
				$event_beneficiaries_before . $event_beneficiaries . $event_beneficiaries_after .
				$event_coverage_before . $event_coverage . $event_coverage_after .
				$event_row_after;
		}
		$table_inner = implode("\n", $table_rows);
		$widget = $table_before . $table_inner . $table_after . '<p class="sunlight-link"><a href="http://politicalpartytime.org/">Powered by the Sunlight Foundation\'s Political Party Time API</a></p>';

		//$this->cache_widget($widget, $instance);
		return $widget;
	}


	/**
	 * If cache is out of date, updates cache.
	 *
	 * @param array $instance The widget options
	 */
	function api_query($instance) {
		$key = $instance['key'];
		$state = $instance['state'];
		$events = array();

		// Checks for state JSON cache
		$cache = './wp-content/cache/'.$state.'.cache.json';
		if (file_exists($cache) && filemtime($cache) > time() - 30*60/*seconds*/){
			// If that ID's cache file is newer than half an hour, use it.
			$json = json_decode(file_get_contents($cache), false);
		} else {
			$response = $this->get_curl($state, $instance);
			$json = json_decode($response, false);
			if (!file_exists("wp-content/cache")) {
				mkdir("wp-content/cache/");
			}
			file_put_contents($cache,json_encode($json));
		}
		$events = $json->objects;

		// returns HTML
		$html = $this->widget_html($events, $instance);
		return $html;
	}

	/**
	 * Fetches the html from the cache. If no cache, calls api_query
	 *
	 * @param array $instance
	 * @return string The widget HTML
	 */
	function regurgitate_cache($instance) {
		if (file_exists($instance->cache) && filemtime($cache) > time() - 29*60/*seconds*/) {
			echo file_get_contents($instance->cache);
		} else {
			echo $this->api_query($instance);
		}
	}

	/**
	 * Draws the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __(get_bloginfo('name'), 'largo') : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		print '<img src="' . get_stylesheet_directory_uri() . '/img/ppt_logo_default_500.png" alt="Political Party Time" id="ppt-logo">';
		echo $this->regurgitate_cache($instance);
		echo $after_widget;
	}

	/**
	 * Saves widget options for updating
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 * @return array $instance The updated instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['state'] = strip_tags( $new_instance['state'] );
		$instance['key'] = strip_tags( $new_instance['key'] );
		$this->api_query($instance);
		return $instance;
	}

	/**
	 * Outputs admin widget options form on admin
	 *
	 * @param array $instance The widget options
	 */
	function form( $instance ){
		$defaults = array(
			'title' => __('Political Party Time', 'largo'),
			'state' => '',
			'key'   => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'largo'); ?></label><br/>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'state' ); ?>"><?php _e('Two-letter state abbreviation', 'largo'); ?></label><br/>
				<input id="<?php echo $this->get_field_id( 'state' ); ?>" name="<?php echo $this->get_field_name( 'state' ); ?>" value="<?php echo $instance['state']; ?>" style="width:20%;" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'key' ); ?>"><?php _e('<a href="http://sunlightfoundation.com/api/accounts/register/">Sunlight Foundation API key</a>:', 'largo'); ?></label><br/>
				<input id="<?php echo $this->get_field_id( 'key' ); ?>" name="<?php echo $this->get_field_name( 'key' ); ?>" value="<?php echo $instance['key']; ?>" style="width:100%;" />
			</p>
		<?php

	}
}
