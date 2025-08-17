<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Retrieve the ID of a page by its title.
 *
 * Uses a lightweight query and caches results for the duration of the request
 * to avoid repeated database lookups. Returns `0` when no page is found.
 *
 * @param string $title Page title to search for.
 * @return int          Page ID or 0 when not found.
 */
function obti_get_page_id( $title ) {
    static $cache = [];

    $title = sanitize_text_field( $title );

    if ( isset( $cache[ $title ] ) ) {
        return $cache[ $title ];
    }

    $q = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'title'          => $title,
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    $cache[ $title ] = $q->have_posts() ? (int) $q->posts[0] : 0;

    return $cache[ $title ];
}

