<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function obti_get_page_id( $title ) {
    $q = new WP_Query([
        'post_type'      => 'page',
        'title'          => $title,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);

    return $q->have_posts() ? intval( $q->posts[0] ) : 0;
}

