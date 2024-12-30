jQuery(document).ready(function ($) {
    // Open Media Uploader
    $('body').on('click', '.upload-image', function (e) {
        e.preventDefault();

        const button = $(this); // The button clicked
        const inputField = button.prev('.image-url'); // The input field to update
        const thumbnailContainer = button.closest('td').find('.image-thumbnail'); // The thumbnail container

        // Open WordPress Media Uploader
        const mediaUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false // Single image upload
        });

        mediaUploader.on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            inputField.val(attachment.url); // Set the image URL in the input field

            // Update the thumbnail preview
            thumbnailContainer.html(`<img src="${attachment.url}" style="max-width: 100px; height: auto;">`);
        });

        mediaUploader.open();
    });

    // Add new item to the gallery table
    $('#add-item').on('click', function () {
        const newRow = `
            <tr>
                <td>
                    <div class="image-thumbnail"></div>
                    <input type="text" name="image_urls[]" class="image-url form-control" readonly>
                    <button type="button" class="btn btn-secondary upload-image">Select Image</button>
                </td>
                <td>
                    <input type="text" name="titles[]" class="form-control" placeholder="Image Title" required>
                </td>
                <td>
                    <input type="text" name="subtitles[]" class="form-control" placeholder="Image Subtitle">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-item">Remove</button>
                </td>
            </tr>`;
        $('#gallery-items').append(newRow);
    });

    // Remove an item from the gallery table
    $('body').on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
    });
});
