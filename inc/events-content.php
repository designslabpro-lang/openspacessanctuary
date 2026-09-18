<?php
/**
 * Events & Retreats — theme-managed content store.
 *
 * Events live in a single option (oss_events_content) edited at
 * Appearance → Events and rendered as a timeline by
 * page-templates/template-events-v2.php. No post type, no single pages.
 * The event types are the ones listed in the client's content document.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_EVENTS_OPTION', 'oss_events_content' );

function oss_events_types() {
	return array(
		'Upcoming Events',
		"Women's Retreats",
		'Open House',
		"Horseman's Symposium",
		'Workshops',
	);
}

/**
 * Defaults seed the two events that already existed on the site.
 */
function oss_events_content_defaults() {
	return array(
		'heading' => 'Events & Retreats',
		'intro'   => 'From open houses to community workshops, here is what is happening at the sanctuary.',
		'events'  => array(
			array(
				'title'       => 'Sanctuary Open House',
				'type'        => 'Open House',
				'date'        => '2026-10-06',
				'time'        => '10:00 AM – 1:00 PM',
				'location'    => 'Open Spaces Sanctuary, Main Pasture',
				'description' => 'Meet our herd, tour the grounds, and learn about our programs. Free and open to the public.',
				'link'        => '',
				'image_id'    => 0,
			),
			array(
				'title'       => 'Community Workshop: Horses as Mirrors',
				'type'        => 'Workshops',
				'date'        => '2026-10-30',
				'time'        => '2:00 PM – 4:00 PM',
				'location'    => 'Open Spaces Sanctuary, Barn Pavilion',
				'description' => 'A hands-on workshop exploring how horses reflect our emotional states and support personal growth.',
				'link'        => '',
				'image_id'    => 0,
			),
		),
	);
}

function oss_events_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$saved   = get_option( OSS_EVENTS_OPTION, array() );
		$content = array_merge( oss_events_content_defaults(), is_array( $saved ) ? $saved : array() );
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

/**
 * Events ordered by date (soonest first); undated events last.
 */
function oss_events_sorted() {
	$events = (array) oss_events_get( 'events' );
	usort( $events, function ( $a, $b ) {
		$ta = ! empty( $a['date'] ) ? strtotime( $a['date'] ) : PHP_INT_MAX;
		$tb = ! empty( $b['date'] ) ? strtotime( $b['date'] ) : PHP_INT_MAX;
		return $ta <=> $tb;
	} );
	return $events;
}
