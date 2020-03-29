			</div><!-- /.main -->
			
			<div id="push"></div><!-- /#push -->
		</div><!-- /.wrapper -->

		<footer class="site-footer">
			<section class="top-footer">
				<div class="container">
					<img src="<?php bloginfo('stylesheet_directory'); ?>/images/tent.png" alt="" class="footer-image" />
				
					

					<div class="row">
						<?php dynamic_sidebar('footer-sidebar'); ?>
					</div><!-- /.row -->
				</div><!-- /.container -->
			</section><!-- /.top-footer -->

			<section class="bottom-footer">
				<div class="container">
					<?php if($copyright_text = carbon_get_theme_option('dpsg_copyright_text')) {
						echo wpautop(str_replace(array('|', '{year}'), array('<span class="sep">|</span>', date('Y')), $copyright_text));
					} ?>
                    <p>Theme mit freundlicher Unterstützung von: <a title="Deutsche Pfadfinderschaft Sankt Georg" href="http://dpsg.de/" target="_blank">DPSG – Deutsche Pfadfinderschaft Sankt Georg
             </a></p>
				</div><!-- /.container -->
			</section><!-- /.bottom-footer -->
		</footer><!-- /.site-footer -->
		<?php wp_footer(); ?>
	</body>
</html>