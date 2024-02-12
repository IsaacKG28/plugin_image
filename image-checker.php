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
?>
<?php
// PHP
$showModal = true;
?>

<!DOCTYPE html>
<html>
<head>
    <style>
    /* CSS */
    .modal {
      display: none; 
      position: fixed; 
      z-index: 1; 
      padding-top: 100px; 
      left: 0;
      top: 0;
      width: 100%; 
      height: 100%; 
      overflow: auto; 
      background-color: rgb(0,0,0); 
      background-color: rgba(0,0,0,0.4); 
    }

    .modal-content {
      background-color: #fefefe;
      margin: auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
    }

    .close {
      color: #aaaaaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: #000;
      text-decoration: none;
      cursor: pointer;
    }
    </style>
</head>
<body>

<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close">×</span>
    <p>Recuerda que si intentas publicar un nuevo producto sin imágenes en la galería se guardará automáticamente como borrador.</p>
  </div>
</div>

<script>
// JavaScript
window.onload = function() {
    <?php if ($showModal) { ?>
        var modal = document.getElementById("myModal");
        modal.style.display = "block";
    <?php } ?>

    // Cuando el usuario haga clic en <span> (x), cierra la ventana modal
    document.getElementsByClassName("close")[0].onclick = function() {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>

<?php
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
?>
