<?php
/**
 * The single post section of the header.
 *
 * Displays the singular header section of a single post/page/portfolio item.
 *
 * @package Alpha-Centauri
 */

?>

<div id="featured" class="featured-area header-single">

	<div class="slider-cell fullscreen" <?php alpha_centauri_fullscreen_image(); ?>>

		<div class="overlay fullscreen">
			<?php alpha_centauri_categories(); ?>
			
			<?php the_title( '<div class="single-header"><h1 class="single-title">', '</h1></div>' ); ?>

		</div>

	</div>

</div>