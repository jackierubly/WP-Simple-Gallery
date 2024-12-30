<?php

add_shortcode('simple_gallery', 'simple_gallery_shortcode');

function simple_gallery_shortcode($atts)
{
    // Extract shortcode attributes
    $atts = shortcode_atts(
        ['id' => ''],
        $atts,
        'simple_gallery'
    );

    // Retrieve the gallery data from the database
    $gallery_data = get_option('simple_gallery_' . $atts['id']);

    if (!$gallery_data || empty($gallery_data['items'])) {
        return '<p>Gallery not found or no items in the gallery.</p>';
    }

    // Start rendering the gallery
    $output = '<div class="container simple-gallery my-5">';

    $output .= '<div class="row g-4">'; // Bootstrap grid with spacing

    // Render each gallery item
    foreach ($gallery_data['items'] as $item) {
        $output .= '<div class="col-lg-3 col-md-4 col-sm-6">';
        $output .= '<div class="card shadow-sm h-100">';
        if (!empty($item['image'])) {
            $output .= '<img src="' . esc_url($item['image']) . '" class="card-img-top" alt="' . esc_attr($item['title']) . '">';
        }
        $output .= '<div class="card-body text-center">';
        if (!empty($item['title'])) {
            $output .= '<h5 class="card-title mb-2">' . esc_html($item['title']) . '</h5>';
        }
        if (!empty($item['subtitle'])) {
            $output .= '<p class="card-text text-muted">' . esc_html($item['subtitle']) . '</p>';
        }
        $output .= '</div>'; // Close card-body
        $output .= '</div>'; // Close card
        $output .= '</div>'; // Close column
    }

    $output .= '</div>'; // Close row
    $output .= '</div>'; // Close container

    return $output;
}
