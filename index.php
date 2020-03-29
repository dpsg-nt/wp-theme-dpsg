<?php get_header(); ?>
			<div class="row">
				<div class="content col-xs-12 col-sm-6 col-lg-8">
					<?php dpsg_page_title('<h2 class="page-title">', '</h2>');

					get_template_part('loop'); ?>
				</div><!-- /.col-xs-6 -->
				<?php get_sidebar(); ?>
			</div><!-- /.row -->
<?php get_footer(); ?>