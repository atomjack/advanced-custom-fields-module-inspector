<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('ACF_Module_Inspector_Admin')) :

	acf_include('includes/admin/tools/class-acf-admin-tool.php');

	class ACF_Module_Inspector_Admin extends ACF_Admin_Tool {
		function __construct() {
			parent::__construct();
		}

		function initialize() {

			// vars
			$this->name  = 'ACF_Module_Inspector';
			$this->title = __('ACF Module Inspector', 'advanced-custom-fields-module-inspector');
			$this->icon  = 'dashicons-upload';
		}

		function html() {

?>
			<p><?php _e('Click the Inspect button to analyze usage of Flexible Content Modules.', 'acf'); ?></p>
			<p class="acf-submit">
				<input type="button" id="inspect-button" class="button button-primary" value="<?php _e('Inspect', 'advanced-custom-fields-module-inspector'); ?>" />
			</p>
			<div class="advanced-custom-fields-module-inspector-results"></div>
<?php

		}
	}

	// initialize
	acf_register_admin_tool('ACF_Module_Inspector_Admin');

endif; // class_exists check

?>