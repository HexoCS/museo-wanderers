<?php
/**
 * Template Name: Vista Individual de Obra
 * Muestra el detalle de una obra específica y obras relacionadas.
 */

get_header(); ?>

<main class="single-obra-container">
    <div class="container">

    <?php
    // LOOP PRINCIPAL (La obra actual)
    while ( have_posts() ) : the_post(); 
        $current_obra_id = get_the_ID();

        // --- LÓGICA DE VOTOS ---
        $likes = (int) get_post_meta( $current_obra_id, '_museo_likes', true );
        $dislikes = (int) get_post_meta( $current_obra_id, '_museo_dislikes', true );

        $ya_voto = isset($_COOKIE['voto_obra_' . $current_obra_id]);
        $disabled_attr = $ya_voto ? 'disabled' : '';
    ?>

        <article class="obra-detalle">
            
            <!-- Layout de 2 Columnas -->
            <div class="obra-layout">
                
                <!-- Columna Izquierda: Imagen -->
                <div class="obra-imagen-grande">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail('large'); ?>
                    <?php else : ?>
                        <div class="sin-imagen-grande">Sin Imagen</div>
                    <?php endif; ?>
                </div>
                
                <!-- Columna Derecha: Información -->
                <div class="obra-info-detalle">
                    <h1 class="obra-titulo-detalle"><?php the_title(); ?></h1>
                    
                    <div class="obra-contenido">
                        <?php the_content(); ?>
                    </div>
                    
                    <div class="obra-autor">
                        <strong>Autor: </strong>
                        <p>
                            <?php 
                            $autor_externo = get_post_meta( get_the_ID(), 'nombre_autor_externo', true );
                            
                            if ( ! empty( $autor_externo ) ) {
                                echo esc_html( $autor_externo );
                            } else {
                                the_author();
                            }
                            ?>
                        </p>
                    </div>
                    
                    <!-- Botones de Votación -->
                    <div class="votos-container">
                        <button class="btn-voto btn-like" 
                                data-id="<?php echo $current_obra_id; ?>" 
                                data-tipo="like" 
                                <?php echo $disabled_attr; ?>>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/like.svg" alt="Like" class="icono-voto-svg">
                            <span class="contador-voto"><?php echo $likes; ?></span>
                        </button>
                        
                        <button class="btn-voto btn-dislike" 
                                data-id="<?php echo $current_obra_id; ?>" 
                                data-tipo="dislike" 
                                <?php echo $disabled_attr; ?>>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/dislike.svg" alt="Dislike" class="icono-voto-svg">
                            <span class="contador-voto"><?php echo $dislikes; ?></span>
                        </button>
                    </div>
                    
                    <!-- Etiquetas de la Obra -->
                    <?php 
                    $tags = get_the_terms( get_the_ID(), 'etiqueta_obra' ); 
                    
                    if ( $tags && ! is_wp_error( $tags ) ) : 
                    ?>
                        <div class="etiquetas-container">
                            <?php foreach ( $tags as $tag ) : ?>
                                <span class="etiqueta-badge"><?php echo esc_html( $tag->name ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>

        </article>

    <?php endwhile; ?>

    <!-- Sección: Otras Obras -->
    <section class="otras-obras-section">
        <h2 class="otras-obras-titulo">Otras Obras</h2>
        
        <div class="obras-grid">
            <?php
            $related_args = array(
                'post_type'      => 'obra',
                'posts_per_page' => 6,
                'post__not_in'   => array( $current_obra_id ), 
                'orderby'        => 'rand' 
            );
            $related_query = new WP_Query( $related_args );

            if ( $related_query->have_posts() ) :
                while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                    
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
            else : ?>
                <p class="no-obras">No hay más obras para mostrar.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sección: Comentarios -->
    <?php if ( comments_open() || get_comments_number() ) : ?>
        <section class="comentarios-section">
            <?php comments_template(); ?>
        </section>
    <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>