<?php get_header(); ?>

<main class="galeria-principal">
    <div class="container">
        
        <!-- Header de la Galería -->
        <div class="galeria-header">
            <h1 class="galeria-titulo">Galería de Obras</h1>
            <a href="<?php echo site_url('/subir-obra'); ?>" class="btn-subir">Subir una Obra +</a>
        </div>

        <!-- Barra de Búsqueda -->
        <div class="search-bar">
            <form role="search" method="get" action="<?php echo home_url( '/' ); ?>">
                <input type="text" value="<?php echo get_search_query(); ?>" name="s" placeholder="Buscar: '1970', 'Camiseta'..." class="search-input">
                <button type="submit" class="btn-buscar">🔍 Buscar</button>
            </form>
        </div>

        <!-- Grid de Obras -->
        <div class="obras-grid">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
            $busqueda = get_query_var('s');

            $args = array(
                'post_type'      => 'obra',
                'posts_per_page' => 12,
                'post_status'    => 'publish',
                'paged'          => $paged,
                's'              => $busqueda
            );
            
            $obras = new WP_Query( $args );

            if ( $obras->have_posts() ) :
                while ( $obras->have_posts() ) : $obras->the_post(); ?>
                    
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
                <div class="no-obras">
                    <p>Aún no hay obras en el museo. ¡Sé el primero en aportar!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php get_footer(); ?>