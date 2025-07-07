<?php
/**
 * Template for Single Garment Page
 */

get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        ?>
        <div id="gc-single-garment" class="gc-single-garment-container">
            <h1 class="gc-garment-title"><?php the_title(); ?></h1>
            <div class="gc-garment-content">
                <?php the_content(); ?>
            </div>
            <div class="gc-garment-customizer">
                <?php echo do_shortcode('[garment_customizer]'); ?>
            </div>
        </div>
        <?php
    endwhile;
else :
    echo '<p>' . esc_html__( 'Garment not found.', 'garment-customizer' ) . '</p>';
endif;

get_footer();
?>
