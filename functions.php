<?php

function folio_assets() {
    wp_enqueue_style( 'stylecss', get_template_directory_uri() . '/assets/css/style.css' );
    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.css' );
}
add_action( 'wp_enqueue_scripts', 'folio_assets' );

show_admin_bar(false);

add_theme_support( 'post-thumbnails' );

//Custom image for product start
function custom_product_image_meta_box() {  
    add_meta_box(
        'product_image_meta_box',
        'Выбрать изображение',
        'custom_product_image_meta_box_callback',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_product_image_meta_box');


function custom_product_image_meta_box_callback($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'product_image_nonce');
    $image_url = get_post_meta($post->ID, '_product_image', true);
    $html = '<p class="description">';
    $html .= 'Выберите ваше изображение';
    $html .= '</p>';
    $html .= '<input type="button" id="product_image_button" class="button" value="'. 'Выбрать изображение' .'" />';
    $html .= '<input type="button" id="remove_product_image_button" class="button" value="'. 'Удалить изображение' .'" />';
    $html .= '<input type="hidden" id="product_image" name="product_image" value="'.$image_url.'" />';
    $html .= '<img id="product_image_preview" src="'.$image_url.'" style="max-width: 200px; max-height: 200px;" />';
    echo $html;  
}

function custom_save_product_image( $post_id, $post ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return;

    if ( !current_user_can('edit_post', $post->ID ))
        return;

    if ( !isset( $_POST['product_image'] ))
        return;

    $image_data = $_POST['product_image'];
    update_post_meta( $post_id, '_product_image', $image_data);
}
add_action('save_post', 'custom_save_product_image', 10, 2);

function product_image_enqueue() {
    global $typenow;
    if ($typenow == 'product') {
        wp_enqueue_media();
        
        wp_register_script( 'dummy-handle', '', [], '', true );
        
        $js_code = '
        jQuery(document).ready(function($){

            var custom_uploader;
        
            $("#product_image_button").click(function(e) {
        
                e.preventDefault();
        
                if (custom_uploader) {
                    custom_uploader.open();
                    return;
                }
        
                custom_uploader = wp.media.frames.file_frame = wp.media({
                    title: "Выбрать изображение",
                    button: { text: "Использовать это изображение" },
                    multiple: false
                });
        
                //Select
                custom_uploader.on("select", function() {
                    attachment = custom_uploader.state().get("selection").first().toJSON();
                    console.log(attachment.url);
                    $("#product_image").val(attachment.url);
                    $("#product_image_preview").attr("src", attachment.url);
                });
                
                //Open modal window
                custom_uploader.open();
            });
        
           //Remove
           $("#remove_product_image_button").click(function(e) {
                e.preventDefault();
                $("#product_image").val("");
                $("#product_image_preview").attr("src", "");
           });
        
        });';
        
        wp_add_inline_script('dummy-handle', $js_code);

        wp_enqueue_script('dummy-handle');
    }
}

add_action('admin_enqueue_scripts', 'product_image_enqueue');

function add_product_columns($columns) {
    unset($columns['thumb']);
    $new_columns = array();
    foreach($columns as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        if('cb' === $column_name) {
            $new_columns['product_custom_image'] = '';
        }
    }
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'add_product_columns', 10, 1);

function add_product_column_content($column, $post_id) {
    switch($column) {
        case 'product_custom_image' :
            $product_image_url = get_post_meta($post_id, '_product_image', true);
            if (!empty($product_image_url)) {
                echo '<img src="' . esc_url($product_image_url) . '" style="width:50px;height:auto;" />';
            }
            break;
    }
}
add_action('manage_product_posts_custom_column', 'add_product_column_content', 10, 2);
//Custom image for product end

//Custom select start
function custom_product_select_meta_box() {  
    add_meta_box(
        'product_select_meta_box',
        'Тип',
        'custom_product_select_meta_box_callback',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_product_select_meta_box');

function custom_product_select_meta_box_callback($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'product_select_nonce');
    $selected_option = get_post_meta($post->ID, '_product_select_option', true);
    custom_product_select_option_html($selected_option);
}

function custom_product_select_option_html($selected_option) {
    echo '<select name="product_select_option" id="product_select_option">';
    echo '<option value="rare"'.selected( $selected_option, 'rare', false ).'>Rare</option>';
    echo '<option value="frequent"'.selected( $selected_option, 'frequent', false ).'>Frequent</option>';
    echo '<option value="unusual"'.selected( $selected_option, 'unusual', false ).'>Unusual</option>';
    echo '</select>';
}

function custom_save_product_select($post_id, $post) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return;

    if ( !current_user_can('edit_post', $post->ID ))
        return;

    if ( !isset( $_POST['product_select_option'] ))
        return;

    $select_data = $_POST['product_select_option'];
    update_post_meta( $post_id, '_product_select_option', $select_data);
}
add_action('save_post', 'custom_save_product_select', 10, 2);
//Custom select end

//Custom date start
function product_date_meta_box() {  
    add_meta_box(
        'product_date_meta_box',
        'Select Date',
        'product_date_meta_box_callback',
        'product',
        'side',
        'core'
    );
}
add_action('add_meta_boxes', 'product_date_meta_box');

function product_date_meta_box_callback($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'product_date_nonce');
    $date = get_post_meta($post->ID, '_product_date', true);
    echo '<input type="text" id="product_date" name="product_date" value="'.$date.'" class="widefat" />';
}

function save_product_date_meta_box( $post_id, $post ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;

    if ( !current_user_can('edit_post', $post->ID ))
        return $post_id;

    if ( isset( $_POST['product_date'] )) {
        $date_data = sanitize_text_field( $_POST['product_date'] );
        update_post_meta( $post_id, '_product_date', $date_data);
    }
}

add_action('save_post', 'save_product_date_meta_box', 10, 2);

function enqueue_datepicker() {
    global $typenow;
    if ($typenow == 'product') {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
        wp_enqueue_style('jquery-ui');

        wp_register_script( 'product-date-script', '', [], '', true );
        $js_code = '
        jQuery(document).ready(function($){
            $( "#product_date" ).datepicker({
                dateFormat : "dd-mm-yy"
            });
        });';
        
        wp_add_inline_script('product-date-script', $js_code);
        wp_enqueue_script('product-date-script');
    }
}

add_action('admin_enqueue_scripts', 'enqueue_datepicker');
//Custom date end

//Clear button start
function custom_clear_fields_meta_box() {  
    add_meta_box(
        'clear_fields_meta_box',
        'Управление полями',
        'custom_clear_fields_meta_box_callback',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_clear_fields_meta_box');

function custom_clear_fields_meta_box_callback($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'clear_fields_nonce');
    $html = '<p class="description">';
    $html .= 'Очистка всех кастомных полей';
    $html .= '</p>';
    $html .= '<input type="button" id="clear_custom_fields_button" class="button" value="Очистить поля" />';
    $html .= '<input type="button" id="save_post_button" class="button" value="Сохранить" style="margin-top:10px; display: block;" />';
    echo $html;  
}

add_action('wp_ajax_clear_custom_fields', 'clear_custom_fields');

function clear_custom_fields() {
    $post_id = intval($_POST['post_id']);

    if (
        ! isset($_POST['clear_custom_fields_nonce_field'])
        || ! wp_verify_nonce($_POST['clear_custom_fields_nonce_field'], 'clear_custom_fields_action')
    ) {
       die('Несанкционированный доступ!');
    }

    delete_post_meta($post_id, '_product_image');
    delete_post_meta($post_id, '_product_dropdown');
    delete_post_meta($post_id, '_product_date');

    echo json_encode([
        'post_id' => $post_id,
        'message' => 'Post updated'
    ]);

    wp_die();
}

function enqueue_clear_fields_script() {
    global $typenow;

    if ($typenow == 'product') {
        wp_register_script('clear_fields_js', '', [], '', true);
        
        $nonce = wp_create_nonce('clear_custom_fields_action');
        
        $js_code = "
            jQuery(document).ready(function($) {
                $('#clear_custom_fields_button').on('click', function(e) {
                    e.preventDefault();

                    var postId = $('#post_ID').val();

                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'clear_custom_fields',
                            post_id: postId,
                            clear_custom_fields_nonce_field: '$nonce' 
                        },
                        success: function(response) {
                            var data = JSON.parse(response);
                            
                            if(data.message === 'Post updated') {
                                location.reload();
                            }
                        },
                        error: function(error) {
                            console.log('Ошибка: ' + error);
                        }
                    });
                });
            });
        ";
        
        wp_add_inline_script('clear_fields_js', $js_code);
        wp_enqueue_script('clear_fields_js');
    }
}

add_action('admin_enqueue_scripts', 'enqueue_clear_fields_script');
//Clear button end

//Custom update button start
function admin_footer() {
    $screen = get_current_screen();

    if ( 'post' === $screen->base && 'product' === $screen->post_type ) {
        echo <<<HTML
        <script>
            jQuery(document).ready(function($) {
                $('#save_post_button').on('click', function(e) {
                    e.preventDefault();
                    $('#post').submit();
                });
            });
        </script>
        HTML;
    }
}

add_action('admin_footer', 'admin_footer');
//Custom update button end

//Custom add product page start
class Custom_Add_Product_Page {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_product_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
    }
    
    public static function enqueue_admin_scripts() {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    }

    public static function add_product_menu() {
        add_submenu_page(
            'edit.php?post_type=product',
            'CREATE PRODUCT',
            'CREATE PRODUCT',
            'manage_options',
            'custom-add-product',
            array(__CLASS__, 'display_add_product_page')
        );
    }

    public static function display_add_product_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('У вас не хватает прав для доступа на эту страницу.'));
        }

        if (isset($_POST['add_product_nonce']) && wp_verify_nonce($_POST['add_product_nonce'], 'add_product_action')) {
            $name = sanitize_text_field($_POST['product_name']);
            $image_url = esc_url_raw($_POST['product_image']);
            $select_option = sanitize_text_field($_POST['product_select_option']);
            $date_option = sanitize_text_field($_POST['product_date']);
            $price = floatval($_POST['product_price']);
            
            $product_id = wp_insert_post(array(
                'post_title' => $name,
                'post_status' => 'publish',
                'post_type' => "product",
            ));

            if (class_exists('WC_Product_Simple')) {
                $product = new WC_Product_Simple($product_id);
                $product->set_regular_price($price);
                $product->set_price($price);
                $product->save();
            }

            if ($image_url) {
                $upload_dir = wp_upload_dir();
                $image_data = file_get_contents($image_url);
                $filename = basename($image_url);
                if(wp_mkdir_p($upload_dir['path']))
                    $file = $upload_dir['path'] . '/' . $filename;
                else
                    $file = $upload_dir['basedir'] . '/' . $filename;
                file_put_contents($file, $image_data);
            
                $wp_filetype = wp_check_filetype($filename, null );
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $file, $product_id );
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
                $res2= set_post_thumbnail( $product_id, $attach_id );
            }

            update_post_meta($product_id, '_product_select_option', $select_option);
            update_post_meta($product_id, '_product_date', $date_option);
            
            echo '<div class="notice notice-success is-dismissible"><p>Товар успешно добавлен!</p></div>';
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
              <?php wp_nonce_field('add_product_action', 'add_product_nonce'); ?>
              
              <p>
                  <label for="product_name">Название товара</label>
                  <input id="product_name" class="widefat" type="text" name="product_name">
              </p>

              <p>
                  <input type="text" name="product_image" id="product_image" readonly="readonly" class="regular-text">
                  <input type='button' class="button-primary" value="Выбрать изображение" id="product_image_button">
              </p>

              <p>
                  <label for="product_select_option">Тип товара</label>
                  <select id="product_select_option" name="product_select_option">
                      <option value="rare">Rare</option>
                      <option value="frequent">Frequent</option>
                      <option value="unusual">Unusual</option>
                  </select>
              </p>

              <p>
                  <label for="product_date">Дата</label>
                  <input type="text" name="product_date" id="product_date" class="regular-text">
              </p>

              <p>
                  <label for="product_price">Цена товара</label>
                  <input id="product_price" class="widefat" type="text" name="product_price">
              </p>

              <p>
                  <input type="submit" value="Добавить товар" class="button-primary">
              </p>
            </form>

            <script>
                jQuery(document).ready(function($){
                    $('#product_image_button').click(function(e) {
                        e.preventDefault();
                        var image = wp.media({ 
                            title: 'Upload Image',
                            multiple: false
                        }).open()
                        .on('select', function(e){
                            var uploaded_image = image.state().get('selection').first();
                            var image_url = uploaded_image.toJSON().url;
                            $('#product_image').val(image_url);
                        });
                    });
                    
                    $('#product_date').datepicker({
                        dateFormat: 'dd-mm-yy'
                    });
                });
            </script>

        </div>
        <?php
    }
}

Custom_Add_Product_Page::init();
//Custom add product page end