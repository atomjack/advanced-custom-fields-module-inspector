<?php

/**
 * Plugin Name: Advanced Custom Fields Module Inspector
 * Plugin URI: https://www.roidna.com
 * Description: Analyze usage of Advanced Custom Fields Flexible Content Modules
 * Version: 1.0
 * Author: Chris Bellew
 */

if (!defined('ABSPATH')) {
	die();
}

if (!class_exists('ACF_Module_Inspector')) :

	class ACF_Module_Inspector {
		var $version = '1.0';

		function __construct() {
			$this->initialize();
			add_action('admin_enqueue_scripts', array($this, 'init_scripts'));
			add_action('wp_ajax_acf_module_inspector_inspect', array($this, 'inspect'));
		}

		function init_scripts() {
			wp_enqueue_script('advanced-custom-fields-module-inspector-admin', plugins_url('js/advanced-custom-fields-module-inspector-admin.js', __FILE__), array('jquery'));
			wp_localize_script(
				'advanced-custom-fields-module-inspector-admin',
				'ACFModuleInspector',
				[
					'ajaxUrl' => admin_url('admin-ajax.php'), //url for php file that process ajax request to WP
					'nonce'   => wp_create_nonce('acf-fcm-module-nonce.'), // this is a unique token to prevent form hijacking
				]
			);
			wp_enqueue_style('advanced-custom-fields-module-inspector-admin', plugins_url('css/advanced-custom-fields-module-inspector.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . '/css/advanced-custom-fields-module-inspector.css'));
		}

		function initialize() {
			$version  = $this->version;
			$basename = plugin_basename(__FILE__);
			$path     = plugin_dir_path(__FILE__);
			$url      = plugin_dir_url(__FILE__);
			$slug     = dirname($basename);

			add_action('admin_init', array($this, 'init'), 5);
		}

		function init() {
			add_action('acf/include_admin_tools', array($this, 'include_tools'));
		}

		function include_tools() {
			include_once $path . 'advanced-custom-fields-module-inspector-admin.php';
		}

		function inspect() {
			$groups = acf_get_field_groups();

			$field_groups = [];

			foreach ($groups as $group) {
				$fields = acf_get_fields($group);

				foreach ($fields as $field) {
					if ('flexible_content' == $field['type']) {
						if (!isset($field_groups[$group['key']])) {
							$field_groups[$group['key']] = [
								'title'  => $group['title'],
								'fields' => [],
							];
						}
						$field_groups[$group['key']]['fields'][$field['key']] = [
							'name'    => $field['name'],
							'label'   => $field['label'],
							'modules' => [],
						];
						foreach ($field['layouts'] as $layout_id => $layout) {
							$field_groups[$group['key']]['fields'][$field['key']]['modules'][$layout['name']] = [
								'label' => $layout['label'],
								'count' => 0,
								'urls' => []
							];
						}
					}
				}
			}

			$posts = get_posts(
				[
					'posts_per_page' => -1,
					'post_type'      => 'any',
				]
			);

			foreach ($posts as $post) {
				foreach ($field_groups as $group_key => $group) {
					foreach ($group['fields'] as $field_key => $field) {

						$modules_arr = get_field($field['name'], $post->ID);
						if (!is_array($modules_arr)) {
							continue;
						}
						foreach ($modules_arr as $module) {
							if (isset($field_groups[$group_key]['fields'][$field_key]['modules'][$module['acf_fc_layout']]) && !in_array(get_permalink($post), $field_groups[$group_key]['fields'][$field_key]['modules'][$module['acf_fc_layout']]['urls'])) {
								$field_groups[$group_key]['fields'][$field_key]['modules'][$module['acf_fc_layout']]['count']++;
								$field_groups[$group_key]['fields'][$field_key]['modules'][$module['acf_fc_layout']]['urls'][] = get_permalink($post);
							}
						}
					}
				}
			}

			usort(
				$field_groups,
				function ($a, $b) {
					if ($a['title'] == $b['title']) {
						return 0;
					}
					return ($a['title'] < $b['title']) ? -1 : 1;
				}
			);
			// ksort[$uses];
			//print_r($field_groups);
?>
			<h2>Field Groups</h2>
			<ul class="groups">
				<?php
				foreach ($field_groups as $group) {
				?>
					<li>
						<h3><i></i><?php echo $group['title']; ?></h3>
						<ul class="fields">
							<?php
							foreach ($group['fields'] as $field_id => $field) {
							?>
								<li>
									<h4><i <?php echo sizeof($field['modules']) === 1 ? ' class="minus"' : ''; ?>"></i><?php echo $field['label']; ?></h4>
									<ul class="modules<?php echo sizeof($field['modules']) === 1 ? ' show' : ''; ?>">
										<?php foreach ($field['modules'] as $module_key => $module) { ?>
											<li>
												<h5<?php echo 0 == $module['count'] ? ' class="empty">' : '><i></i>'; ?><?php printf('%s (%d)', $module['label'], $module['count']); ?> </h5>
													<ul class="urls">
														<?php foreach ($module['urls'] as $url) { ?>
															<li><?php printf('<a href="%s" target="_blank">%s</a>', $url, $url); ?></li>
														<?php } ?>
													</ul>
											</li>
										<?php } ?>
									</ul>
								</li>
							<?php } ?>
						</ul>
					</li>
				<?php } ?>
			</ul>
<?php

			die();
		}
	}

	global $ACF_Module_Inspector;
	$ACF_Module_Inspector = new ACF_Module_Inspector();

endif;

if (!function_exists('acf_module_inspector_is_acf_installed')) {
	function acf_module_inspector_is_acf_installed() {
		$file = plugin_basename(__FILE__);

		if (is_admin() && (!class_exists('acf_pro') && current_user_can('activate_plugins')) && is_plugin_active($file)) {
			add_action('admin_notices', function () {
				echo '<div class="error"><p>' . sprintf(__('Activation failed: <strong>Advanced Custom Fields Pro</strong> must be activated to use the <strong>ACF Module Inspector</strong>. %sVisit your plugins page to install and activate.', 'advanced-custom-fields-module-inspector'), '<a href="' . admin_url('plugins.php#debug-bar') . '">') . '</a></p></div>';
			});

			deactivate_plugins($file, false, is_network_admin());

			// Add to recently active plugins list.
			if (!is_network_admin()) {
				update_option('recently_activated', array($file => time()) + (array) get_option('recently_activated'));
			} else {
				update_site_option('recently_activated', array($file => time()) + (array) get_site_option('recently_activated'));
			}

			// Prevent trying again on page reload.
			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}
		}
	}
}
add_action('admin_init', 'acf_module_inspector_is_acf_installed');
