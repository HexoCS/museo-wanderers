<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header style="background: #0f4c29; color: white; padding: 1rem 0;">
    <div class="container">
        <nav style="display: flex; justify-content: space-between; align-items: center;">
            <div class="logo" style="font-weight: bold; font-size: 1.5rem;">
                <a href="<?php echo home_url(); ?>">Museo Wanderers</a>
            </div>
            
            <ul style="display: flex; gap: 1rem;">
                <li><a href="#">Inicio</a></li>
                <li><a href="#">Institución</a></li>
                <li><a href="#">Noticias</a></li>
                <li><a href="#">Escuelas</a></li>
                <li><a href="<?php echo home_url(); ?>" style="text-decoration: underline;">MUSEO</a></li>
            </ul>
        </nav>
    </div>
</header>

<section style="background: #333; color: white; padding: 2rem 0; text-align: center;">
    <h2>Preservando la Historia del Decano</h2>
    <p>Sube tus obras y sé parte de la leyenda.</p>
</section>