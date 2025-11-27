<?php get_header(); ?>

<main class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0;">
        <h1>Galería de Obras</h1>
        <a href="<?php echo site_url('/subir-obra'); ?>" class="btn">Subir una Obra +</a>
    </div>

    <div class="grid">
        <?php
        // CONSULTA A LA BASE DE DATOS (Query Loop)
        $args = array(
            'post_type'      => 'obra', // <--- IMPORTANTE: Debe coincidir con el register_post_type en functions.php
            'posts_per_page' => 12,     // Cantidad de obras por carga
            'post_status'    => 'publish'
        );
        
        $obras = new WP_Query( $args );

        if ( $obras->have_posts() ) :
            while ( $obras->have_posts() ) : $obras->the_post(); ?>
                
                <article class="card">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div style="height: 200px; overflow: hidden; margin-bottom: 1rem;">
                                <?php the_post_thumbnail('medium', ['style' => 'object-fit: cover; width: 100%; height: 100%;']); ?>
                            </div>
                        <?php else : ?>
                            <div style="height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                <span style="color: #888;">Sin Foto</span>
                            </div>
                        <?php endif; ?>
                        
                        <h3 style="margin-bottom: 0.5rem;"><?php the_title(); ?></h3>
                    </a>
                    <p style="font-size: 0.9rem; color: #666;">Autor: <?php the_author(); ?></p>
                </article>

            <?php endwhile;
            wp_reset_postdata(); // Limpiar datos globales después del loop
        else : ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                <p>Aún no hay obras en el museo. ¡Sé el primero en aportar!</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php get_footer(); ?>