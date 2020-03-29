<?php get_header();

	the_post(); ?>
			<div class="row">
				<div class="content col-xs-12 col-sm-6 col-lg-8">
					<article class="post">
						<?php dpsg_page_title();

						if(has_post_thumbnail()) :

							$img_obj = wp_get_attachment_image_src( get_post_thumbnail_id(), 'dpsg-post-single' ); ?>
							<p><img src="<?php echo $img_obj[0]; ?>" alt="" class="featured-image" /></p>
						<?php endif;

						$brief_description = carbon_get_the_post_meta('dpsg_post_description'); ?>
						<?php $category = get_the_category(); ?>
						<h3>Erschienen am <?php the_time('j. F Y'); ?> in <a href="<?php echo get_category_link($category[0]->term_id ) ?>"><?php echo $category[0]->cat_name; ?></a> <?php echo ( $brief_description != '' ? $brief_description : ''); ?></h3>
						
						<div class="author-post" style="min-height: 112px;">
							<?php $author_id = get_the_author_meta('ID');
							$author_description = get_the_author_meta('description');

							$user_url = get_the_author_meta( 'user_url', $author_id ); ?>
							<a href="<?php echo esc_url($user_url); ?>" class="avatar">
								<?php echo get_avatar($author_id, 83); ?>
							</a>
							<h4><?php

								$networks = array(
									'googleplus2',
									'twitter',
									'facebook'
								);

								?><span class="socials">
									<?php foreach($networks as $n) :

										if($network_url = carbon_get_user_meta(intval($author_id), 'dpsg_network_' . $n)) : ?>
											<a href="<?php echo esc_url($network_url); ?>" target="_blank"><i class="icon-<?php echo $n; ?>"></i></a>
										<?php endif;

									endforeach; ?>
								</span>
								<?php the_author(); ?>
							</h4>
							<?php if(!empty($author_description)) : ?>
								<blockquote>
									<?php echo wpautop($author_description); ?>
								</blockquote>
							<?php endif; ?>
							<div class="cl">&nbsp;</div>
						</div><!-- /.author-post -->
						<div class="entry">
							<?php dpsg_content_edit(); ?>
						</div>
						<?php the_tags( '<div class="tags-post"><i class="icon icon-tags"></i> ', ' ', '</div>'); ?>
						<?php if ( function_exists('socialshareprivacy') ) { socialshareprivacy(); } ?>
						<section id="comments" class="comments">
							<?php comments_template(); ?>
						</section>
					</article><!-- /.post -->
				</div><!-- /.col-xs-6 -->
				<?php get_sidebar(); ?>
			</div><!-- /.row -->
<?php get_footer(); ?>