<?php

class Carbon_Container_NavMenu extends Carbon_Container {
	function __construct($id) {
		// Reset the registerd fields array, this is required so we can have fields with same names
		self::$registered_field_names = array();

		$id = str_replace(" ", '', ucwords(str_replace("_", ' ', $id)));

		$this->id = $id;

		$this->store = new Carbon_DataStore_NavMenu();

		self::initialize_filters();
	}

	/**
	 * $menu_id  => used to pass the correct menu_item_id to the Container object
	 * $render   => bool value, controlling if the container will render fields
	 */
	function init($menu_id = 0, $render = true) {
		$this->set_post_id($menu_id);

		$this->load();
		$this->_attach();

		if ( !empty($menu_id) && $render === true ) {
			$this->render();
		}

		return $this;
	}

	function set_post_id($menu_id) {
		$this->menu_id = $menu_id;
		$this->store->set_id($menu_id);
	}

	/**
	 * Checks whether the current request is valid
	 * @return bool
	 **/
	function is_valid_save() {
		return true;
	}

	function save($user_data = null) {
		foreach ($this->fields as $field) {
			$field->set_value_from_input();
			$field->save();
		}

		do_action('carbon_after_save_custom_fields', $this);
	}

	function to_json($load) {
		$carbon_data = parent::to_json(false);

		// Sends the menu_id to javascript
		$carbon_data = array_merge($carbon_data, array(
			'menu_id' => $this->menu_id,
		));

		return $carbon_data;
	}

	function render() {
		include dirname(__FILE__) . '/admin-templates/container-nav-menu.php';
	}

	// This needs to be fixed, so the containers for nav menus are not printed everywhere
	function is_valid_attach() {
		return true;
		$screen = get_current_screen();

		return $screen && $screen->id === 'nav-menus';
	}

	/* ==========================================================================
		# Attach Containers to menus
	========================================================================== */

	public static $instances = array();
	public static $active_containers = false;
	public static $initialized = false;

	// Initialize filters. This will be executed only once
	public static function initialize_filters() {
		if ( self::$initialized ) {
			return;
		}

		require_once dirname(__FILE__) . '/walkers/Crb_Walker_Nav_Menu_Edit_Fields.php';

		add_action( 'crb_print_carbon_container_nav_menu_fields_html', array('Carbon_Container_NavMenu', 'form'), 10, 5 );
		add_filter( 'wp_edit_nav_menu_walker', array( 'Carbon_Container_NavMenu', 'edit_walker'), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( 'Carbon_Container_NavMenu', 'update'), 10, 3 );
	}

	// Get containers only once, and store in instance memory.
	public static function get_containers() {
		if ( empty(self::$active_containers) ) {
			self::$active_containers = Carbon_Container::get_active_containers();
		}

		return self::$active_containers;
	}

	// Render custom fields inside each Nav Menu entry
	public static function form($output, $item, $depth, $args, $id) {
		$current_menu_item_id = $item->ID;

		self::set_instance_for_id($current_menu_item_id, true);
	}

	// Setup custom walker for the Nav Menu entries
	public static function edit_walker($walker, $menu_id) {
		return 'Crb_Walker_Nav_Menu_Edit_Fields';
	}

	// Trigger Save for all instances
	public static function update($menu_id, $current_menu_item_id, $args) {
		$instance = self::set_instance_for_id($current_menu_item_id, false);
		$instance->_save();

		return $instance;
	}

	// Render attribute prevents field containers showing on menu save
	public static function set_instance_for_id($current_menu_item_id, $render = true) {
		$active_containers = self::get_containers();
		$suffix = '-' . $current_menu_item_id;

		foreach ($active_containers as $container) {
			if ( $container->type != 'NavMenu' ) {
				continue;
			}

			$custom_fields = array();
			$fields = $container->get_fields();

			foreach ($fields as $field) {
				$tmp_field = clone $field;

				// Setup Public properties
				$tmp_field->current_menu_item_id = $current_menu_item_id;
				$tmp_field->initial_name = $tmp_field->get_name();

				// Setup Field ID and Name
				$tmp_field->set_id($tmp_field->get_id() . $suffix);
				$tmp_field->set_name($tmp_field->get_name() . $suffix);

				// Update Datastore instance
				$new_datastore = new Carbon_DataStore_NavMenu();
				$new_datastore->set_id($current_menu_item_id);
				$tmp_field->set_datastore($new_datastore);

				$custom_fields[] = $tmp_field;
			}

			self::$instances[$current_menu_item_id] = Carbon_Container::factory('nav_menu', $container->id . $suffix)
				->add_fields($custom_fields)
				->init($current_menu_item_id, $render);
		}

		return self::$instances[$current_menu_item_id];
	}
}