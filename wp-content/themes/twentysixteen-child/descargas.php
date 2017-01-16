<?php
 /**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * Template name: descargas
 * @subpackage Twenty_SixteenChild
 * @since Twenty Sixteen 1.0 
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="gogogo classname">
        <img class="rectangle-mid" src="/wp-content/themes/twentysixteen-child/finally.png"></img>
        </div> 
        <?php
        // Start the loop.
        while ( have_posts() ) : the_post();
            get_template_part( 'template-parts/content', 'page' );
        endwhile;
        ?>

    </main>


</div>

