<?php

// post meta
Carbon_Container::factory('custom_fields', __('Post settings', 'dpsg'))
	->show_on_post_type('post')
	->add_fields(array(
		Carbon_Field::factory('textarea', 'dpsg_post_description', __('Brief description', 'dpsg'))
	));


// user meta
Carbon_Container::factory('user_meta', __('Additional user settings', 'dpsg'))
	->add_fields(array(
		Carbon_Field::factory('text', 'dpsg_network_googleplus2', __('Google+ page', 'dpsg')), // blame icomoon for the 2...
		Carbon_Field::factory('text', 'dpsg_network_twitter', __('Twitter page', 'dpsg')),
		Carbon_Field::factory('text', 'dpsg_network_facebook', __('Facebook page', 'dpsg'))
	));
	