<?php
/*
Plugin Name: 0 CSS personalizado por cada Post
Plugin URI: https://www.webyblog.es
Description: Añade CSS personalizado a cada tipo de entrada personalizada, post y pagina.
Version: 1.0
Author: Juan Luis Martel
Author URI: https://www.webyblog.es
*/


/*
La función custom_css_for_selective_content() crea una página de opciones en el menú de administración de WordPress. Esta página permite a los usuarios seleccionar los tipos de publicaciones personalizadas a los que desean aplicar CSS personalizado. 
*/

function custom_css_for_selective_content() {
  add_options_page(
    'Custom CSS for Selective Content',
    'Custom CSS',
    'manage_options',
    'custom_css_for_selective_content',
    'custom_css_for_selective_content_page'
  );
}

/*
La línea add_action('admin_menu', 'custom_css_for_selective_content') engancha la función a la acción admin_menu, por lo que se ejecuta cuando se está construyendo el menú de administración.
*/

add_action('admin_menu', 'custom_css_for_selective_content');

/* 
La función custom_css_for_selective_content_page() define el contenido de la página de opciones en el menú de administración. Muestra una lista de tipos de publicaciones personalizadas y permite al usuario seleccionar aquellos a los que desea aplicar CSS personalizado.
*/

function custom_css_for_selective_content_page() {
  if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
  }
  
  $post_types = get_post_types(array('public' => true), 'objects');
  $selected_post_types = get_option('custom_css_for_selective_content_post_types', array());
  ?>
  <div class="wrap">
    <h1>Custom CSS for Selective Content</h1>
    <form method="post" action="options.php">
      <?php settings_fields('custom_css_for_selective_content_group'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Post Types</th>
          <td>
            <?php foreach($post_types as $post_type) : ?>
              <label for="custom_css_for_selective_content_post_types_<?php echo esc_attr($post_type->name); ?>">
                <input type="checkbox" name="custom_css_for_selective_content_post_types[]" id="custom_css_for_selective_content_post_types_<?php echo esc_attr($post_type->name); ?>" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $selected_post_types)); ?> />
                <?php echo esc_html($post_type->label); ?>
              </label><br />
            <?php endforeach; ?>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

/* 
La función custom_css_for_selective_content_register_settings() registra la configuración del complemento en WordPress.
*/

function custom_css_for_selective_content_register_settings() {
  register_setting('custom_css_for_selective_content_group', 'custom_css_for_selective_content_post_types');
}
add_action('admin_init', 'custom_css_for_selective_content_register_settings');

/* 
La función custom_css_for_selective_content_add_meta_box() agrega un meta box (cuadro de entrada) en la pantalla de edición de cada tipo de publicación personalizada seleccionada. Este meta box permite a los usuarios agregar CSS personalizado a cada publicación individual.
*/

function custom_css_for_selective_content_add_meta_box() {
  $selected_post_types = get_option('custom_css_for_selective_content_post_types', array());
  
  foreach ($selected_post_types as $post_type) {
    add_meta_box(
'custom_css_for_selective_content',
'Custom CSS',
'custom_css_for_selective_content_callback',
$post_type,
'normal',
'high'
);
}
}
add_action('add_meta_boxes', 'custom_css_for_selective_content_add_meta_box');

/* 
La función custom_css_for_selective_content_callback() muestra el contenido del meta box, que es un área de texto donde los usuarios pueden ingresar su CSS personalizado.
*/

function custom_css_for_selective_content_callback($post) {
$custom_css = get_post_meta($post->ID, '_custom_css', true);
wp_nonce_field('custom_css_for_selective_content_nonce', 'custom_css_for_selective_content_nonce');
?>
  <textarea name="custom_css" id="custom_css" rows="10" style="width: 100%;"><?php echo esc_html($custom_css); ?></textarea>
  <?php
}

/* 
La función custom_css_for_selective_content_save() se encarga de guardar el CSS personalizado ingresado por el usuario cuando se guarda la publicación.
*/

function custom_css_for_selective_content_save($post_id) {
  if (!isset($_POST['custom_css_for_selective_content_nonce'])) {
    return;
  }
  
  if (!wp_verify_nonce($_POST['custom_css_for_selective_content_nonce'], 'custom_css_for_selective_content_nonce')) {
    return;
  }
  
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }
  
  if (!current_user_can('edit_post', $post_id)) {
    return;
  }
  
  if (!isset($_POST['custom_css'])) {
    return;
  }
  
  update_post_meta($post_id, '_custom_css', sanitize_text_field($_POST['custom_css']));
}
add_action('save_post', 'custom_css_for_selective_content_save');

/* 
La función custom_css_for_selective_content_frontend() se ejecuta en el front-end de WordPress e imprime el CSS personalizado en la etiqueta <head> del documento HTML para las publicaciones individuales que tienen CSS personalizado.
*/

function custom_css_for_selective_content_frontend() {
  if (!is_singular()) {
    return;
  }
  
  $post_id = get_the_ID();
  $custom_css = get_post_meta($post_id, '_custom_css', true);
  
  if (!empty($custom_css)) {
    echo '<style type="text/css">' . $custom_css . '</style>';
  }
}

/* 
Finalmente, las funciones se enganchan a las acciones de WordPress apropiadas mediante add_action() para que se ejecuten en los momentos adecuados.
*/
add_action('wp_head', 'custom_css_for_selective_content_frontend', 999);

