<?php
/*
Plugin Name: Deshabilitar Publicar Sin Imágenes
Description: Deshabilita el botón de publicar en productos de WooCommerce si no hay imágenes en la galería.
Version: 1.0
Author: Fernando Isaac Gonzalez Medina
*/
add_action('admin_footer', 'disable_publish_button');
function disable_publish_button() {
    global $post;
    if($post->post_type == 'product') { // Verifica si el tipo de post es 'product'
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var gallery_images = $('li.image').length;
                if(gallery_images == 0) { // Verifica si la galería de imágenes está vacía
                    $('#publish').prop('disabled', true); // Deshabilita el botón de publicar
                }
                $('body').on('DOMNodeInserted', 'li.image', function () {
                    $('#publish').prop('disabled', false); // Habilita el botón de publicar si se agrega una imagen a la galería
                });
                $('body').on('DOMNodeRemoved', 'li.image', function () {
                    // var gallery_images = $('li.image').length;
                    // if (gallery_images == 0) //ESTAS LINEAS EVITABAN LA ACTUALIZACION DEL BOTON
                        $('#publish').prop('disabled', true); // Deshabilita el botón de publicar si se eliminan todas las imágenes de la galería   
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
            // No hay imágenes en la galería, mostramos una ventana modal
            echo '<script type="text/javascript">
                alert("No puedes publicar este producto sin agregar imágenes a la galería.");
            </script>';
            // Cambiamos el estado a 'Borrador'
            $post->post_status = 'draft';
            wp_update_post($post);
        }
    }
}
?>
