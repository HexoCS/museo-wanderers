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
            update_post_meta( $post_id, 'nombre_autor_externo', $autor_nombre );
            update_post_meta( $post_id, 'email_contacto', $autor_email );

            // F. Manejo del Archivo (FIX CRÍTICO DE INGENIERÍA)
            // ------------------------------------------------------------------
            // Forzamos la carga de librerías de administración aunque estemos en frontend
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            // Verificación extra de integridad del archivo recibido
            if ( empty($_FILES['imagen_obra']) || $_FILES['imagen_obra']['error'] !== UPLOAD_ERR_OK ) {
                 // Si PHP no pudo subir el archivo (ej: límite de tamaño excedido antes de procesar)
                 $mensaje_estado = "Error crítico: La imagen no pudo ser procesada por el servidor.";
                 $tipo_mensaje = "error";
            } else {
                // Intentamos procesar la subida con WordPress
                $attachment_id = media_handle_upload( 'imagen_obra', $post_id );

                if ( is_wp_error( $attachment_id ) ) {
                    // Error específico de WordPress (ej: tipo de archivo no permitido)
                    $mensaje_estado = "Error al guardar la imagen: " . $attachment_id->get_error_message();
                    $tipo_mensaje = "error";
                } else {
                    // ¡Éxito total! Asignamos la miniatura
                    set_post_thumbnail( $post_id, $attachment_id );
                    
                    $mensaje_estado = "¡Gracias! Tu obra ha sido recibida y está en revisión.";
                    $tipo_mensaje = "exito";
                }
            }
            // ------------------------------------------------------------------

        } else {
            $mensaje_estado = "Hubo un error al guardar la información en la base de datos.";
            $tipo_mensaje = "error";
        }
    }
}

get_header(); ?>

<main class="subir-obra-container">
    <div class="container">

        <?php if ( $mensaje_estado ) : ?>
            <div class="mensaje-estado mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje_estado; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-subir-obra">
            
            <?php wp_nonce_field( 'crear_obra_nueva', 'museo_upload_nonce' ); ?>
            <input type="hidden" name="museo_action" value="subir">

            <div class="form-layout">
                
                <div class="form-upload">
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="imagen_obra" id="imagenObra" accept="image/*" required class="input-file">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <div class="upload-icon">📷</div>
                            <p class="upload-text">Click para subir imagen</p>
                        </div>
                        <img id="previewImage" class="preview-image" style="display: none;">
                    </div>
                </div>
                
                <div class="form-campos">
                    
                    <div class="campo-grupo">
                        <label class="campo-label">Título Obra</label>
                        <input type="text" name="titulo_obra" class="campo-input" placeholder="Título de la obra..." required>
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-label">Historia / Descripción</label>
                        <textarea name="descripcion_obra" rows="6" class="campo-textarea" placeholder="Cuéntanos la historia..."></textarea>
                    </div>

                    <div class="campo-grupo">
                        <label class="campo-label">Autor</label>
                        <input type="text" name="autor_nombre" class="campo-input" placeholder="Tu nombre...">
                    </div>
                    
                    <div class="campo-grupo">
                        <label class="campo-label">Email</label>
                        <input type="email" name="autor_email" class="campo-input" placeholder="Tu email...">
                    </div>

                    <button type="submit" class="btn-post">POST</button>

                </div>
                
            </div>

        </form>

        <section class="otras-obras-section">
            <h2 class="otras-obras-titulo">Otras Obras</h2>
            
            <div class="obras-grid">
                <?php
                $recent_args = array(
                    'post_type'      => 'obra',
                    'posts_per_page' => 6,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                );
                $recent_query = new WP_Query( $recent_args );

                if ( $recent_query->have_posts() ) :
                    while ( $recent_query->have_posts() ) : $recent_query->the_post(); ?>
                        
                        <article class="obra-card">
                            <a href="<?php the_permalink(); ?>" class="obra-link">
                                <div class="obra-imagen">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail('medium'); ?>
                                        <span class="obra-plaquita"><?php the_title(); ?></span>
                                    <?php else : ?>
                                        <div class="sin-imagen">
                                            <span>Sin Foto</span>
                                        </div>
                                        <span class="obra-plaquita"><?php the_title(); ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>

                    <?php endwhile;
                    wp_reset_postdata(); 
                endif; ?>
            </div>
        </section>

    </div>
</main>

<script>
// Preview de imagen al seleccionar
document.getElementById('imagenObra').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadPlaceholder').style.display = 'none';
            const preview = document.getElementById('previewImage');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Click en el área para abrir selector de archivos
document.getElementById('uploadArea').addEventListener('click', function() {
    document.getElementById('imagenObra').click();
});
</script>

<?php get_footer(); ?>