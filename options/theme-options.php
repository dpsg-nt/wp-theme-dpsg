<?php

$link_targets = dpsg_link_targets();

$header_fields = array(
	Carbon_Field::factory('separator', 'header_fields', 'Header'),

	Carbon_Field::factory('text', 'dpsg_header_first_link_text', 'First link text'),
	Carbon_Field::factory('text', 'dpsg_header_first_link_url', 'First link URL'),
	Carbon_Field::factory('select', 'dpsg_header_first_link_target', 'Open link in')
		->add_options($link_targets),

	Carbon_Field::factory('text', 'dpsg_header_second_link_text', 'Second link text'),
	Carbon_Field::factory('text', 'dpsg_header_second_link_url', 'Second link URL'),
	Carbon_Field::factory('select', 'dpsg_header_second_link_target', 'Open link in')
		->add_options($link_targets),

	Carbon_Field::factory('text', 'dpsg_header_third_link_text', 'Third link text'),
	Carbon_Field::factory('text', 'dpsg_header_third_link_url', 'Third link URL'),
	Carbon_Field::factory('select', 'dpsg_header_third_link_target', 'Open link in')
		->add_options($link_targets),


	Carbon_Field::factory('text', 'dpsg_header_text', 'Text'),
	Carbon_Field::factory('text', 'dpsg_header_text_link_url', 'Text link'),
	Carbon_Field::factory('select', 'dpsg_header_text_link_target', 'Open link in')
		->add_options($link_targets),
);

$footer_fields = array(
	Carbon_Field::factory('separator', 'footer_fields', 'Footer'),
	Carbon_Field::factory('rich_text', 'dpsg_copyright_text', 'Copyright text')
		->help_text('Use {year} for the current year.'),
);

$script_fields = array(
	Carbon_Field::factory('separator', 'script_fields', 'Scripts'),
	Carbon_Field::factory('header_scripts', 'header_script', 'Header script'),
	Carbon_Field::factory('footer_scripts', 'footer_script', 'Footer script'),
);

$carbon_fields = array_merge($header_fields, $footer_fields, $script_fields);

Carbon_Container::factory('theme_options', 'Theme Options')
	->add_fields($carbon_fields);

$social_networks = dpsg_get_social_networks();
$social_network_fields = array();
foreach($social_networks as $network => $address) {
	array_push($social_network_fields, Carbon_Field::factory('text', $network)
		->set_default_value($address));
}
array_unshift($social_network_fields, Carbon_Field::factory('separator', 'social_networks_fields', 'Social networks'));

Carbon_Container::factory('theme_options', 'Social Networks')
	->set_page_parent('Theme Options')
	->add_fields($social_network_fields);

if ( carbon_twitter_widget_registered() ) {
	Carbon_Container::factory('theme_options', 'Twitter Settings')
		->set_page_parent('Theme Options')
		->add_fields(array(
			Carbon_Field::factory('html', 'twitter_settings_html')
				->set_html('
					<div style="position: relative; margin-left: -230px; background: #eee; border: 1px solid #ccc; padding: 10px;">
						<p><strong>Twitter API requires a Twitter application for communication with 3rd party sites. Here are the steps for creating and setting up a Twitter application:</strong></p>
						<ol>
							<li>Go to <a href="https://dev.twitter.com/apps/new" target="_blank">https://dev.twitter.com/apps/new</a> and log in, if necessary</li>
							<li>Supply the necessary required fields, accept the Terms of Service, and solve the CAPTCHA. Callback URL field may be left empty</li>
							<li>Submit the form</li>
							<li>On the next screen scroll down to <strong>Your access token</strong> section and click the <strong>Create my access token</strong> button</li>
							<li>Copy the following fields: Access token, Access token secret, Consumer key, Consumer secret to the below fields</li>
						</ol>
					</div>
				'),
			Carbon_Field::factory('text', 'twitter_oauth_access_token')
				->set_default_value(''),
			Carbon_Field::factory('text', 'twitter_oauth_access_token_secret')
				->set_default_value(''),
			Carbon_Field::factory('text', 'twitter_consumer_key')
				->set_default_value(''),
			Carbon_Field::factory('text', 'twitter_consumer_secret')
				->set_default_value(''),
		));
}