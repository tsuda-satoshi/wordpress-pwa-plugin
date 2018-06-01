<?php


class Plugin_name_Admin_View {
	public function __construct() {
	}
	public function render($data) {
		include_once plugin_dir_path(__FILE__). 'partials/plugin-name-admin-display.php';
	}
}
