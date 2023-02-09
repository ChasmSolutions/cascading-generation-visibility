<?php
/**
 * Plugin Name: Cascading Generation Visibility
 * Plugin URI: https://github.com/ChasmSolutions/cascading-generation-visibility
 * Description: Makes the generation visibility of a group cascade up to parents.
 * Text Domain: cascading-generation-visibility
 * Domain Path: /languages
 * Version:  1.0
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/Pray4Movement/prayer-global-porch
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 6.3
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'post_connection_added', 'dt_cascading_generation_visibility', 10, 4 );
function dt_cascading_generation_visibility( $post_type, $post_id, $field_key, $value  ){

    if ( $post_type === 'groups' ){
        if ( $field_key === 'parent_groups' ){

            // get the list of parent ids
            $parent_ids = _get_parent_ids( $post_id, 'groups_to_groups' );

            dt_write_log($parent_ids);

            // get the list of shared ids connected to the parent ids
            foreach( $parent_ids as $parent_id ){
                $shared_ids = DT_Posts::get_shared_with( 'groups', $parent_id, false );
                if ( is_wp_error( $shared_ids ) ){
                    continue ;
                }
                foreach( $shared_ids as $shared_id ){
                    DT_Posts::add_shared( $post_type, $post_id, $shared_id['user_id'], null, false, false );
                }
            }
        }
    }
}
function _get_parent_ids( $post_id, $p2p_type, $parent_ids = [] ){
    global $wpdb;

    $parent_id = $wpdb->get_var( $wpdb->prepare( "
           SELECT p2p_to
           FROM $wpdb->p2p
           WHERE p2p_from = %d
           AND p2p_type = %s
         ",
        $post_id,
        $p2p_type
    ) );

    if ( $parent_id === null ){
        return $parent_ids;
    } else {
        $parent_ids[] = $parent_id;
        return _get_parent_ids( $parent_id, $p2p_type, $parent_ids );
    }
}