<?php

/**
 * Overrides the default wp_install_defaults function which creates the dummy content when WordPress is first installed.
 *
 * We simply creates the 'Uncategorised' category. Nothing else.
 *
 * @global wpdb       $wpdb
 *
 * @param int $user_id User ID.
 */
function wp_install_defaults( $user_id ) {

    global $wpdb;

    // Default category
    $cat_name = __('Uncategorized');
    /* translators: Default category slug */
    $cat_slug = sanitize_title(_x('Uncategorized', 'Default category slug'));
    if ( global_terms_enabled() ) {
        $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_ID FROM {$wpdb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
        if ( $cat_id == null ) {
            $wpdb->insert( $wpdb->sitecategories, array('cat_ID' => 0, 'cat_name' => $cat_name, 'category_nicename' => $cat_slug, 'last_updated' => current_time('mysql', true)) );
            $cat_id = $wpdb->insert_id;
        }
        update_option('default_category', $cat_id);
    } else {
        $cat_id = 1;
    }

    $wpdb->insert( $wpdb->terms, array('term_id' => $cat_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0) );
    $wpdb->insert( $wpdb->term_taxonomy, array('term_id' => $cat_id, 'taxonomy' => 'category', 'description' => '', 'parent' => 0, 'count' => 1));

}