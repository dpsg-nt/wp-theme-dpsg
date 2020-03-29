<?php

function crb_load_widgets() {
	// register_widget('CrbLatestTweetsWidget');
	register_widget('DPSGBannerWidget');
	register_widget('DPSGAuthorsWidget');
}
add_action('widgets_init', 'crb_load_widgets');

class CrbLatestTweetsWidget extends Carbon_Widget {
	protected $form_options = array(
		'width' => 300
	);

	function __construct() {
		$this->setup('Latest Tweets', 'Displays a block with your latest tweets', array(
			Carbon_Field::factory('text', 'title', 'Title'),
			Carbon_Field::factory('text', 'username', 'Username'),
			Carbon_Field::factory('text', 'count', 'Number of Tweets to show')->set_default_value('5')
		));
	}

	function front_end($args, $instance) {
		if ( !carbon_twitter_is_configured() ) {
			return; //twitter settings are not configured
		}

		$tweets = TwitterHelper::get_tweets($instance['username'], $instance['count']);
		if (empty($tweets)) {
			return; //no tweets, or error while retrieving
		}

		extract($args);
		if ($instance['title']) {
			echo $before_title . $instance['title'] . $after_title;
		}
		?>
		<ul>
			<?php foreach ($tweets as $tweet): ?>
				<li><?php echo $tweet->tweet_text ?> - <span><?php echo $tweet->time_distance ?> ago</span></li>
			<?php endforeach ?>
		</ul>
		<?php
	}
}

class DPSGBannerWidget extends Carbon_Widget {

	function __construct() {
		$this->setup('DPSG - Banner widget', __('Displays a banner', 'dpsg'), array(
			Carbon_Field::factory('attachment', 'image', __('Banner (image)', 'dpsg'))
				->help_text('<br/>' . sprintf(__('Recommended image dimensions - %s pixels.', 'dpsg'), '348 x 348')),
			Carbon_Field::factory('text', 'link_url', __('Link URL', 'dpsg')),
			Carbon_Field::factory('select', 'link_target', __('Open link in', 'dpsg'))
				->add_options(dpsg_link_targets())
		), 'dpsg-banner-widget');
	}

	function front_end($args, $instance) {
		extract($args);
		extract($instance);

		if(!empty($image)) : ?>
			<a href="<?php echo esc_url($link_url); ?>" target="<?php echo $link_target; ?>">
				<?php $img_obj = wp_get_attachment_image_src( $image, 'dpsg-banner-image' ); ?>
				<img src="<?php echo $img_obj[0]; ?>" alt="" />
			</a>
		<?php endif;

	}
}

class DPSGAuthorsWidget extends Carbon_Widget {

	function __construct() {
		$this->setup('DPSG - Authors widget', __('Displays blog authors', 'dpsg'), array(
			Carbon_Field::factory('text', 'title', __('Title', 'dpsg')),
		), 'widget-authors');
	}

	function front_end($args, $instance) {
		extract($args);
		extract($instance);

		if ($title != '') {
			echo $before_title . $title . $after_title;
		}

		$authors = get_users(array(
			/*'role' => 'author',*/
			'blog_id' => '1',
			'exclude' => array(1,3), /* 1: webadm@dpsg.de 3: nils.goetting@dpsg.de */
			
		));
		
		if(!empty($authors)) : shuffle ($authors); ?>
			<ul>
				<?php foreach($authors as $a) :
					if ($dpsgTmp++ < 3) {
						$user_data = get_userdata($a->ID); ?>
						<li>
							<span class="author-avatar"><?php echo get_avatar($a->ID, 83); ?></span>
							<h4>
								<?php $networks = array(
									'googleplus2',
									'twitter',
									'facebook'
								); ?>
								<span class="socials">
									<?php foreach($networks as $n) :

										if($network_url = carbon_get_user_meta(intval($a->ID), 'dpsg_network_' . $n)) : ?>
											<a href="<?php echo esc_url($network_url); ?>" target="_blank"><i class="icon-<?php echo $n; ?>"></i></a>
										<?php endif;

									endforeach; ?>
								</span>
								<?php echo crb_shortalize($a->display_name, 15, '…'); ?>
							</h4>
							<?php if(!empty($user_data->description)) : ?>
								<p><?php echo $user_data->description; ?></p>
							<?php endif; ?>
						</li>
					<?php 
				} 
				endforeach; ?>
			</ul>
		<?php else : ?>
			<p>No authors found.</p>
		<?php endif;
		
	
	}
}