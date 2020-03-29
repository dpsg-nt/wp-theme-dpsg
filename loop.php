<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
		<article <?php post_class('post'); ?>>
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<?php if(has_post_thumbnail()) :

				$img_obj = wp_get_attachment_image_src( get_post_thumbnail_id(), 'dpsg-post-single' ); ?>
				<p><a href="<?php the_permalink() ?>"><img src="<?php echo $img_obj[0]; ?>" alt="" class="featured-image" /></a></p>
			<?php endif;

			if ( $post->post_type == 'post' ) :

				$brief_description = carbon_get_the_post_meta('dpsg_post_description'); ?>
				<?php $category = get_the_category(); ?>
				<h3>Erschienen am <?php the_time('j. F Y'); ?> in <a href="<?php echo get_category_link($category[0]->term_id ) ?>"><?php echo $category[0]->cat_name; ?></a> <?php echo ( $brief_description != '' ? $brief_description : ''); ?></h3>
			<?php endif ?>

			<div class="entry">
				<?php the_excerpt(); ?>
				<a class="btn btn-default" href="<?php the_permalink(); ?>"><?php echo __('Weiterlesen', 'dpsg'); ?></a>
			</div>
		</article>
	<?php endwhile; ?>

	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link(__('&laquo; Ältere Beiträge')); ?></div>
			<div class="alignright"><?php previous_posts_link(__('Neuere Beiträge &raquo;')); ?></div>
			<div class="cl">&nbsp;</div>
		</div>
	<?php endif; ?>
	
<?php else : ?>
	<article id="post-0" class="post error404 not-found">
		<h2>Nicht gefunden</h2>
		
		<div class="entry">
			<?php  
				if ( is_category() ) { // If this is a category archive
					printf("<p>Es gibt aktuell keine Beiträge in der Kategorie %s.</p>", single_cat_title('',false));
				} else if ( is_date() ) { // If this is a date archive
					echo("<p>Es gibt keine Beträge in diesem Zeitraum</p>");
				} else if ( is_author() ) { // If this is a category archive
					$userdata = get_user_by('id', get_queried_object_id());
					printf("<p>Es gibt aktuell keine Beträge von %s.</p>", $userdata->display_name);
				} else if ( is_search() ) {
					echo("<p>Keine Beiträge gefunden. Noch einmal suchen?</p>");
				} else {
					echo("<p>Keine Beiträge gefunden.</p>");
				}
			?>
			<?php get_search_form(); ?>
		</div>
	</article>
<?php endif; ?>