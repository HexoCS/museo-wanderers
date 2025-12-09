<?php
/**
 * Template Name: Panel de Moderación (Gestión)
 * Description: Vista privada para aprobar o rechazar obras pendientes.
 */

// 1. SEGURIDAD: Solo usuarios con permiso pueden ver esto
if ( ! current_user_can( 'edit_others_posts' ) ) {
    wp_redirect( home_url() ); // Si no es admin/editor
    exit;
}

// 2. CONTROLADOR DE ACCIONES (Procesar botones)
$msg = '';
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['moderacion_action'] ) ) {
    
    // Verificar Nonce (Seguridad anti-CSRF)
    if ( ! isset( $_POST['mod_nonce'] ) || ! wp_verify_nonce( $_POST['mod_nonce'], 'procesar_obra' ) ) {
        die( 'Error de seguridad.' );
    }

    $obra_id = intval( $_POST['obra_id'] );
    $accion  = $_POST['moderacion_action'];

    if ( $accion === 'aprobar' ) {
        
        // A. PROCESAR ETIQUETAS (TAGS)
        if ( isset( $_POST['tags_input'] ) && ! empty( $_POST['tags_input'] ) ) {
            $tags_texto = sanitize_text_field( $_POST['tags_input'] );
            // Convertimos "1970, camiseta" -> ['1970', 'camiseta']
            $tags_array = explode( ',', $tags_texto ); 
            wp_set_object_terms( $obra_id, $tags_array, 'etiqueta_obra' );
        }

        // B. PUBLICAR LA OBRA
        $update = wp_update_post( array(
            'ID'          => $obra_id,
            'post_status' => 'publish'
        ) );
        
        if ( $update ) $msg = '<div style="background:#d4edda; color:#155724; padding:1rem; margin-bottom:1rem;">¡Obra aprobada y publicada!</div>';
    
    } elseif ( $accion === 'rechazar' ) {
        // Mover a la papelera
        $trash = wp_trash_post( $obra_id );
        if ( $trash ) $msg = '<div style="background:#f8d7da; color:#721c24; padding:1rem; margin-bottom:1rem;">Obra movida a la papelera.</div>';
    }
}

get_header(); 
?>

<main class="container" style="padding: 2rem 0;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
        <h1>Panel de Moderación</h1>
        <a href="<?php echo admin_url('edit.php?post_type=obra'); ?>" class="btn" style="background:#333;">Ver Todo en wp-admin</a>
    </div>

    <?php echo $msg; ?>

    <?php
    $args = array(
        'post_type'      => 'obra',
        'post_status'    => 'pending', // <--- La clave: solo borradores pendientes
        'posts_per_page' => -1,        // Traer todas
        'orderby'        => 'date',
        'order'          => 'ASC'      // Las más antiguas primero (FIFO)
    );
    $pendientes = new WP_Query( $args );

    if ( $pendientes->have_posts() ) : 
        ?>
        <p>Hay <strong><?php echo $pendientes->found_posts; ?></strong> obras esperando revisión.</p>
        
        <div class="obras-moderacion-grid">
            <?php while ( $pendientes->have_posts() ) : $pendientes->the_post(); 
            
                $imagen_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                if ( ! $imagen_url ) {
                    $imagen_url = get_template_directory_uri() . '/assets/images/placeholder.jpg';
                }
                
                $autor = get_post_meta( get_the_ID(), 'nombre_autor_externo', true ) ?: 'No especificado';
                $email = get_post_meta( get_the_ID(), 'email_contacto', true ) ?: 'No especificado';
            ?>
                
                <article class="obra-moderacion-card">
                    
                    <!-- Imagen con marco clickeable -->
                    <a href="<?php echo esc_url( $imagen_url ); ?>" class="obra-imagen-moderacion" target="_blank">
                        <img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php the_title(); ?>">
                    </a>

                    <!-- Información de la obra y controles -->
                    <div class="obra-moderacion-info">
                        <h3 class="obra-moderacion-titulo"><?php the_title(); ?></h3>
                        <p class="obra-moderacion-descripcion"><?php echo wp_trim_words( get_the_content(), 20 ); ?></p>
                        
                        <div class="obra-moderacion-meta">
                            <p><strong>Autor:</strong> <?php echo esc_html( $autor ); ?></p>
                            <p><strong>Email:</strong> <?php echo esc_html( $email ); ?></p>
                            <p><strong>Fecha:</strong> <?php echo get_the_date(); ?></p>
                        </div>

                        <!-- Formulario de etiquetas y acciones -->
                        <div class="obra-moderacion-controles">
                        <form method="POST" class="obra-moderacion-form-tags">
                            <?php wp_nonce_field( 'procesar_obra', 'mod_nonce' ); ?>
                            <input type="hidden" name="obra_id" value="<?php the_ID(); ?>">
                            <input type="hidden" name="moderacion_action" value="aprobar">
                            
                            <label>Etiquetas (separar con comas):</label>
                            <input type="text" name="tags_input" placeholder="Ej: 1970, camiseta, final">
                            
                            <div class="obra-moderacion-acciones">
                                <!-- Aprobar -->
                                <button type="submit" class="btn-accion btn-aprobar" title="Aprobar y Publicar">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/like.svg" alt="Aprobar" class="icono-accion-svg">
                                </button>
                        </form>
                                
                                <!-- Rechazar -->
                                <form method="POST" onsubmit="return confirm('¿Estás seguro de borrar esta obra?');" style="display: inline;">
                                    <?php wp_nonce_field( 'procesar_obra', 'mod_nonce' ); ?>
                                    <input type="hidden" name="obra_id" value="<?php the_ID(); ?>">
                                    <input type="hidden" name="moderacion_action" value="rechazar">
                                    <button type="submit" class="btn-accion btn-rechazar" title="Rechazar">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/dislike.svg" alt="Rechazar" class="icono-accion-svg">
                                    </button>
                                </form>

                                <!-- Editar -->
                                <a href="<?php echo get_edit_post_link(); ?>" target="_blank" class="btn-accion btn-editar" title="Editar Datos">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/edit.svg" alt="Editar" class="icono-accion-svg">
                                </a>
                            </div>
                        </div>
                    </div>

                </article>

            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    <?php else : ?>
        
        <div style="text-align: center; padding: 4rem; background: #08311f; border: 1px dashed #ccc;">
            <h3>¡Todo al día!</h3>
            <p>No hay obras pendientes de revisión.</p>
        </div>

    <?php endif; ?>

</main>

<?php get_footer(); ?>