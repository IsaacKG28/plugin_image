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
                var gallery_images = $('.product_images.ui-sortable li').length;
                if(gallery_images == 0) { // Verifica si la galería de imágenes está vacía
                    $('#publish').prop('disabled', true); // Deshabilita el botón de publicar
                }
                $('body').on('DOMNodeInserted', '.product_images.ui-sortable li', function () {
                    $('#publish').prop('disabled', false); // Habilita el botón de publicar si se agrega una imagen a la galería
                });
                $('body').on('DOMNodeRemoved', '.product_images.ui-sortable li', function () {
                    if($('.product_images.ui-sortable li').length == 0) {
                        $('#publish').prop('disabled', true); // Deshabilita el botón de publicar si se eliminan todas las imágenes de la galería
                    }
                });
            });
        </script>
        <?php
    }
}

?>
