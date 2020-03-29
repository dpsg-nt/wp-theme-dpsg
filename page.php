<?php get_header();

	the_post(); ?>
			<div class="row">
				<div class="content col-xs-12 col-sm-6 col-lg-8">
					<article class="post">
						<?php dpsg_page_title(); ?>
						<div class="entry">
							<?php dpsg_content_edit(); ?>
						</div>
					</article><!-- /.post -->
				</div><!-- /.col-xs-6 -->
				<?php get_sidebar(); ?>
			</div><!-- /.row -->
<?php get_footer(); ?>