<?php
/**
 * Plugin Name: KeesTalksTech Recommended Plugins
 * Plugin URI: https://github.com/KeesCBakker/ktt-wp-recommended-plugins
 * Description: Manages the installation and management of recommended plugins.
 * Version: 1.2.1
 * Author: Kees C. Bakker
 * Author URI: https://keestalkstech.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class KTT_Recommended_Plugins_List_Table extends WP_List_Table {
    private $plugin_data = [];

    public function __construct() {
        parent::__construct([
            'singular' => 'plugin',
            'plural'   => 'plugins',
            'ajax'     => false
        ]);

        $this->prepare_items();
    }

    public function prepare_items() {
        $this->plugin_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'plugins.json'), true);
        $this->items = $this->plugin_data;
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    public function get_columns() {
        return [
            'name' => 'Plugin Name',
            'description' => 'Description'
        ];
    }

    public function column_default($item, $column_name) {
        return $item[$column_name] ?? '';
    }

    public function column_name($item) {
        $actions = [
            'more_info' => '<a href="' . esc_url($item['url']) . '" target="_blank">More Info</a>'
        ];

        // Add activation, deactivation, and uninstall links
        $plugin_path = $this->custom_plugin_path($item['slug']);
        if ($plugin_path && is_plugin_active($plugin_path)) {
            $actions['deactivate'] = '<a href="?page=custom-plugin-installer&deactivate=' . urlencode($plugin_path) . '">Deactivate</a>';
        } elseif ($plugin_path) {
            $actions['activate'] = '<a href="?page=custom-plugin-installer&activate=' . urlencode($plugin_path) . '">Activate</a>';
            $actions['uninstall'] = '<a href="?page=custom-plugin-installer&uninstall=' . urlencode($plugin_path) . '" style="color: red;">Uninstall</a>';
        } else {
            $actions['install'] = '<a href="?page=custom-plugin-installer&install=' . urlencode($item['slug']) . '">Install</a>';
        }

        return sprintf('%s %s', $item['name'], $this->row_actions($actions));
    }

    private function custom_plugin_path($slug) {
        foreach (get_plugins() as $path => $details) {
            if (strpos($path, $slug) !== false) return $path;
        }
        return null;
    }
}


add_action('admin_menu', function () {
    add_submenu_page('plugins.php', 'Recommendations', 'Recommendations', 'manage_options', 'custom-plugin-installer', function () {
        $listTable = new KTT_Recommended_Plugins_List_Table();
        $listTable->prepare_items();
        echo '<div class="wrap"><h1>Recommended Plugins</h1>';
        $listTable->display();
        echo '</div>';
    });
});

include_once(plugin_dir_path(__FILE__) . 'updater.php');
$updater = new KttRecommendedPluginsUpdater(__FILE__);
$updater->set_username('KeesCBakker');
$updater->set_repository('ktt-wp-recommended-plugins');
$updater->initialize();

add_action('admin_post_ktt_handle_plugin_action', function () {
    if (!current_user_can('install_plugins') || !check_admin_referer('ktt-' . ($_GET['plugin_action'] ?? ''))) {
        wp_die('Unauthorized operation');
    }
    $action = $_GET['plugin_action'] ?? '';
    $plugin = $_GET['plugin'] ?? '';
    switch ($action) {
        case 'install':
            $api = plugins_api('plugin_information', ['slug' => $plugin]);
            $upgrader = new Plugin_Upgrader();
            $upgrader->install($api->download_link);
            break;
        case 'activate':
            activate_plugin($plugin);
            break;
        case 'deactivate':
            deactivate_plugins($plugin);
            break;
        case 'uninstall':
            delete_plugins([$plugin]);
            break;
    }
    wp_redirect(admin_url('plugins.php?page=custom-plugin-installer'));
    exit;
});
