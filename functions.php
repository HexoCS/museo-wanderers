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