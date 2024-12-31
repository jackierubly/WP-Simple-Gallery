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
    <div class="wrap" style="font-family: Arial, sans-serif;">
        <h1 style="font-size: 36px; font-weight: bold; color: #333; text-align: center;">Manage Galleries</h1>
        <div style="text-align: center; margin: 20px 0;">
            <a href="?page=edit-simple-gallery"
                class="button button-primary"
                style="background-color: #008080; color: white; font-size: 20px; padding: 15px 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-transform: uppercase; font-weight: bold; text-decoration: none;">
                Create New Gallery
            </a>
        </div>
        <table class="widefat fixed" style="border-collapse: collapse; width: 100%; margin-top: 20px; font-size: 18px; background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 15px; font-size: 18px; text-align: left; color: #555;">Gallery Name</th>
                    <th style="padding: 15px; font-size: 18px; text-align: left; color: #555;">Gallery Preview</th>
                    <th style="padding: 15px; font-size: 18px; text-align: left; color: #555;">Shortcode <i>(Place this into the page)</i></th>
                    <th style="padding: 15px; font-size: 18px; text-align: left; color: #555;">Actions</th>
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
                        <tr style="border-bottom: 1px solid #eee; transition: background-color 0.3s;">
                            <td style="padding: 15px; font-weight: bold; color: #333;">
                                <?php echo esc_html($gallery_data['name']); ?>
                            </td>
                            <td style="padding: 15px;">
                                <div style="display: flex; gap: 10px;">
                                    <?php foreach ($preview_images as $preview): ?>
                                        <?php if (!empty($preview['image'])): ?>
                                            <img src="<?php echo esc_url($preview['image']); ?>" alt="Preview"
                                                style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                        <?php else: ?>
                                            <em style="color: #ccc;">No image</em>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span id="shortcode-<?php echo esc_attr($gallery->option_name); ?>"
                                        style="font-size: 16px; color: #008080; background: #eef6f6; padding: 5px 10px; border-radius: 5px; display: inline-block;">
                                        <?php echo $shortcode; ?>
                                    </span>
                                    <button class="button copy-shortcode"
                                        data-clipboard-target="#shortcode-<?php echo esc_attr($gallery->option_name); ?>"
                                        style="background-color: #008080; color: white; font-size: 14px; padding: 8px 15px; border-radius: 5px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
                                        Copy
                                    </button>
                                </div>
                            </td>
                            <td style="padding: 15px;">
                                <a href="?page=edit-simple-gallery&gallery_id=<?php echo esc_attr($gallery->option_name); ?>"
                                    class="button"
                                    style="background-color: #008080; color: white; font-size: 16px; padding: 10px 20px; border-radius: 8px; margin-right: 10px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); text-decoration: none;">
                                    Edit
                                </a>
                                <a href="?page=simple-gallery&action=delete&gallery_id=<?php echo esc_attr($gallery->option_name); ?>"
                                    class="button delete"
                                    data-gallery-id="<?php echo esc_attr($gallery->option_name); ?>"
                                    style="background-color: #ff4d4d; color: white; font-size: 16px; padding: 10px 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); text-decoration: none;">
                                    Delete
                                </a>
                                <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center;">
                                    <div style="background: white; padding: 20px; border-radius: 10px; width: 400px; text-align: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                        <h3 style="font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px;">Confirm Deletion</h3>
                                        <p style="font-size: 16px; color: #666; margin-bottom: 30px;">Are you sure you want to delete this gallery? This action cannot be undone.</p>
                                        <div style="display: flex; justify-content: center; gap: 10px;">
                                            <button id="confirmDelete" class="button"
                                                style="background-color: #ff4d4d; color: white; padding: 10px 20px; font-size: 16px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Delete</button>
                                            <button id="cancelDelete" class="button"
                                                style="background-color: #17a2b8; color: white; padding: 10px 20px; font-size: 16px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Cancel</button>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const deleteButtons = document.querySelectorAll('.delete');
                                        const modal = document.getElementById('deleteModal');
                                        const confirmDeleteButton = document.getElementById('confirmDelete');
                                        const cancelDeleteButton = document.getElementById('cancelDelete');

                                        let deleteHref = null;

                                        // Attach event listeners to delete buttons
                                        deleteButtons.forEach(button => {
                                            button.addEventListener('click', function(event) {
                                                event.preventDefault(); // Prevent immediate navigation
                                                deleteHref = this.getAttribute('href'); // Store the original href
                                                modal.style.display = 'flex'; // Show modal
                                                cancelDeleteButton.focus(); // Set focus on the Cancel button
                                            });
                                        });

                                        // Confirm deletion
                                        confirmDeleteButton.addEventListener('click', function() {
                                            if (deleteHref) {
                                                window.location.href = deleteHref; // Proceed with deletion
                                            }
                                        });

                                        // Cancel deletion
                                        cancelDeleteButton.addEventListener('click', function() {
                                            modal.style.display = 'none'; // Hide the modal
                                            deleteHref = null; // Reset the href
                                        });

                                        // Close modal on outside click
                                        modal.addEventListener('click', function(event) {
                                            if (event.target === modal) {
                                                modal.style.display = 'none'; // Hide the modal
                                                deleteHref = null; // Reset the href
                                            }
                                        });
                                    });
                                </script>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #666; font-size: 20px;">
                            No galleries found.
                        </td>
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
    <div class="wrap" style="font-family: Arial, sans-serif;">
        <h1 style="font-size: 36px; font-weight: bold; color: #333; text-align: center; margin-bottom: 20px;">
            <?php echo $gallery ? 'Edit Gallery' : 'Create New Gallery'; ?>
        </h1>

        <!-- Gallery Settings Section -->
        <form method="POST" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); font-size: 18px; color: #444;">
            <div class="mb-4" style="margin-bottom: 40px;">
                <h2 class="mb-3" style="font-size: 26px; font-weight: bold; color: #444; border-bottom: 2px solid #f4f4f4; padding-bottom: 10px;">Gallery Settings</h2>

                <div class="mb-3" style="margin-bottom: 20px;">
                    <label for="gallery_name" class="form-label" style="font-size: 18px; font-weight: bold;">Title</label>
                    <input type="text" name="gallery_name" id="gallery_name"
                        class="form-control"
                        style="width: 100%; padding: 15px; border-radius: 5px; border: 1px solid #ddd;"
                        value="<?php echo $gallery ? esc_attr($gallery['name']) : ''; ?>"
                        placeholder="Enter gallery title" required>
                </div>

                <div class="mb-3" style="margin-bottom: 20px;">
                    <label for="gallery_description" class="form-label" style="font-size: 18px; font-weight: bold;">Description</label>
                    <textarea name="gallery_description" id="gallery_description"
                        class="form-control" rows="3"
                        style="width: 100%; padding: 15px; border-radius: 5px; border: 1px solid #ddd;"
                        placeholder="Enter gallery description"><?php echo $gallery ? esc_textarea($gallery['description']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Gallery Items Table -->
            <h2 style="font-size: 26px; font-weight: bold; color: #444; margin-bottom: 20px; border-bottom: 2px solid #f4f4f4; padding-bottom: 10px;">Gallery Items</h2>
            <table class="widefat fixed" style="width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <thead style="background-color: #f8f9fa; border-bottom: 2px solid #ddd;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">Image</th>
                        <th style="padding: 15px; text-align: left;">Title</th>
                        <th style="padding: 15px; text-align: left;">Subtitle</th>
                        <th style="padding: 15px; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody id="gallery-items">
                    <?php if ($gallery && !empty($gallery['items'])) : ?>
                        <?php foreach ($gallery['items'] as $item) : ?>
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px;">
                                    <div class="image-thumbnail" style="text-align: center;">
                                        <img src="<?php echo esc_url($item['image']); ?>" style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
                                    </div>
                                    <input type="text" name="image_urls[]" class="image-url form-control"
                                        style="width: 100%; margin-top: 10px; padding: 10px; border-radius: 5px; border: 1px solid #ddd;"
                                        value="<?php echo esc_url($item['image']); ?>" readonly>
                                    <button type="button" class="button upload-image"
                                        style="
        background-color: #007bff; 
        color: white; 
        font-size: 16px; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: bold; 
        border: none; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        transition: background-color 0.3s, transform 0.2s;">
                                        <span style="display: inline-block; background: white; color: #007bff; border-radius: 50%; padding: 5px; display: flex; align-items: center; justify-content: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; fill: #007bff;" viewBox="0 0 16 16">
                                                <path d="M4.5 1a.5.5 0 0 1 .5.5v1h6v-1a.5.5 0 0 1 1 0v1h1a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h1v-1a.5.5 0 0 1 .5-.5zM1 4.5v9a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1h-11a1 1 0 0 0-1 1zm4.5 3.5a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0zM5 8a3 3 0 1 0 6 0 3 3 0 0 0-6 0zm10-2a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 1 .5-.5z" />
                                            </svg>
                                        </span>
                                        Select Image
                                    </button>

                                </td>
                                <td style="padding: 15px;">
                                    <input type="text" name="titles[]" value="<?php echo esc_attr($item['title']); ?>"
                                        placeholder="Image Title" required
                                        class="form-control"
                                        style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td style="padding: 15px;">
                                    <input type="text" name="subtitles[]" value="<?php echo esc_attr($item['subtitle']); ?>"
                                        placeholder="Image Subtitle"
                                        class="form-control"
                                        style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td style="padding: 15px; text-align: center;">
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
            <div style="text-align: center; margin-top: 30px;">
                <button type="button" id="add-item" class="button"
                    style="background-color: #17a2b8; color: white; padding: 12px 25px; font-size: 18px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; margin-right: 20px;">Add Item</button>
                <button type="submit" name="save_gallery" class="button button-primary"
                    style="background-color: #008080; color: white; padding: 12px 25px; font-size: 18px; border-radius: 5px; font-weight: bold; border: none; cursor: pointer;">Save Changes</button>
            </div>
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
