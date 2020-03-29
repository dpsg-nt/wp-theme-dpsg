<?php get_header(); ?>
			<div class="row">
				<div class="content col-xs-12 col-sm-6 col-lg-8">
					<article class="post">
						<?php dpsg_page_title(); ?>
						<p><?php printf(__('Please check the URL for proper spelling and capitalization.<br/>If you\'re having trouble locating a destination, try visiting the <a href="%1$s">home page</a>.'), get_option('home')); ?></p>
					</article><!-- /.post -->
				</div><!-- /.col-xs-6 -->
				<?php get_sidebar(); ?>
			</div><!-- /.row -->
<?php get_footer(); ?>