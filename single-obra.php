<?php
/**
 * Template Name: Vista Individual de Obra
 * Muestra el detalle de una obra específica y obras relacionadas.
 */

get_header(); ?>

<main class="container" style="padding: 2rem 0;">

    <?php
    // LOOP PRINCIPAL (La obra actual)
    while ( have_posts() ) : the_post(); 
        // Guardamos el ID para excluirlo después en "Otras obras"
        $current_obra_id = get_the_ID();

        // --- LÓGICA DE VOTOS (Backend -> Frontend) ---
        // 1. Recuperamos los contadores actuales de la base de datos
        $likes = (int) get_post_meta( $current_obra_id, '_museo_likes', true );
        $dislikes = (int) get_post_meta( $current_obra_id, '_museo_dislikes', true );

        // 2. Verificamos si el usuario ya tiene la cookie de esta obra
        $ya_voto = isset($_COOKIE['voto_obra_' . $current_obra_id]);

        // 3. Definimos estilos para el estado "Deshabilitado"
        $disabled_attr = $ya_voto ? 'disabled' : '';
        $cursor_style  = $ya_voto ? 'cursor: not-allowed; opacity: 0.6;' : 'cursor: pointer;';
        $bg_like       = $ya_voto ? '#ccc' : '#28a745';
        $bg_dislike    = $ya_voto ? '#ccc' : '#dc3545';
    ?>

        <article style="background: white; padding: 2rem; border-radius: 8px; border: 1px solid #ddd;">
            
            <header style="margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php the_title(); ?></h1>
                
                <div style="color: #666; font-size: 0.9rem; display: flex; gap: 1rem;">
                    <span><strong>Autor:</strong> <?php the_author(); ?></span>
                    <span><strong>Fecha:</strong> <?php echo get_the_date(); ?></span>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div style="margin-bottom: 2rem; text-align: center; background: #f9f9f9; padding: 1rem;">
                    <?php the_post_thumbnail('large', ['style' => 'max-height: 500px; width: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);']); ?>
                </div>
            <?php endif; ?>

            <div class="contenido-obra" style="font-size: 1.1rem; line-height: 1.8; max-width: 800px; margin: 0 auto;">
                <?php the_content(); ?>
            </div>

            <div style="margin-top: 3rem; text-align: center; padding: 1.5rem; background: #f0f0f0; border-radius: 50px; display: inline-block;">
                
                <button class="btn-voto" 
                        data-id="<?php echo $current_obra_id; ?>" 
                        data-tipo="like" 
                        <?php echo $disabled_attr; ?>
                        style="background: <?php echo $bg_like; ?>; color: white; border: none; padding: 10px 20px; border-radius: 20px; margin-right: 10px; <?php echo $cursor_style; ?>">
                    👍 Me gusta <span><?php echo $likes; ?></span>
                </button>
                
                <button class="btn-voto" 
                        data-id="<?php echo $current_obra_id; ?>" 
                        data-tipo="dislike" 
                        <?php echo $disabled_attr; ?>
                        style="background: <?php echo $bg_dislike; ?>; color: white; border: none; padding: 10px 20px; border-radius: 20px; <?php echo $cursor_style; ?>">
                    👎 No me gusta <span><?php echo $dislikes; ?></span>
                </button>

            </div>

        </article>

    <?php endwhile; // Fin del Loop Principal ?>

    <hr style="margin: 3rem 0; border: 0; border-top: 2px solid #eee;">

    <section>
        <h3 style="margin-bottom: 1.5rem; font-size: 1.8rem;">Otras Obras del Museo</h3>
        
        <div class="grid">
            <?php
            // Query Secundaria: Trae 3 obras, pero EXCLUYE la que estamos viendo
            $related_args = array(
                'post_type'      => 'obra',
                'posts_per_page' => 3,
                'post__not_in'   => array( $current_obra_id ), 
                'orderby'        => 'rand' 
            );
            $related_query = new WP_Query( $related_args );

            if ( $related_query->have_posts() ) :
                while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                    
                    <article class="card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div style="height: 150px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <?php the_post_thumbnail('medium', ['style' => 'object-fit: cover; width: 100%; height: 100%;']); ?>
                                </div>
                            <?php endif; ?>
                            <h4 style="font-size: 1rem;"><?php the_title(); ?></h4>
                        </a>
                    </article>

                <?php endwhile;
                wp_reset_postdata(); 
            else : ?>
                <p>No hay más obras para mostrar.</p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>