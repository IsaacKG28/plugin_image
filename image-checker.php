<?php
/*
Plugin Name: Image Checker
Description: Deshabilita el botón de publicar en productos de WooCommerce si no hay imágenes en la galería.
Version: 1.0
Author: Fernando Isaac Gonzalez Medina
*/
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
                var gallery_images = $('li.image').length;
                if(gallery_images == 0) {
                    $('#publish').prop('disabled', true);
                }
                $('body').on('DOMNodeInserted', 'li.image', function () {
                    $('#publish').prop('disabled', false);
                });
                $('body').on('DOMNodeRemoved', 'li.image', function () {
                    // var gallery_images = $('li.image').length;
                    // if (gallery_images == 0)
                        $('#publish').prop('disabled', true);
                });
            });
        </script>
        <?php
    }
}
//Accion para BEAR BULK EDITOR
add_action('save_post', 'check_product_images', 10, 3);
function check_product_images($post_id, $post, $update) {
    if ($post->post_type == 'product' && $post->post_status == 'publish') {
        $product = wc_get_product($post_id);
        $attachment_ids = $product->get_gallery_image_ids();
        if (empty($attachment_ids)) {
            
            // Cambiamos el estado a 'Borrador'
            $post->post_status = 'draft';
            wp_update_post($post);
        }
    }
} 
add_action('admin_enqueue_scripts', 'enqueue_my_custom_popup_script');
function enqueue_my_custom_popup_script() {
    $screen = get_current_screen();
    if ( $screen->id == "product_page_woobe" ) {
        wp_enqueue_script('my_custom_popup_script', plugins_url('/my_custom_popup.js', __FILE__), array('jquery', 'thickbox'), false, true);
    }
}

add_action('admin_footer', 'my_custom_popup');
function my_custom_popup() {
    $screen = get_current_screen();
    if ( $screen->id == "product_page_woobe" ) {
        echo '<div id="my_custom_popup" style="display: none;">
                <p>Recuerda que si pones como "Publicado" un artículo sin imágenes en la galería, éste se cambiará automáticamente a "Borrador".</p>
              </div>';
    }
}

?>
