<?php
/**
 * Plugin Name: Community Activity On profile
 * Author: BuddyDev
 * Author URI: https://buddydev.com/
 * Plugin URI: https://buddydev.com/plugins/bp-community-activity/
 * Version: 1.0.5
 * Description: It shows all the community activity on the profile of a user if the user is logged in
 * Credits: Greg for the Idea
 * License: GPL
*/
// no direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

if ( ! defined( 'BPCOM_ACTIVITY_SLUG' ) ) {
	define( 'BPCOM_ACTIVITY_SLUG', 'all-activity' );
}

/**
 * Load translation.
 */
function bp_com_activity_load_textdomain() {
	load_plugin_textdomain( 'bp-community-activity-on-profile', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'bp_init', 'bp_com_activity_load_textdomain', 2 );

/**
 * Add All activity nav to the user activities.
 */
function bp_add_community_activity_to_profile_nav() {

	if ( ! is_user_logged_in() || ! bp_is_active( 'activity' ) ) {
		return;
	}

	$slug          = bp_get_activity_slug();
	$activity_link = bp_loggedin_user_domain() . $slug . '/';
	// add to user activity subnav if it is logged in users profile.
	bp_core_new_subnav_item( array(
		'name'            => __( 'All Activity', 'bp-community-activity-on-profile' ),
		'slug'            => BPCOM_ACTIVITY_SLUG,
		'parent_url'      => $activity_link,
		'parent_slug'     => $slug,
		'screen_function' => 'bp_community_activity_screen',
		'position'        => 5,
		'user_has_access' => bp_is_my_profile(),
	) );

}

add_action( 'bp_activity_setup_nav', 'bp_add_community_activity_to_profile_nav' );

/**
 * Load home page.
 */
function bp_community_activity_screen() {
	do_action( 'bp_community_activity_screen' );
	bp_core_load_template( apply_filters( 'bp_activity_template_community_activity', 'members/single/home' ) );
}

/**
 * Filter query string to force to show all activities
 *
 * @param string $query_string.
 * @param string $object object.
 *
 * @return string
 */
function bp_community_ajax_filter( $query_string, $object ) {
	// if user is logged in & current action is community on profile tab.
	if ( bp_is_my_profile() && bp_is_activity_component() && bp_is_current_action( BPCOM_ACTIVITY_SLUG ) ) {
		$comm_query   = 'user_id=0&scope=0'; // just make it so it prints directory :).
		$query_string = $query_string ? $query_string . '&' . $comm_query : $comm_query;
	}

	return $query_string;
}
add_filter( 'bp_ajax_querystring', 'bp_community_ajax_filter', 12, 2 );

/**
 * Fix delete link on profile activity
 *
 * @param string               $del_link delete link.
 * @param BP_Activity_Activity $activity activity object.
 *
 * @return string
 */
function bpcom_fix_delete_link( $del_link, $activity ) {

	if ( bp_is_my_profile() && bp_is_activity_component() && bp_is_current_action( BPCOM_ACTIVITY_SLUG ) ) {
		// let us apply our mod.
		if ( bp_activity_user_can_delete( $activity ) ) {
			return $del_link;
		}

		return '';
	}

	return $del_link;
}

add_filter( 'bp_activity_delete_link', 'bpcom_fix_delete_link', 10, 2 );

/**
 * Show post form on all activity screen.
 */
function bpcom_show_post_form_if_needed() {
	if ( ! bp_is_my_profile() ) {
		return;
	}

	if ( ! did_action( 'bp_after_activity_post_form' ) && bp_is_user_activity() && bp_is_current_action( BPCOM_ACTIVITY_SLUG )  ) {
		bp_get_template_part( 'activity/post-form' );
	}

}
add_action( 'bp_after_member_activity_post_form', 'bpcom_show_post_form_if_needed' );

