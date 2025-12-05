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
        <a href="<?php echo admin_url('edit.php?post_type=obra'); ?>" class="btn" style="background:#333;">Ver Todo en Backend</a>
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
        
        <div style="display: flex; flex-direction: column; gap: 2rem; margin-top: 1rem;">
            <?php while ( $pendientes->have_posts() ) : $pendientes->the_post(); ?>
                
                <article style="border: 2px solid #0f4c29; padding: 1.5rem; background: white; border-radius: 8px; display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
                    
                    <div style="background: #eee; display: flex; align-items: center; justify-content: center; height: 250px;">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail('medium', ['style' => 'max-height:100%; max-width:100%; object-fit:contain;']); ?>
                        <?php else : ?>
                            <span>Sin Foto</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h2 style="margin-top: 0;"><?php the_title(); ?></h2>
                        
                        <div style="background: #f9f9f9; padding: 1rem; margin: 1rem 0; font-size: 0.9rem;">
                            <p><strong>Autor (Crédito):</strong> <?php echo get_post_meta(get_the_ID(), 'nombre_autor_externo', true); ?></p>
                            <p><strong>Email:</strong> <?php echo get_post_meta(get_the_ID(), 'email_contacto', true); ?></p>
                            <p><strong>Descripción:</strong></p>
                            <div style="font-style: italic;"><?php the_content(); ?></div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            
                            <form method="POST" style="display: flex; flex-direction: column; gap: 0.5rem; background: #e8f5e9; padding: 10px; border-radius: 5px; width: 100%; max-width: 300px;">
                                <?php wp_nonce_field( 'procesar_obra', 'mod_nonce' ); ?>
                                <input type="hidden" name="obra_id" value="<?php the_ID(); ?>">
                                <input type="hidden" name="moderacion_action" value="aprobar">
                                
                                <label style="font-size: 0.8rem; font-weight: bold; color: #155724;">Etiquetas (separar con comas):</label>
                                <input type="text" name="tags_input" placeholder="Ej: 1970, camiseta, final" 
                                       style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem;">
                                
                                <button type="submit" class="btn" style="border:none; cursor:pointer; margin-top: 5px;">✅ Aprobar y Publicar</button>
                            </form>

                            <form method="POST" onsubmit="return confirm('¿Estás seguro de borrar esta obra?');" style="margin-top: auto;">
                                <?php wp_nonce_field( 'procesar_obra', 'mod_nonce' ); ?>
                                <input type="hidden" name="obra_id" value="<?php the_ID(); ?>">
                                <input type="hidden" name="moderacion_action" value="rechazar">
                                <button type="submit" class="btn" style="background:#dc3545; border:none; cursor:pointer;">🗑 Rechazar</button>
                            </form>

                            <a href="<?php echo get_edit_post_link(); ?>" target="_blank" class="btn" style="background:#007bff; text-decoration:none; margin-top: auto;">✏️ Editar Datos</a>
                        </div>
                    </div>

                </article>

            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    <?php else : ?>
        
        <div style="text-align: center; padding: 4rem; background: #fff; border: 1px dashed #ccc;">
            <h3>¡Todo al día!</h3>
            <p>No hay obras pendientes de revisión.</p>
        </div>

    <?php endif; ?>

</main>

<?php get_footer(); ?>