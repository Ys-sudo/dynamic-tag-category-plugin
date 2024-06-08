<?php
/*
Plugin Name: Dynamic Tag to Category Products
Plugin URI: https://github.com/Ys-sudo/dynamic-tag-category-plugin
Description: Adds a user-specified tag to all products in a user-defined category.
Version: 1.0
Author: George Lazaridis
Author URI: https://github.com/Ys-sudo
License: MIT
*/

// Ensure the script runs only in the admin area
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Function to add a tag to all products in a specific category
function add_tag_to_category_products($category_identifier, $tag_name) {
    // Get category by slug or name
    $category = get_term_by('slug', $category_identifier, 'product_cat');
    if (!$category) {
        $category = get_term_by('name', $category_identifier, 'product_cat');
    }

    // Check if the category exists
    if (!$category) {
        echo '<div class="error notice"><p>Category not found: ' . esc_html($category_identifier) . '.</p></div>';
        return;
    }

    // Set up the WP_Query arguments
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category->term_id,
            ),
        ),
    );

    // Run the query
    $query = new WP_Query($args);

    // Check if there are any products found
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Get the current product ID
            $product_id = get_the_ID();
            
            // Add the user-specified tag to the product
            wp_set_post_terms($product_id, $tag_name, 'product_tag', true);
        }
        
        // Reset post data
        wp_reset_postdata();
        
        echo '<div class="updated notice"><p>Tag "' . esc_html($tag_name) . '" has been added to all products in the category "' . esc_html($category->name) . '".</p></div>';
    } else {
        echo '<div class="error notice"><p>No products found in the category "' . esc_html($category->name) . '".</p></div>';
    }
}

// Hook the function to an admin action
add_action('admin_menu', 'add_category_tag_menu');

function add_category_tag_menu() {
    add_submenu_page(
        'tools.php',                // Parent slug
        'Add Tag to Category',      // Page title
        'Add Tag to Category',      // Menu title
        'manage_options',           // Capability
        'add-tag-to-category',      // Menu slug
        'add_category_tag_page'     // Function to display the page content
    );
}

function add_category_tag_page() {
    if (isset($_POST['category_identifier']) && isset($_POST['tag_name'])) {
        $category_identifier = sanitize_text_field($_POST['category_identifier']);
        $tag_name = sanitize_text_field($_POST['tag_name']);
        add_tag_to_category_products($category_identifier, $tag_name);
    }

    ?>
    <div class="wrap">
        <h1>Add Tag to Products in Category</h1>
        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Category Name or Slug</th>
                    <td><input type="text" name="category_identifier" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tag Name</th>
                    <td><input type="text" name="tag_name" required /></td>
                </tr>
            </table>
            <?php submit_button('Add Tag'); ?>
        </form>
    </div>
    <?php
}
?>
