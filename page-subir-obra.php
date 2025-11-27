<?php
/**
 * Template Name: Formulario Subir Obra
 * Description: Controlador frontend para recepción de obras y archivos.
 */

// --- 1. LÓGICA DE CONTROLADOR (Procesamiento del Formulario) ---
$mensaje_estado = '';
$tipo_mensaje = ''; // 'exito' o 'error'

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['museo_action'] ) ) {
    
    // A. Verificación de Seguridad (Nonce)
    if ( ! isset( $_POST['museo_upload_nonce'] ) || ! wp_verify_nonce( $_POST['museo_upload_nonce'], 'crear_obra_nueva' ) ) {
        die( 'Error de seguridad.' );
    }

    // B. Captura de Datos
    $titulo = sanitize_text_field( $_POST['titulo_obra'] );
    $descripcion = sanitize_textarea_field( $_POST['descripcion_obra'] );
    $autor_nombre = sanitize_text_field( $_POST['autor_nombre'] );
    $autor_email = sanitize_email( $_POST['autor_email'] );

    // C. Validación Básica
    if ( empty($titulo) || empty($_FILES['imagen_obra']['name']) ) {
        $mensaje_estado = "El título y la foto son obligatorios.";
        $tipo_mensaje = "error";
    } else {
        
        // D. Inserción del Post (Estado: Pendiente)
        $nuevo_post = array(
            'post_title'    => $titulo,
            'post_content'  => $descripcion,
            'post_status'   => 'pending', // <--- CLAVE: No se publica, va a moderación
            'post_type'     => 'obra',
        );

        $post_id = wp_insert_post( $nuevo_post );

        if ( $post_id ) {
            // E. Guardar Metadatos (Autor y Email)
            // Usamos Custom Fields para guardar datos que no son título/body
            update_post_meta( $post_id, 'nombre_autor_externo', $autor_nombre );
            update_post_meta( $post_id, 'email_contacto', $autor_email );

            // F. Manejo del Archivo 
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
            }

            // Subir archivo al servidor
            $attachment_id = media_handle_upload( 'imagen_obra', $post_id );

            if ( is_wp_error( $attachment_id ) ) {
                $mensaje_estado = "Error al subir la imagen: " . $attachment_id->get_error_message();
                $tipo_mensaje = "error";
            } else {
                // Asignar como Imagen Destacada
                set_post_thumbnail( $post_id, $attachment_id );
                
                $mensaje_estado = "¡Gracias! Tu obra ha sido recibida y está en revisión";
                $tipo_mensaje = "exito";
            }
        } else {
            $mensaje_estado = "Hubo un error al guardar la información.";
            $tipo_mensaje = "error";
        }
    }
}

get_header(); ?>

<main class="container" style="padding: 2rem 0;">

    <h1 style="text-align: center; margin-bottom: 2rem;">Aportar una Obra al Museo</h1>

    <?php if ( $mensaje_estado ) : ?>
        <div style="padding: 1rem; margin-bottom: 2rem; border-radius: 5px; 
            background: <?php echo ($tipo_mensaje == 'exito') ? '#d4edda' : '#f8d7da'; ?>; 
            color: <?php echo ($tipo_mensaje == 'exito') ? '#155724' : '#721c24'; ?>;
            border: 1px solid <?php echo ($tipo_mensaje == 'exito') ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo $mensaje_estado; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border: 1px solid #ddd; border-radius: 8px;">
        
        <?php wp_nonce_field( 'crear_obra_nueva', 'museo_upload_nonce' ); ?>
        <input type="hidden" name="museo_action" value="subir">

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Fotografía de la Obra *</label>
            <input type="file" name="imagen_obra" accept="image/*" required style="width: 100%;">
            <small style="color: #666;">Formatos: JPG, PNG. Máx 5MB.</small>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Título de la Obra *</label>
            <input type="text" name="titulo_obra" placeholder="Ej: Camiseta autografiada 2001" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Historia / Descripción</label>
            <textarea name="descripcion_obra" rows="5" placeholder="Cuéntanos la historia detrás de este objeto..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;"></textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Tu Nombre (Créditos)</label>
            <input type="text" name="autor_nombre" placeholder="¿Quién sube este recuerdo?" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Email de Contacto (Privado)</label>
            <input type="email" name="autor_email" placeholder="Para contactarte si es necesario" style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <button type="submit" class="btn" style="width: 100%; font-size: 1.2rem; cursor: pointer;">Subir Obra</button>

    </form>

    <hr style="margin: 4rem 0; border-top: 2px solid #eee;">

    <section>
        <h3 style="text-align: center; margin-bottom: 2rem;">Últimas Obras Agregadas</h3>
        <div class="grid">
            <?php
            $recent_args = array(
                'post_type'      => 'obra',
                'posts_per_page' => 4,
                'post_status'    => 'publish', // Solo mostramos las aprobadas
                'orderby'        => 'date',
                'order'          => 'DESC'
            );
            $recent_query = new WP_Query( $recent_args );

            if ( $recent_query->have_posts() ) :
                while ( $recent_query->have_posts() ) : $recent_query->the_post(); ?>
                    <article class="card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div style="height: 150px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <?php the_post_thumbnail('medium', ['style' => 'object-fit: cover; width: 100%; height: 100%;']); ?>
                                </div>
                            <?php endif; ?>
                            <h4><?php the_title(); ?></h4>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>