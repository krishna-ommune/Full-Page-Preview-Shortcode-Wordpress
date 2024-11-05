<?php
/*
Plugin Name: Full Page Preview Shortcode
Description: A plugin to add multiple URLs and generate shortcodes to display full page previews.
Version: 1.3
Author: Your Name
*/

// Enqueue necessary scripts and styles
function url_preview_enqueue_scripts() {
    wp_enqueue_style('url-preview-style', plugin_dir_url(__FILE__) . 'url-preview-style.css');
    wp_enqueue_script('url-preview-script', plugin_dir_url(__FILE__) . 'url-preview-script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'url_preview_enqueue_scripts');

// Create Admin Menu
function fpp_shortcode_menu() {
    add_menu_page(
        'Full Page Preview',
        'Page Previews',
        'manage_options',
        'full-page-preview',
        'fpp_admin_page',
        'dashicons-admin-links',
        100
    );
}
add_action('admin_menu', 'fpp_shortcode_menu');

// Admin Page Content
function fpp_admin_page() {
    ?>
    <div class="wrap">
        <h1>Full Page Preview Shortcode Generator</h1>
        <form method="post" action="options.php">
            <?php 
            settings_fields('fpp-settings-group'); 
            do_settings_sections('fpp-settings-group');
            ?>
            <h2>Add URLs</h2>
            <div id="url-list">
                <?php 
                $urls = get_option('fpp_urls', array());
                foreach ($urls as $index => $url) {
                    echo '<div class="url-item">';
                    echo '<input type="url" name="fpp_urls[]" value="' . esc_attr($url) . '" required />';
                    echo '<button type="button" class="button remove-url">Remove</button>';
                    echo '<button type="button" class="button copy-shortcode" data-id="' . esc_attr($index) . '">Copy Shortcode</button>';
                    echo '</div>';
                }
                ?>
            </div>
            <button type="button" class="button" id="add-url">Add URL</button>
            <?php submit_button(); ?>
        </form>
        <h2>Available Shortcodes</h2>
        <p>Use these shortcodes in your posts or pages:</p>
        <ul>
            <?php 
            if ($urls) {
                foreach ($urls as $index => $url) {
                    echo '<li>[full_page_preview id="' . esc_attr($index) . '"] <button type="button" class="button copy-shortcode" data-id="' . esc_attr($index) . '">Copy Shortcode</button></li>';
                }
            } else {
                echo '<li>No URLs added yet.</li>';
            }
            ?>
        </ul>
    </div>

    <script type="text/javascript">
        document.getElementById('add-url').addEventListener('click', function() {
            var newField = document.createElement('div');
            newField.className = 'url-item';
            newField.innerHTML = '<input type="url" name="fpp_urls[]" value="" required /> <button type="button" class="button remove-url">Remove</button> <button type="button" class="button copy-shortcode" data-id="">Copy Shortcode</button>';
            document.getElementById('url-list').appendChild(newField);

            newField.querySelector('.remove-url').addEventListener('click', function() {
                newField.remove();
            });
        });

        document.querySelectorAll('.remove-url').forEach(function(button) {
            button.addEventListener('click', function() {
                button.parentNode.remove();
            });
        });

        // Copy shortcode to clipboard
        document.querySelectorAll('.copy-shortcode').forEach(function(button) {
            button.addEventListener('click', function() {
                var shortcodeId = button.getAttribute('data-id');
                var shortcode = '[full_page_preview id="' + shortcodeId + '"]';
                
                // Create a temporary input to copy the shortcode
                var tempInput = document.createElement('input');
                tempInput.value = shortcode;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                // Feedback to the user
                alert('Shortcode copied to clipboard: ' + shortcode);
            });
        });
    </script>
    <style>
        .url-item {
            margin-bottom: 10px;
        }
        .url-item input {
            width: 70%;
            display: inline-block;
        }
        .url-item .button {
            margin-left: 10px;
        }
        h2 {
            margin-top: 20px;
        }
    </style>
    <?php
}

// Register Settings
function fpp_register_settings() {
    register_setting('fpp-settings-group', 'fpp_urls');
}
add_action('admin_init', 'fpp_register_settings');

// Generate Shortcode for Full Page Preview
function fpp_shortcode($atts) {
    $urls = get_option('fpp_urls', array());
    if (!$urls) return 'No URLs available.';

    $atts = shortcode_atts(array('id' => 0), $atts, 'full_page_preview');
    $id = intval($atts['id']);

    if (!isset($urls[$id])) return 'Invalid URL ID.';

    $external_url = esc_url($urls[$id]);

    // Return the iframe to show the full page preview
    $output = '<div class="url-preview">';
    $output .= '<iframe src="' . $external_url . '" style="width: 100%; height: 600px; border: none;"></iframe>'; // Set height to 600px or desired height
    $output .= '</div>';

    return $output;
}
add_shortcode('full_page_preview', 'fpp_shortcode');
