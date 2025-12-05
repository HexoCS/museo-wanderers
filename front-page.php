<?php get_header(); ?>

<main class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0;">
        <h1>Galería de Obras</h1>
        <a href="<?php echo site_url('/subir-obra'); ?>" class="btn">Subir una Obra +</a>
    </div>


    <div class="search-bar" style="margin-bottom: 2rem; background: #eee; padding: 1.5rem; border-radius: 8px;">
        <form role="search" method="get" action="<?php echo home_url( '/' ); ?>" style="display: flex; gap: 1rem;">
            <input type="text" value="<?php echo get_search_query(); ?>" name="s" placeholder="Buscar: '1970', 'Camiseta'..." style="flex-grow: 1; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn">🔍 Buscar</button>
        </form>
    </div>



    <div class="grid">
        <?php
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $busqueda = get_query_var('s'); // Captura lo que escribieron en el input

        $args = array(
            'post_type'      => 'obra',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'paged'          => $paged,
            's'              => $busqueda // WordPress maneja la búsqueda nativamente aquí
        );
        
        // *Truco de Ingeniería*: Si hay búsqueda, WP busca en Título y Contenido.
        // Para que busque en TAXONOMÍAS, necesitamos el hook que te daré en el paso extra.
        
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
                   <p style="font-size: 0.9rem; color: #666;">
                        Autor: 
                        <?php 
                        // 1. Intentar obtener el nombre externo
                        $autor_externo = get_post_meta( get_the_ID(), 'nombre_autor_externo', true );
                        
                        if ( ! empty( $autor_externo ) ) {
                            echo esc_html( $autor_externo ); // Mostrar nombre del formulario
                        } else {
                            the_author(); // Fallback: Mostrar dev_museo
                        }
                        ?>
                    </p>
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