<?php
/*
 * Plugin Name: Community Activity On profile
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com/members/sbrajesh/
 * Plugin URI: http://buddydev.com/plugins/bp-community-activity/
 * Version:1.0.1
 * Description: It shows all the commnity activity on the profile of a user if the user is logged in
 * Credits: Greg for the Idea
 * License: GPL
 * Last Updated: 15th June, 2011
 *  
 * 
*/

if(!defined("BPCOM_ACTIVITY_SLUG"))
    define ("BPCOM_ACTIVITY_SLUG", "all-activity");
/*localization*/
function bp_com_activity_load_textdomain() {
        $locale = apply_filters( 'bp_com_activity_load_textdomain_get_locale', get_locale() );
        
      
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
              
		$mofile = apply_filters( 'bp_com_activity_load_textdomain_mofile', $mofile_default );
		
                if ( file_exists( $mofile ) ) {
                    // make sure file exists, and load it
			load_textdomain( "bpcomac", $mofile );
		}
	}
}
add_action ( 'bp_loaded', 'bp_com_activity_load_textdomain', 2 );

//add all activity to nav
function bp_add_community_activity_to_profile_nav(){
    global $bp;
   if(!is_user_logged_in())
       return;
   
   $activity_link = bp_core_get_user_domain(bp_loggedin_user_id()) . $bp->activity->slug . '/';
   //add to user activity subnav if it is logged in users profile
   bp_core_new_subnav_item( array( 'name' => __( 'All Activity', 'bpcomac' ), 'slug' => BPCOM_ACTIVITY_SLUG, 'parent_url' => $activity_link, 'parent_slug' => $bp->activity->slug, 'screen_function' => 'bp_community_activity_screen', 'position' => 5,'user_has_access'=>  bp_is_my_profile() ) );

	
}
add_action( 'bp_activity_setup_nav','bp_add_community_activity_to_profile_nav' );
//just load the home page
function bp_community_activity_screen(){
    
        do_action( 'bp_community_activity_screen' );
	bp_core_load_template( apply_filters( 'bp_activity_template_community_activity', 'members/single/home' ) ); 
}
//filter ajax request


//load te,mplate
 //do the magic here by filtering
function bp_community_ajax_filter($query_string, $object ){
    global $bp;
    //if user is logged in & current action is community on profile tab
    if(bp_is_home()&&$bp->current_component==$bp->activity->slug&&$bp->current_action==BPCOM_ACTIVITY_SLUG){
        $comm_query= "user_id=0&scope=0";//just make it so it prints directory :)
        $query_string= $query_string?$query_string."&".$comm_query:$comm_query;

    }
        
    return $query_string;
}

add_filter( 'bp_ajax_querystring', "bp_community_ajax_filter",12,2);

//fix delete link on profile activity
add_filter("bp_activity_delete_link","bpcom_fix_delete_link",10,2);
function bpcom_fix_delete_link($del_link,$activity){
    global $bp;
       if(bp_is_home()&&$bp->current_component==$bp->activity->slug&&$bp->current_action==BPCOM_ACTIVITY_SLUG){
           //let us apply our mod
           if ( ( is_user_logged_in() && $activity->user_id == $bp->loggedin_user->id ) || $bp->loggedin_user->is_super_admin )
		return $del_link;
           return '';
       }
    return $del_link;
}
?>