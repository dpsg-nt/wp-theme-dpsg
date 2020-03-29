<?php
function crb_init_theme() {
	# Enqueue jQuery
	wp_enqueue_script('jquery');

	if (is_admin()) { /* Front end scripts and styles won't be included in admin area */
		return;
	}
	# Enqueue Custom Scripts
	# @wp_enqueue_script attributes -- id, location, dependancies, version
	//wp_enqueue_script('custom-script', get_bloginfo('template_url') . '/js/custom-script.js', array('jquery'), '1.0');
	wp_enqueue_style('style-bootstrap', get_bloginfo('template_url') . '/css/bootstrap.min.css', array(), '3.1.1');
	wp_enqueue_style('style-icomoon', get_bloginfo('template_url') . '/css/icomoon.css', array(), '1.0.0');

	wp_enqueue_script('modernizr', get_bloginfo('template_url') . '/js/modernizr.js', array(), '2.7.1');
	wp_enqueue_script('bootstrap', get_bloginfo('template_url') . '/js/bootstrap.min.js', array(), '3.1.1');
	wp_enqueue_script('theme-functions', get_bloginfo('template_url') . '/js/functions.js', array('jquery'), '1.0.1');
}

define('CRB_THEME_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
add_action('init', 'crb_init_theme');
add_action('after_setup_theme', 'crb_setup_theme');

# To override theme setup process in a child theme, add your own crb_setup_theme() to your child theme's
# functions.php file.
if (!function_exists('crb_setup_theme')) {
	function crb_setup_theme() {
		include_once(CRB_THEME_DIR . 'lib/common.php');
		include_once(CRB_THEME_DIR . 'lib/carbon-fields/carbon-fields.php');

		# Theme supports
		add_theme_support('automatic-feed-links');
		add_theme_support('post-thumbnails');

		# Theme Localization
		load_theme_textdomain('dpsg', get_template_directory() . '/languages');
		
		# Manually select Post Formats to be supported - http://codex.wordpress.org/Post_Formats
		// add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

		# Register Theme Menu Locations
		add_theme_support('menus');
		register_nav_menus(array(
			'main-menu'=>__('Main Menu', 'dpsg'),
		));

		# Register CPTs
		include_once(CRB_THEME_DIR . 'options/post-types.php');
		
		# Attach custom widgets
		include_once(CRB_THEME_DIR . 'options/widgets.php');
		
		# Add Actions
		add_action('widgets_init', 'crb_widgets_init');

		add_action('carbon_register_fields', 'crb_attach_theme_options');
		#add_action('carbon_after_register_fields', 'crb_attach_theme_help');

		# Add Custom image sizes
		add_image_size('dpsg-post-single', 750, 0);
		add_image_size('dpsg-banner-image', 348, 348, true);

		# Add Filters
	}
}

# Register Sidebars
# Note: In a child theme with custom crb_setup_theme() this function is not hooked to widgets_init
function crb_widgets_init() {
	register_sidebar(array(
		'name' => 'Blog Sidebar',
		'id' => 'blog-sidebar',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));
	register_sidebar(array(
		'name' => 'Footer Sidebar',
		'id' => 'footer-sidebar',
		'before_widget' => '<div id="%1$s" class="col-xs-6 col-md-4 widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));
}

function crb_attach_theme_options() {
	# Attach fields
	include_once(CRB_THEME_DIR . 'options/theme-options.php');
	include_once(CRB_THEME_DIR . 'options/custom-fields.php');
}

function crb_attach_theme_help() {
	# Theme Help needs to be after options/theme-options.php
	include_once(CRB_THEME_DIR . 'lib/theme-help/theme-readme.php');
}

function dpsg_page_title($wrapper_start = '<h2>', $wrapper_end = '</h2>') {

	$title = '';

	if(is_home()) :
		$blog_page_id = get_option('page_for_posts');
		if($blog_page_id != 0) :
			$title = get_the_title($blog_page_id);
		else :
			$title = __('Blog', 'dpsg');
		endif;
	elseif(is_archive()) :
		if (is_category()) :
			$title = sprintf(__(' Kategorie: &#8216;%s&#8217;', 'dpsg'), single_cat_title('', false));
		elseif( is_tag() ) :
			$title = sprintf(__('Tag:  &#8216;%s&#8217;', 'dpsg'), single_tag_title('', false));
		elseif (is_day()) :
			$title = sprintf(__('Archiv von %s', 'dpsg'), get_the_time('F jS, Y'));
		elseif (is_month()) :
			$title = sprintf(__('Archiv von %s', 'dpsg'), get_the_time('F, Y'));
		elseif (is_year()) :
			$title = sprintf(__('Archiv von %s', 'dpsg'), get_the_time('Y'));
		elseif (is_author()) :
			$title = __('Author Archive', 'dpsg');
		elseif (isset($_GET['paged']) && !empty($_GET['paged'])) :
			$title = __('Blog Archives', 'dpsg');
		endif;
	elseif(is_404()) :
		$title = __('Error 404 - Not Found', 'dpsg');
	elseif(is_search()) :
		$title = sprintf(__('Suchergebnisse für: &#8216;%s&#8217;', 'dpsg'), get_search_query());
	else :
		global $post;
		$title = get_the_title($post->ID);
	endif;

	if(!empty($title)) {
		echo $wrapper_start . $title . $wrapper_end;
	}
}

function dpsg_content_edit() {
	the_content(__('<p class="serif">Read the rest of this page &raquo;</p>')); ?>
	<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
	<?php edit_post_link(__('Beitrag bearbeiten.'), '<p>', '</p>');
}

function dpsg_sitename($return = false) {

	$sitename = esc_attr(get_bloginfo('name'));

	if($return) {
		return $sitename;
	}

	echo $sitename;
}

function dpsg_get_social_networks() {
	return array(
		'rss' => get_bloginfo('rss2_url'),
		'youtube' => 'http://youtu.be',
		'facebook' => 'http://fb.me',
		'twitter' => 'http://twitter.com',
	);
}

function dpsg_list_social_networks() {
	$social_networks = dpsg_get_social_networks();
	foreach($social_networks as $network_name => $network_address) {
		$current_network_address = carbon_get_theme_option($network_name);
		if(empty($current_network_address)) {
			continue;
		} ?>
		<li><a href="<?php echo esc_url($current_network_address); ?>" target="_blank"><i class="icon-<?php echo $network_name; ?>"></i></a></li>
	<?php }
}

function dpsg_link_targets() {
	return array(
		'_blank' => 'A new window/tab',
		'_self' => 'The same window/tab'
	);
}

add_filter('excerpt_more', 'dpsg_excerpt_more');
function dpsg_excerpt_more( $more ) {
	return '...';
}

function dpsg_search_filter($query) {
	if(!is_admin()) {
	    if ($query->is_search) {
	        $query->set('post_type', 'post');
	    }
	}
	return $query;
}
add_filter('pre_get_posts', 'dpsg_search_filter');



function dpsg_widget_search($args) {
extract($args);
echo $before_widget;
echo "<div class='widget widget-search'>";
get_search_form();
echo "</div>";
echo $after_widget;
}
// register the custom widgets
$my_class = array('classname' => 'dpsg_widget_search');
wp_register_sidebar_widget('dpsg_search', __('DPSG Suche'), 'dpsg_widget_search', $my_class);

// mj: Rolle Mitarbeiter erlauben, Dateien/Bilder hochzuladen
	if ( current_user_can('contributor') && !current_user_can('upload_files') )
		add_action('admin_init', 'allow_contributor_uploads');
	  
	function allow_contributor_uploads() {
		$contributor = get_role('contributor');
		$contributor->add_cap('upload_files');
	}
