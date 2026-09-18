<?php
/**
 * Homepage Content — plain-PHP editable data store.
 *
 * No Elementor dependency: every piece of homepage copy/media lives in a
 * single option (oss_home_content), edited through a real wp-admin screen
 * (Appearance → Homepage Content) and rendered by
 * page-templates/template-home-custom.php.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_HOME_OPTION', 'oss_home_content' );

/**
 * Default values — exactly the source brief content, used until an admin
 * edits a field (and as the fallback if a field is ever cleared).
 */
function oss_home_content_defaults() {
	return array(
		'hero_eyebrow'    => 'Healing Begins Here',
		'hero_heading'    => 'A Place where People Grow and Heal!',
		'hero_body'       => "Life can leave us carrying burdens that feel too heavy to bear—grief, trauma, stress, illness, burnout, or the weight of caring for others. At Open Spaces Sanctuary, we believe healing begins when we reconnect with ourselves, with nature, and with the quiet wisdom of horses.\n\nNestled in the peaceful countryside of Ocala, Florida, Open Spaces Sanctuary offers transformative equine-assisted learning experiences designed to restore hope, build resilience, and inspire lasting personal growth. Here, people and horses come together in a safe, supportive environment where authentic connection leads to meaningful change.\n\nWhether you are seeking healing, personal growth, or simply a place to breathe again, you are welcome here.",
		'hero_btn1_text'  => 'Learn More',
		'hero_btn1_url'   => '/about/',
		'hero_btn2_text'  => 'Donate Now',
		'hero_btn2_url'   => '/contact/',
		'hero_image_id'   => 52,
		'hero_slide_ids'  => array( 52, 110, 87 ),

		'power_eyebrow'   => 'Healing Begins Here',
		'power_heading'   => 'The Healing Power of Horses',
		'power_body1'     => 'Horses have an extraordinary ability to reflect what we are feeling without judgment or expectation. They respond honestly to our emotions, energy, and intentions, helping us become more aware of ourselves in ways words often cannot.',
		'power_body2'     => 'Through guided, ground-based experiences—no riding—participants discover greater confidence, healthier boundaries, improved communication, emotional resilience, and renewed hope.',
		'power_quote'     => "Healing doesn't happen because of what we do to the horses. It happens because of the experiences and relationships we build with them.",
		'power_caption'   => "Connection\nChanges Lives",
		'power_image_id'  => 110,

		'serve_heading'   => 'Who We Serve',
		'serve_intro'     => "Open Spaces Sanctuary welcomes everyone navigating life's challenges while offering specialized programs for:",
		'serve_items'     => array(
			array( 'icon' => 'shield', 'label' => 'Veterans and Military Families' ),
			array( 'icon' => 'star', 'label' => 'First Responders' ),
			array( 'icon' => 'heart', 'label' => 'Survivors of Trauma' ),
			array( 'icon' => 'hands', 'label' => 'Caregivers' ),
			array( 'icon' => 'ribbon', 'label' => 'Cancer Patients and Individuals Facing Health Challenges' ),
			array( 'icon' => 'bloom', 'label' => 'Women Seeking Empowerment' ),
			array( 'icon' => 'compass', 'label' => 'Individuals, Families, and Professionals looking to grow, heal, and reconnect' ),
		),
		'serve_closing'   => "No matter your story, you'll find compassion, acceptance, and support here.",

		'programs_heading' => 'Our Programs',
		'programs_intro'   => 'Every person arrives with unique experiences and goals. Our programs are thoughtfully designed to meet you where you are.',

		'horses_heading'  => 'Meet Our Horses',
		'horses_body'     => "Every horse at Open Spaces Sanctuary has a story.\n\nSome have overcome hardship. Some have found a second chance. Together, they become remarkable teachers—offering honesty, patience, trust, and unconditional acceptance to every person they meet.\n\nOur horses are not tools. They are partners in healing.",
		'horses_sub'      => 'Meet the Herd',
		'horses_image_id' => 52,

		'founder_heading' => 'Our Founder',
		'founder_name'    => 'Donna Blem',
		'founder_body'    => "Open Spaces Sanctuary was founded by Donna Blem, whose lifelong passion for horses and service has impacted thousands of lives.\n\nAs the co-founder and Executive Director of one of the nation's largest therapeutic riding centers, Donna spent over two decades building programs that changed lives while mentoring staff, volunteers, and equine partners. Her experience in nonprofit leadership, equine-assisted learning, and natural horsemanship now guides the vision of Open Spaces Sanctuary—a place where healing, hope, and connection flourish for both people and horses.",
		'founder_btn'     => 'Meet Donna',
		'founder_image_id' => 0,

		'stories_heading' => 'Stories of Hope',
		'testimonials'    => array(
			array( 'quote' => "Coming to the ranch was truly a game changer for me... After transitioning out of the military, I've often felt disconnected. Today I left with a sense of lightness and hope.", 'name' => 'Doug B.' ),
			array( 'quote' => "Donna truly 'gets it.' Every lesson leaves me feeling seen and heard. She has helped me better understand myself while creating a safe, supportive environment for healing.", 'name' => 'Linda H.' ),
			array( 'quote' => 'Today I realized I need to accept my mom where she is each day rather than holding onto expectations. That breakthrough changed everything for me.', 'name' => 'Alex R.' ),
		),

		'donate_heading'  => 'Help Us Change Lives',
		'donate_body'     => "Every donation creates opportunities for healing—for individuals navigating trauma, illness, grief, and life's many challenges, and for the horses who make this work possible.\n\nYour generosity helps provide scholarships, exceptional horse care, educational programs, and a peaceful sanctuary where transformation can happen.\n\nTogether, we can create hope, one person and one horse at a time.",
		'donate_btn'      => 'Donate Today',
		'donate_btn_url'  => '/contact/',
		'donate_image_id' => 52,

		'connect_heading' => 'Stay Connected',
		'connect_body'    => 'Join our community and receive inspiring stories, upcoming events, program updates, and opportunities to make a difference.',

		'final_heading'   => 'Everyone Deserves a Place to Heal.',
		'final_body'      => "Whether you're looking for support, searching for meaningful ways to give back, or simply curious about the incredible partnership between people and horses, we'd love to welcome you to Open Spaces Sanctuary.",
		'final_sub'       => 'Come experience the healing power of connection',
		'final_btn1_text' => 'Learn More',
		'final_btn1_url'  => '/about/',
		'final_btn2_text' => 'Donate Now',
		'final_btn2_url'  => '/contact/',
		'final_image_id'  => 110,
	);
}

/**
 * Icon library shared by the Who We Serve editor field and renderer.
 */
function oss_home_icon_library() {
	return array(
		'shield'  => '<path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
		'star'    => '<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.7z"/>',
		'heart'   => '<path d="M12 20s-7-4.4-9.5-8.8C.8 8 2 4.5 5.5 4c2-.3 3.7.8 4.5 2.3C10.8 4.8 12.5 3.7 14.5 4 18 4.5 19.2 8 17.5 11.2 15 15.6 12 20 12 20z"/>',
		'hands'   => '<path d="M8 13V6a1.5 1.5 0 0 1 3 0v5"/><path d="M11 11V4.5a1.5 1.5 0 0 1 3 0V11"/><path d="M14 11.5V6a1.5 1.5 0 0 1 3 0v8c0 3.3-2.7 6-6 6h-1c-2 0-3.3-.7-4.5-2L4 15.5c-.8-.8-.8-2 0-2.7.7-.7 1.8-.7 2.5 0L8 14"/>',
		'ribbon'  => '<circle cx="12" cy="7" r="4"/><path d="M9.5 10.5L6 21l6-3 6 3-3.5-10.5"/>',
		'bloom'   => '<path d="M12 12c0-3 1.5-5 4-6-1 2.5-1 4.5 0 6-1.5 1-3.5 1-4 0z"/><path d="M12 12c0-3-1.5-5-4-6 1 2.5 1 4.5 0 6 1.5 1 3.5 1 4 0z"/><path d="M12 12c2.5 1.2 4 3 4 5.5-2.5-.3-4-1.5-4-3.5"/><path d="M12 12c-2.5 1.2-4 3-4 5.5 2.5-.3 4-1.5 4-3.5"/><circle cx="12" cy="12" r="1.4"/><path d="M12 17.5V21"/>',
		'compass' => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-2 5-5 2 2-5z"/>',
		'people'  => '<circle cx="8" cy="8" r="3"/><circle cx="16" cy="8" r="3"/><path d="M2 20c0-3 2.5-5 6-5s6 2 6 5"/><path d="M10 20c0-3 2.5-5 6-5s6 2 6 5"/>',
		'leaf'    => '<path d="M20 4C10 4 4 10 4 20c10 0 16-6 16-16z"/><path d="M4 20L14 10"/>',
	);
}

/**
 * Read a homepage content field, falling back to its default.
 */
function oss_home_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_home_content_defaults();
		$saved    = get_option( OSS_HOME_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

/**
 * Render a stored image (by attachment ID) with a fallback placeholder.
 */
function oss_home_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_home_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
