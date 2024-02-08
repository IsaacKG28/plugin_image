<?php
/*
Plugin Name: Disable Publish Button
Description: Deshabilita el botón de publicar producto en WooCommerce si no hay imágenes en la galería.
Version: 1.0
Author: Fernando Isaac Gonzalez Medina
*/

add_action('admin_footer', 'disable_publish_button');
function disable_publish_button() {
    global $post;
    if($post->post_type == 'product') { // verifica si el tipo de post es 'product'
        $gallery_images = get_post_meta($post->ID, '_product_image_gallery', true);
        if(empty($gallery_images)) { // verifica si la galería de imágenes está vacía
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#publish').prop('disabled', true); // deshabilita el botón de publicar
                    $('body').on('change', '.wp-picker-input-wrap input', function() { // escucha los cambios en la galería de imágenes
                        var gallery_images = $('#_product_image_gallery').val();
                        if(gallery_images != '') {
                            $('#publish').prop('disabled', false); // habilita el botón de publicar si la galería de imágenes no está vacía
                        }
                    });
                });
            </script>
            <?php
        }
    }
}
?>
