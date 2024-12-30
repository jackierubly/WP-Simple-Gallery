<?php

if (!function_exists('simple_gallery_menu')) {
    function simple_gallery_menu()
    {
        // Main menu page
        add_menu_page(
            'Simple Gallery',                // Page title
            'Simple Gallery',                // Menu title
            'edit_posts',                    // Capability (Admins, Editors, Authors)
            'simple-gallery',                // Menu slug
            'simple_gallery_list_page',      // Callback function for gallery list
            'dashicons-format-gallery',      // Icon
            20                               // Position
        );

        // Submenu for editing individual galleries
        add_submenu_page(
            null,                            // No visible menu
            'Edit Gallery',                  // Page title
            'Edit Gallery',                  // Menu title
            'edit_posts',                    // Capability (Admins, Editors, Authors)
            'edit-simple-gallery',           // Menu slug
            'simple_gallery_edit_page'       // Callback function
        );
    }
}
add_action('admin_menu', 'simple_gallery_menu');


function simple_gallery_list_page()
{
    global $wpdb;

    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['gallery_id'])) {
        delete_option($_GET['gallery_id']);
        echo '<div class="updated"><p>Gallery deleted successfully.</p></div>';
    }

    // Fetch all galleries
    $galleries = $wpdb->get_results(
        "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'gallery_%'"
    );

?>
    <div class="wrap">
        <h1 style="font-size: 36px; font-weight: bold; color: #333;">Manage Galleries</h1>
        <a href="?page=edit-simple-gallery" class="button button-primary" style="background-color: #008080; color: white; font-size: 22px; padding: 10px 20px; border-radius: 5px;">Create New Gallery</a>
        <br><br>
        <table class="widefat fixed" style="border: 1px solid #ddd; font-size: 18px;">
            <thead>
                <tr style="background-color: #f4f4f4; text-align: left;">
                    <th style="padding: 10px;">Gallery Name</th>
                    <th style="padding: 10px;">Gallery Preview</th>
                    <th style="padding: 10px;">Shortcode <i>(Place this into the page)</i></th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($galleries) : ?>
                    <?php foreach ($galleries as $gallery) :
                        $gallery_data = maybe_unserialize($gallery->option_value);
                        $shortcode = '[gallery id="' . esc_attr($gallery->option_name) . '"]';

                        // Get the first 4 images for the preview
                        $preview_images = array_slice($gallery_data['items'], 0, 4);
                    ?>
                        <tr style="border-bottom: 1px solid #ddd; padding-top: 30px;">
                            <td style="padding: 10px; font-weight: bold; color: #333;">
                                <h3 style="margin: 0; font-size: 20px;"><?php echo esc_html($gallery_data['name']); ?></h3>
                            </td>
                            <td style="padding: 10px;">
                                <div style="display: flex; gap: 10px;">
                                    <?php foreach ($preview_images as $preview): ?>
                                        <?php if (!empty($preview['image'])): ?>
                                            <img src="<?php echo esc_url($preview['image']); ?>" alt="Preview" style="width: 80px; height: auto; border-radius: 5px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <?php else: ?>
                                            <em style="color: #666;">No image</em>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="padding: 10px; color: #555;">
                                <span id="shortcode-<?php echo esc_attr($gallery->option_name); ?>" style="font-size: 16px; color: #008080;"><?php echo $shortcode; ?></span>
                                <button class="button copy-shortcode" data-clipboard-target="#shortcode-<?php echo esc_attr($gallery->option_name); ?>" style="background-color: #008080; color: white; font-size: 14px; margin-left: 10px; padding: 5px 10px; border-radius: 5px;">Copy</button>
                            </td>
                            <td style="padding: 10px;">
                                <a href="?page=edit-simple-gallery&gallery_id=<?php echo esc_attr($gallery->option_name); ?>"
                                    class="button"
                                    style="background-color: #008080; color: white; font-size: 18px; padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block; margin-bottom: 5px;">Edit</a>
                                <a href="?page=simple-gallery&action=delete&gallery_id=<?php echo esc_attr($gallery->option_name); ?>"
                                    class="button delete"
                                    style="background-color: #ff4d4d; color: white; font-size: 18px; padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #666; font-size: 20px;">No galleries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>



<?php
}

function simple_gallery_edit_page()
{
    global $wpdb;

    // Load existing gallery data if editing
    $gallery_id = isset($_GET['gallery_id']) ? sanitize_text_field($_GET['gallery_id']) : '';
    $gallery = $gallery_id ? get_option($gallery_id) : null;

    // Handle form submission
    if (isset($_POST['save_gallery'])) {
        $gallery_name = sanitize_text_field($_POST['gallery_name']);
        $gallery_description = sanitize_textarea_field($_POST['gallery_description']);
        $gallery_items = [];

        // Collect gallery items
        if (!empty($_POST['image_urls'])) {
            foreach ($_POST['image_urls'] as $index => $image_url) {
                $gallery_items[] = [
                    'image'    => esc_url_raw($image_url),
                    'title'    => sanitize_text_field($_POST['titles'][$index]),
                    'subtitle' => sanitize_text_field($_POST['subtitles'][$index]),
                ];
            }
        }

        // Save or update the gallery
        if ($gallery_id) {
            update_option($gallery_id, [
                'name'        => $gallery_name,
                'description' => $gallery_description,
                'items'       => $gallery_items,
            ]);
        } else {
            $gallery_id = uniqid('gallery_');
            add_option($gallery_id, [
                'name'        => $gallery_name,
                'description' => $gallery_description,
                'items'       => $gallery_items,
            ]);
        }

        // Redirect back to the gallery list
        if (!headers_sent()) {
            wp_redirect(admin_url('admin.php?page=simple-gallery'));
            exit;
        }
    }

?>
    <div class="wrap">
        <h1 style="font-size: 36px; font-weight: bold; color: #333;">
            <?php echo $gallery ? 'Edit Gallery' : 'Create New Gallery'; ?>
        </h1>
        <br><br>

        <!-- Gallery Settings Section -->
        <form method="POST" style="font-size: 18px; color: #444;">
            <div class="mb-4" style="margin-bottom: 40px;">
                <h2 class="mb-3" style="font-size: 26px; font-weight: bold; color: #444;">Gallery Settings</h2>

                <div class="mb-3" style="margin-bottom: 20px;">
                    <label for="gallery_name" class="form-label" style="font-size: 18px; font-weight: bold;">Title</label>
                    <input type="text" name="gallery_name" id="gallery_name"
                        class="form-control" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;"
                        value="<?php echo $gallery ? esc_attr($gallery['name']) : ''; ?>"
                        placeholder="Enter gallery title" required>
                </div>

                <div class="mb-3" style="margin-bottom: 20px;">
                    <label for="gallery_description" class="form-label" style="font-size: 18px; font-weight: bold;">Description</label>
                    <textarea name="gallery_description" id="gallery_description"
                        class="form-control" rows="3"
                        style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;"
                        placeholder="Enter gallery description"><?php echo $gallery ? esc_textarea($gallery['description']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Gallery Items Table -->
            <h2 style="font-size: 26px; font-weight: bold; color: #444; margin-bottom: 20px;">Gallery Items</h2>
            <table class="widefat fixed" style="border: 1px solid #ddd; font-size: 18px; margin-bottom: 20px;">
                <thead style="background-color: #f4f4f4;">
                    <tr>
                        <th style="padding: 10px;">Image</th>
                        <th style="padding: 10px;">Title</th>
                        <th style="padding: 10px;">Subtitle</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="gallery-items">
                    <?php if ($gallery && !empty($gallery['items'])) : ?>
                        <?php foreach ($gallery['items'] as $item) : ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px;">
                                    <div class="image-thumbnail">
                                        <img src="<?php echo esc_url($item['image']); ?>" style="max-width: 250px; height: auto; border-radius: 5px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                    </div>
                                    <input type="text" name="image_urls[]" class="image-url form-control"
                                        style="width: 100%; margin-top: 10px;" value="<?php echo esc_url($item['image']); ?>" readonly>
                                    <button type="button" class="button upload-image"
                                        style="background-color: #17a2b8; color: white; margin-top: 10px; padding: 10px 15px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Select Image</button>
                                </td>
                                <td style="padding: 10px;">
                                    <input type="text" name="titles[]" value="<?php echo esc_attr($item['title']); ?>" placeholder="Image Title" required
                                        class="form-control" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td style="padding: 10px;">
                                    <input type="text" name="subtitles[]" value="<?php echo esc_attr($item['subtitle']); ?>" placeholder="Image Subtitle"
                                        class="form-control" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td style="padding: 10px;">
                                    <button type="button" class="button remove-item"
                                        style="background-color: #ff4d4d; color: white; padding: 10px 15px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #666;">No items found. Click "Add Item" to create new items.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Buttons -->
            <button type="button" id="add-item" class="button"
                style="background-color: #17a2b8; color: white; padding: 10px 20px; font-size: 18px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Add Item</button>
            <br><br>
            <button type="submit" name="save_gallery" class="button button-primary"
                style="background-color:rgb(184, 23, 58); color: white; padding: 10px 20px; font-size: 18px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Save Changes</button>
        </form>
    </div>


<?php
}

function render_simple_gallery($atts)
{
    // Parse the attributes
    $atts = shortcode_atts(['id' => ''], $atts, 'gallery');

    // Ensure the ID is provided
    if (empty($atts['id'])) {
        return '<p>Error: No gallery ID provided.</p>';
    }

    // Retrieve the gallery from the database
    $gallery = get_option($atts['id']);
    if (!$gallery || empty($gallery['items'])) {
        return '<p>Error: Gallery not found or has no items.</p>';
    }

    // Start rendering the gallery container
    $output = '<div class="simple-gallery">';

    // Loop through gallery items
    foreach ($gallery['items'] as $item) {
        $output .= '<div class="gallery-item">';

        // Wrap the image in a lightbox-enabled link
        if (!empty($item['image'])) {
            $output .= '<a href="' . esc_url($item['image']) . '" data-lightbox="gallery" title="' . esc_attr($item['title']) . '">';
            $output .= '<img src="' . esc_url($item['image']) . '" alt="' . esc_attr($item['title']) . '">';
            $output .= '</a>';
        }

        // Render the title (if exists)
        if (!empty($item['title'])) {
            $output .= '<h3>' . esc_html($item['title']) . '</h3>';
        }

        // Render the subtitle (if exists)
        if (!empty($item['subtitle'])) {
            $output .= '<p>' . esc_html($item['subtitle']) . '</p>';
        }

        $output .= '</div>'; // Close gallery-item
    }

    $output .= '</div>'; // Close simple-gallery

    return $output;
}
add_shortcode('gallery', 'render_simple_gallery');
