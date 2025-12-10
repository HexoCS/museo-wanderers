<?php
/**
 * Template Name: Resultados de Búsqueda
 * Se activa automáticamente cuando hay una query ?s=busqueda
 */

get_header(); ?>

<main class="main-content">

    <div class="barra-busqueda">
        <form role="search" method="get" action="<?php echo home_url( '/' ); ?>">
            <input type="text" value="<?php echo get_search_query(); ?>" name="s" placeholder="Buscar en el museo..." class="input-busqueda">
            <button type="submit" class="btn-buscar">🔍 Buscar</button>
        </form>
    </div>

    <?php 
    $busqueda = get_search_query();
    
    // Si la búsqueda está vacía, no mostrar nada
    if ( empty( trim( $busqueda ) ) ) : ?>
        
        <div style="text-align: center; padding: 4rem 2rem; color: var(--color-blanco);">
            <h3 style="color: var(--color-secundario); margin-bottom: 1rem;">Ingresa un término de búsqueda</h3>
            <p>Escribe algo en la barra de búsqueda para encontrar obras.</p>
        </div>

    <?php else : ?>

        <h1 class="titulo-resultados">
            Resultados para: <span style="color: var(--color-secundario);">"<?php echo esc_html( $busqueda ); ?>"</span>
        </h1>

        <?php
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        
        $args = array(
            'post_type'      => 'obra',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'paged'          => $paged,
            's'              => $busqueda,
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        $obras = new WP_Query( $args );
        
        if ( $obras->have_posts() ) : ?>
            
            <div class="obras-grid">
                <?php while ( $obras->have_posts() ) : $obras->the_post(); 
                    $imagen_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                ?>
                    
                    <article class="obra-card">
                        <a href="<?php the_permalink(); ?>" class="obra-imagen">
                            <img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php the_title(); ?>">
                            <span class="obra-plaquita"><?php the_title(); ?></span>
                        </a>
                    </article>

                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <!-- Paginación -->
            <div style="margin-top: 3rem; text-align: center;">
                <?php echo paginate_links( array(
                    'total'     => $obras->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '« Anterior',
                    'next_text' => 'Siguiente »',
                ) ); ?>
            </div>

        <?php else : ?>
            
            <div style="text-align: center; padding: 4rem 2rem; color: var(--color-blanco);">
                <h3 style="color: var(--color-secundario); margin-bottom: 1rem;">No encontramos nada con "<?php echo esc_html( $busqueda ); ?>"</h3>
                <p>Intenta con otra palabra clave o etiqueta.</p>
            </div>

        <?php endif; ?>
        
    <?php endif; ?>

</main>

<?php get_footer(); ?>