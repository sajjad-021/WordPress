<?php
/**
 * Utilities for fetching video data.
 *
 * @package    WordPress SEO
 * @subpackage WordPress SEO Video
 *
 * @since 11.1
 */

/**
 * Utility functions to get data about videos.
 *
 * @package WordPress SEO Video
 * @since   11.1
 */
class WPSEO_Video_Utils {
	/**
	 * Check whether VideoSEO is active for a specific post type.
	 *
	 * @since 11.1
	 *
	 * @param string $post_type The post type to check for.
	 *
	 * @return bool True if active, false if inactive.
	 */
	public static function is_videoseo_active_for_posttype( $post_type ) {
		$options = get_option( 'wpseo_video' );

		if ( ! is_array( $options['videositemap_posttypes'] ) || $options['videositemap_posttypes'] === array() ) {
			return false;
		}

		return in_array( $post_type, $options['videositemap_posttypes'], true );
	}

	/**
	 * Check to see if the video thumbnail was manually set, if so, update the $video array.
	 *
	 * @since 11.1
	 *
	 * @param int   $post_id The post to check for.
	 * @param array $video   The video array.
	 *
	 * @return array
	 */
	public static function get_video_image( $post_id, $video ) {
		// Allow for the video's thumbnail to be overridden by the meta box input.
		$videoimg = WPSEO_Meta::get_value( 'videositemap-thumbnail', $post_id );
		if ( $videoimg !== '' ) {
			$video['thumbnail_loc'] = $videoimg;
		}

		return $video;
	}


	/**
	 * Retrieve the duration of a video.
	 *
	 * Use a user provided duration if available, fall back to the available video data
	 * as previously retrieved through an API call.
	 *
	 * @since 11.1
	 *
	 * @param array $video   Data about the video being evaluated.
	 * @param int   $post_id Optional. Post ID.
	 *
	 * @return int Duration in seconds or 0 if no duration could be determined.
	 */
	public static function get_video_duration( $video, $post_id = null ) {
		$video_duration = 0;

		if ( isset( $post_id ) ) {
			$video_duration = (int) WPSEO_Meta::get_value( 'videositemap-duration', $post_id );
		}

		if ( $video_duration === 0 && isset( $video['duration'] ) ) {
			$video_duration = (int) $video['duration'];
		}

		return $video_duration;
	}


	/**
	 * Converts the duration in seconds to an ISO 8601 compatible output. Assumes the length is not over 24 hours.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_8601
	 *
	 * @param int $duration The duration in seconds.
	 *
	 * @return string $out ISO 8601 compatible output.
	 */
	public static function iso_8601_duration( $duration ) {
		if ( $duration <= 0 ) {
			return '';
		}

		$out = 'PT';
		if ( $duration > HOUR_IN_SECONDS ) {
			$hours    = floor( $duration / HOUR_IN_SECONDS );
			$out      .= $hours . 'H';
			$duration = ( $duration - ( $hours * HOUR_IN_SECONDS ) );
		}
		if ( $duration > MINUTE_IN_SECONDS ) {
			$minutes  = floor( $duration / MINUTE_IN_SECONDS );
			$out      .= $minutes . 'M';
			$duration = ( $duration - ( $minutes * MINUTE_IN_SECONDS ) );
		}
		if ( $duration > 0 ) {
			$out .= $duration . 'S';
		}

		return $out;
	}

	/**
	 * Determine whether a video is family friendly or not.
	 *
	 * @since 11.1
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool True if family friendly, false if not.
	 */
	public static function is_video_family_friendly( $post_id ) {
		$not_family_friendly = apply_filters( 'wpseo_video_family_friendly', WPSEO_Meta::get_value( 'videositemap-not-family-friendly', $post_id ), $post_id );

		return ( false === ( is_string( $not_family_friendly ) && $not_family_friendly === 'on' ) );
	}

	/**
	 * Return the plugin file
	 *
	 * @since 11.1
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return WPSEO_VIDEO_FILE;
	}

	/**
	 * Load translations
	 *
	 * @since 11.1
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'yoast-video-seo', false, dirname( plugin_basename( WPSEO_VIDEO_FILE ) ) . '/languages/' );
	}
}
