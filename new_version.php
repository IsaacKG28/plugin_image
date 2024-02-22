<?php
/*
Plugin Name: Image Checker
Description: Éste plugin verifica que existan imágenes en la galería del producto para que su status pueda ser 'publish' en caso contrario publica los productos como borrador.
Version: 1.0
Author: Fernando Isaac Gonzalez Medina
*/
//Empieza la lógica para BEAR Bulk
//Acción que cambia un producto publicado a borrador si no tiene imágenes
add_action('save_post', 'check_gallery_status', 10, 3);

function check_gallery_status($post_ID, $post, $update) {
    // Solo queremos hacer esto para productos
    if ($post->post_type == 'product') {
        // Obtener los IDs de las imágenes adjuntas al producto
        $attachment_ids = get_post_meta($post_ID, '_product_image_gallery', true);

        // Verificar si la galería está vacía
        if (empty($attachment_ids) && $post->post_status == 'publish') {
            // La galería está vacía, establecer el estado como 'draft'
            wp_update_post(array(
                'ID' => $post_ID,
                'post_status' => 'draft'
            ));
        }
    }
}

add_action('init', 'check_existing_products');

function check_existing_products() {
    // Obtener todos los productos publicados
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids', // Solo obtener los IDs de los productos
    );
    $product_ids = get_posts($args);

    // Verificar cada producto
    $products_to_process = 100; // Puedes ajustar este número según tus necesidades
    foreach ($product_ids as $product_id) {
        $product = get_post($product_id); // Obtener el producto solo cuando sea necesario
        check_gallery_status($product_id, $product, false);

        // Agregar un break después de procesar un número específico de productos
       
        if (--$products_to_process <= 0) {
            break;
        }
    }
}
//Parte que hookea correctamente BEAR para cambiar el status en tiempo real
add_filter('woobe_new_product_status', function ($status) {
    // Verificar si la galería está vacía
    if (empty(get_post_gallery(get_the_ID(), false))) {
        // La galería está vacía, establecer el estado como 'draft'
        return 'draft';
    } else {
        // La galería no está vacía, mantener el estado actual
        return $status;
    }
});
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

                // Selecciona el elemento span que quieres observar
                let spanElement = document.getElementById('post-status-display');
                let publishButton = document.getElementById('publish');

                // Configura el objeto de opciones para el observador
                let config = { subtree: true, childList: true };

                // Crea un nuevo observador con una función de devolución de llamada
                let observer = new MutationObserver(function(mutationsList, observer) {
                    // Itera a través de las mutaciones
                    for (let mutation of mutationsList) {
                        // Verifica si se agregaron o eliminaron nodos dentro del span
                        if (mutation.type === 'childList') {
                            console.log('Se realizaron cambios dentro del span');
                            jQuery(document).ready(function($) {
                let gallery_images = $('li.image').length;
                let edit_post_status_display = $('.edit-post-status hide-if-no-js').css('display');
                if (gallery_images == 0 && edit_post_status_display != 'none') {
                    $('#publish').prop('disabled', true);
                }
                $('body').on('DOMNodeInserted', 'li.image', function () {
                    gallery_images++;
                    if (gallery_images > 0 || edit_post_status_display == 'none') {
                        $('#publish').prop('disabled', false);
                    }
                });
                $('body').on('DOMNodeRemoved', 'li.image', function () {
                    gallery_images--;
                    if (gallery_images == 0 && edit_post_status_display != 'none') {
                        $('#publish').prop('disabled', true);
                    }
                });
            });
                            }
                        }
                    
                });

                        // Inicia la observación del elemento span con la configuración dada
                        observer.observe(spanElement, config);
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
}
?>
