<?php
/**
 * Template Name: Resultados de Búsqueda
 * Se activa automáticamente cuando hay una query ?s=busqueda
 */

get_header(); ?>

<main class="container" style="padding: 2rem 0;">

    <div class="search-bar" style="margin-bottom: 2rem; background: #eee; padding: 1.5rem; border-radius: 8px;">
        <form role="search" method="get" action="<?php echo home_url( '/' ); ?>" style="display: flex; gap: 1rem;">
            <input type="text" value="<?php echo get_search_query(); ?>" name="s" placeholder="Buscar otra cosa..." style="flex-grow: 1; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn">🔍 Buscar</button>
        </form>
    </div>

    <h1 style="margin-bottom: 2rem;">
        Resultados para: <span style="color: #0f4c29;">"<?php echo get_search_query(); ?>"</span>
    </h1>

    <div class="grid">
        <?php
        // NOTA DE INGENIERO:
        // En search.php NO hacemos "new WP_Query". 
        // WordPress ya hizo la consulta principal basada en la URL y nos entrega los datos listos.
        // Solo iteramos el Loop principal.

        if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>
                
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
                        $autor_externo = get_post_meta( get_the_ID(), 'nombre_autor_externo', true );
                        if ( ! empty( $autor_externo ) ) {
                            echo esc_html( $autor_externo );
                        } else {
                            the_author();
                        }
                        ?>
                    </p>
                    
                    <?php 
                    $tags = get_the_terms( get_the_ID(), 'etiqueta_obra' );
                    if ( $tags && ! is_wp_error( $tags ) ) : ?>
                        <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #0f4c29;">
                            Found tags: 
                            <?php foreach($tags as $t) echo $t->name . ' '; ?>
                        </div>
                    <?php endif; ?>

                </article>

            <?php endwhile;
            
            // Paginación numérica si hay muchos resultados
            echo '<div style="grid-column: 1/-1; margin-top: 2rem;">' . paginate_links() . '</div>';

        else : ?>
            
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #fff; border: 1px dashed #ccc;">
                <h3>No encontramos nada con "<?php echo get_search_query(); ?>"</h3>
                <p>Intenta con otra palabra clave o etiqueta.</p>
            </div>

        <?php endif; ?>
    </div>

</main>

<?php get_footer(); ?>