<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header>
    <div class="container header-layout">
        
        <div class="navbar-capsula">
            
            <a href="<?php echo home_url(); ?>" class="site-title-text">
                Corporación Wanderers
            </a>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="https://corporacionwanderers.cl/">Inicio</a></li>
                    <li><a href="https://corporacionwanderers.cl/quienes-somos/">Institución</a></li>
                    <li><a href="https://corporacionwanderers.cl/noticias/">Noticias</a></li>
                    <li><a href="https://corporacionwanderers.cl/ramas-deportivas/">Ramas deportivas</a></li>
                    <li><a href="<?php echo home_url(); ?>" class="active">Museo</a></li>
                </ul>
            </nav>

        </div>
        
        <div class="logo-container">
            <a href="<?php echo home_url(); ?>">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/svg/logo_corpo.svg" alt="Wanderers" class="escudo-big">
            </a>
        </div>

    </div>
</header>

<section style="background: #333; color: white; padding: 2rem 0; text-align: center;">
    <h2>Preservando la Historia del Decano</h2>
    <p>Sube tus obras y sé parte de la leyenda.</p>
</section>