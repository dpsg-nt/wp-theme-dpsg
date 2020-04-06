<!DOCTYPE html>
<html>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0" />

		<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

		<link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/images/favicon.ico" />
		<?php wp_head(); ?>
		<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style.css" type="text/css" media="screen" />
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

		<link rel="stylesheet/less" href="<?php bloginfo('template_url'); ?>/css/style.less" type="text/css" media="screen, print" />
		<script src="<?php bloginfo('template_url'); ?>/js/less-1.6.3.min.js"></script>

	</head>
	<body <?php body_class(); ?>>
		<div class="wrapper">
			<header class="site-header">
				<div class="container">
					<div class="row">
						<div class="col-xs-6 col-lg-4">
							<h1 id="logo">
								<a href="<?php echo get_home_url(null, '/'); ?>" title="<?php dpsg_sitename(); ?>"><img src="<?php echo esc_url(carbon_get_theme_option('dpsg_logo_url')); ?>" alt="Website Logo" /></a>
							</h1><!-- /#logo -->
						</div><!-- /.col-xs-12 -->

						<div class="col-xs-12 col-sm-6 col-lg-8">
							<nav class="nav-utils cf">
								<ul>
									<?php $first_link_text = carbon_get_theme_option('dpsg_header_first_link_text');
									$first_link_url = carbon_get_theme_option('dpsg_header_first_link_url');

									$second_link_text = carbon_get_theme_option('dpsg_header_second_link_text');
									$second_link_url = carbon_get_theme_option('dpsg_header_second_link_url');

									$third_link_text = carbon_get_theme_option('dpsg_header_third_link_text');
									$third_link_url = carbon_get_theme_option('dpsg_header_third_link_url');

									$has_first_link = !empty($first_link_text) && !empty($first_link_url);
									$has_second_link = !empty($second_link_text) && !empty($second_link_url);
									$has_third_link = !empty($third_link_text) && !empty($third_link_url);

									if($has_first_link) : ?>
										<li><a href="<?php echo esc_url($first_link_url); ?>" target="<?php echo carbon_get_theme_option('dpsg_header_first_link_target'); ?>"><i class="icon-dpsg"></i><?php echo $first_link_text; ?></a></li>
									<?php endif;

									if($has_second_link) : ?>
										<li><a href="<?php echo esc_url($second_link_url); ?>" target="<?php echo carbon_get_theme_option('dpsg_header_second_link_target'); ?>"><i class="icon-help"></i><?php echo $second_link_text; ?></a></li>
									<?php endif;

									if($has_third_link) : ?>
										<li><a href="<?php echo esc_url($third_link_url); ?>" target="<?php echo carbon_get_theme_option('dpsg_header_third_link_target'); ?>"><i class="icon-dpsg"></i><?php echo $third_link_text; ?></a></li>
									<?php endif; ?>
								</ul>
							</nav><!-- /.utils -->

							<nav class="nav-social cf">
								<ul>
									<?php dpsg_list_social_networks(); ?>
								</ul>
								<?php $header_text = carbon_get_theme_option('dpsg_header_text');

								if(!empty($header_text)) : ?>
									<h3><a href="<?php echo esc_url(carbon_get_theme_option('dpsg_header_text_link_url')); ?>" target="<?php echo carbon_get_theme_option('dpsg_header_text_link_target'); ?>"><?php echo $header_text; ?></a></h3>
								<?php endif; ?>
							</nav><!-- /.socials -->
						</div><!-- /.col-xs-6 -->
					</div><!-- /.row -->
				</div><!-- /.container -->
			</header><!-- /.site-header -->

			<nav class="nav">
				<div class="container">
					<a href="#" class="trigger">
						Navigation
						<small>
							<span></span>
							<span></span>
							<span></span>
						</small>
					</a>

					<div class="bar-nav">
						<?php wp_nav_menu( array(
							'theme_location'  => 'main-menu',
							'container'       => '', 
							'menu_class'      => '', 
							'fallback_cb'     => '',
						)); ?>
						
					</div><!-- /.navbar -->
				</div><!-- /.container -->
			</nav><!-- /.nav -->

			<div class="container main">