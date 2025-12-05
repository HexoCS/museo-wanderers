<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Seguridad

// 1. SETUP INICIAL
function museo_setup() {
    add_theme_support( 'title-tag' );       // Título en la pestaña del navegador
    add_theme_support( 'post-thumbnails' ); // Permite subir "Imagen destacada"
}
add_action( 'after_setup_theme', 'museo_setup' );

// 2. CARGAR ESTILOS
function museo_scripts() {
    
    wp_enqueue_style( 'museo-main', get_stylesheet_uri() );

    // ENCOLAR JS DE VOTOS (Solo en single de obras para no cargar basura en home)
    if ( is_singular( 'obra' ) ) {
        wp_enqueue_script( 'museo-votos', get_template_directory_uri() . '/assets/js/votos.js', array(), '1.0', true );

        // Pasar variables de PHP a JS (Localize Script)
        wp_localize_script( 'museo-votos', 'museoData', array(
            'root_url' => get_site_url(),
            'nonce'    => wp_create_nonce('wp_rest') // Buena práctica aunque sea público
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'museo_scripts' );

// 3. REGISTRAR CPT "OBRA" (Base de Datos)
function museo_registrar_cpt() {
    $args = array(
        'labels' => array(
            'name' => 'Obras', 
            'singular_name' => 'Obra',
            'add_new_item' => 'Añadir Nueva Obra'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ),
        'rewrite' => array( 'slug' => 'obra' ),
    );
    register_post_type( 'obra', $args );
}
add_action( 'init', 'museo_registrar_cpt' );




/**
 * 4. API REST PARA VOTACIONES (Likes/Dislikes)
 * Endpoint: /wp-json/museo/v1/votar
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'museo/v1', '/votar', array(
        'methods'  => 'POST',
        'callback' => 'museo_procesar_voto',
        'permission_callback' => '__return_true', // Público (cualquiera puede votar)
    ) );
} );

function museo_procesar_voto( WP_REST_Request $request ) {
    // 1. Obtener parámetros
    $obra_id = $request->get_param( 'id' );
    $tipo    = $request->get_param( 'tipo' ); // 'like' o 'dislike'

    // 2. Validaciones básicas
    if ( ! $obra_id || ! get_post( $obra_id ) ) {
        return new WP_Error( 'no_obra', 'Obra no encontrada', array( 'status' => 404 ) );
    }

    // 3. Verificar Cookie (¿Ya votó este usuario en esta obra?)
    if ( isset( $_COOKIE['voto_obra_' . $obra_id] ) ) {
        return new WP_Error( 'ya_voto', 'Ya has votado por esta obra', array( 'status' => 400 ) );
    }

    // 4. Actualizar Metadatos (Contadores)
    $meta_key = ( $tipo === 'like' ) ? '_museo_likes' : '_museo_dislikes';
    
    // Obtener valor actual, asegurarse que sea número entero
    $count = (int) get_post_meta( $obra_id, $meta_key, true );
    $count++;
    
    // Guardar nuevo valor
    update_post_meta( $obra_id, $meta_key, $count );

    // 5. Setear Cookie (Desde PHP para que el servidor sepa)
    // Expira en 30 días
    setcookie( 'voto_obra_' . $obra_id, 'true', time() + (86400 * 30), COOKIEPATH, COOKIE_DOMAIN );

    // 6. Responder JSON
    return array(
        'success' => true,
        'new_count' => $count,
        'type' => $tipo
    );
}