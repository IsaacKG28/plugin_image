<?php
/*
Plugin Name: Image Checker
Description: Éste plugin verifica que existan imágenes en la galería del producto para que su status pueda ser Publicado en caso contrario publica los productos como borrador.
Version: 1.0
Author: Fernando Isaac Gonzalez Medina
*/
//Empieza la lógica para BEAR Bulk
//Acción que cambia un producto publicado a borrador si no tiene imágenes
add_action('added_post_meta', 'check_gallery_status_on_update', 10, 4);
add_action('updated_post_meta', 'check_gallery_status_on_update', 10, 4);
add_action('deleted_post_meta', 'check_gallery_status_on_update', 10, 4);

function check_gallery_status_on_update($meta_id, $post_ID, $meta_key, $meta_value) {
    if (get_post_type($post_ID) == 'product' && $meta_key == '_product_image_gallery') {
        if (empty(get_post_meta($post_ID, '_product_image_gallery', true))) {
            wp_update_post(array(
                'ID' => $post_ID,
                'post_status' => 'draft',
            ));
        }
    }
}

add_action('init', 'check_existing_products');
function check_existing_products() {
    $paged = 1;
    $posts_per_page = 100;

    do {
        $meta_query = array(
            'key' => '_product_image_gallery_checked',
            'compare' => 'NOT EXISTS',
        );

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'fields' => 'ids',
            'meta_query' => $meta_query,
        );

        $query = new WP_Query($args);
        $products = $query->posts;

        foreach ($products as $product_id) {
            check_gallery_status_on_update(null, $product_id, '_product_image_gallery', null);
            update_post_meta($product_id, '_product_image_gallery_checked', true);
        }

        wp_reset_postdata();
        $paged++;
    } while(count($products) == $posts_per_page);
}

//Aqui termina lógica de BEAR Bulk

//acción para productos en woocommerce
add_action('admin_footer', 'disable_publish_button');
function disable_publish_button() {
    global $post;
    // Verifica si $post es una instancia válida de WP_Post
    if (!is_a($post, 'WP_Post')) {
        return; // Salir de la función si $post no es válido
    }
    // Ahora puedes acceder a las propiedades de $post de manera segura
    if ($post->post_type == 'product') { // Verifica si el tipo de post es 'product'
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                let gallery_images = $('li.image').length;
                let checkConditions = function() {
                    let edit_post_status_display = $('.edit-post-status.hide-if-no-js').css('display');
                    if (gallery_images == 0 && edit_post_status_display != 'none') {
                        $('#publish').prop('disabled', true);
                    } else {
                        $('#publish').prop('disabled', false);
                    }
                };
                checkConditions();
                $('body').on('DOMNodeInserted', 'li.image', function () {
                    gallery_images++;
                    checkConditions();
                });
                $('body').on('DOMNodeRemoved', 'li.image', function () {
                    gallery_images--;
                    checkConditions();
                });
                // Observar cambios en el atributo 'style' del elemento '.edit-post-status.hide-if-no-js'
                let targetNode = document.querySelector('.edit-post-status.hide-if-no-js');
                let config = { attributes: true, attributeFilter: ['style'] };
                let callback = function(mutationsList, observer) {
                    for(let mutation of mutationsList) {
                        if (mutation.type === 'attributes') {
                            checkConditions();
                        }
                    }
                };
                let observer = new MutationObserver(callback);
                observer.observe(targetNode, config);
            });
        </script>
        <?php
    }
}
//JS para ventana modal
add_action('admin_enqueue_scripts', 'enqueue_my_custom_popup_script');
function enqueue_my_custom_popup_script() {
    $screen = get_current_screen();
    if ( $screen->id == "product_page_woobe" ) {
        wp_enqueue_script('my_custom_popup_script', plugins_url('/my_custom_popup.js', __FILE__), array('jquery', 'thickbox'), false, true);
         // Enqueueing CSS file
         wp_enqueue_style('my_custom_popup_style', plugins_url('/my_custom_popup.css', __FILE__));
    }
}
add_action('admin_footer', 'my_custom_popup');
function my_custom_popup() {
    $screen = get_current_screen();
    if ( $screen->id == "product_page_woobe") {
        echo '<div id="my_custom_popup">
                <p class = "text-modal">Recuerda que si pones como "Publicado" un artículo sin imágenes en la galería, éste se cambiará automáticamente a "Borrador".</p>
              </div>';
    }
//TEST VERSION
}
?> 
