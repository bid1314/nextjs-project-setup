<?php
/**
 * Template for Garment Shop Page
 */

get_header();

?>

<div id="gc-shop-page" class="gc-shop-page-container">
  <h1 class="gc-shop-title"><?php esc_html_e( 'Garment Shop', 'garment-customizer' ); ?></h1>

  <div class="gc-garments-list">
    <?php
    $args = array(
      'post_type' => 'garment',
      'posts_per_page' => 12,
      'post_status' => 'publish',
      'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
    );

    $garments_query = new WP_Query( $args );

    if ( $garments_query->have_posts() ) :
      echo '<ul class="gc-garments-grid">';
      while ( $garments_query->have_posts() ) : $garments_query->the_post();
        ?>
        <li class="gc-garment-item">
          <a href="<?php the_permalink(); ?>" class="gc-garment-link">
            <?php if ( has_post_thumbnail() ) : ?>
              <div class="gc-garment-thumbnail">
                <?php the_post_thumbnail( 'medium' ); ?>
              </div>
            <?php endif; ?>
            <h2 class="gc-garment-title"><?php the_title(); ?></h2>
          </a>
        </li>
        <?php
      endwhile;
      echo '</ul>';

      // Pagination
      the_posts_pagination( array(
        'mid_size' => 2,
        'prev_text' => __( 'Previous', 'garment-customizer' ),
        'next_text' => __( 'Next', 'garment-customizer' ),
      ) );

      wp_reset_postdata();
    else :
      echo '<p>' . esc_html__( 'No garments found.', 'garment-customizer' ) . '</p>';
    endif;
    ?>
  </div>
</div>

<?php
get_footer();
?>
