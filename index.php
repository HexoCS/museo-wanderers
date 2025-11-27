<?php

get_header(); ?>

<main class="container" style="padding: 2rem 0;">

    <h1>Vista General (Fallback)</h1>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post(); ?>
            
            <article style="margin-bottom: 2rem; border-bottom: 1px solid #ccc; padding-bottom: 1rem;">
                <a href="<?php the_permalink(); ?>">
                    <h2><?php the_title(); ?></h2>
                </a>
                <div><?php the_excerpt(); ?></div>
            </article>

        <?php endwhile;
    else : ?>
        <p>No hay contenido para mostrar.</p>
    <?php endif; ?>

</main>

<?php get_footer(); ?>