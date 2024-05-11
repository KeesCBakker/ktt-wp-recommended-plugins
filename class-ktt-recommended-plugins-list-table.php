<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require_once(ABSPATH . 'wp-admin/includes/plugin-install.php'); // for plugins_api
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'); // for Plugin_Upgrader


class KTT_Recommended_Plugins_List_Table extends WP_List_Table
{
    private $plugin_data = [];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'plugin',
            'plural'   => 'plugins',
            'ajax'     => false
        ]);
        $this->prepare_items();
    }

    public function prepare_items()
    {
        $this->plugin_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'plugins.json'), true);
        $this->_column_headers = [$this->get_columns(), $this->get_sortable_columns(), $this->get_hidden_columns()];
        $this->items = $this->plugin_data;
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />', // Checkbox for bulk actions.
            'name' => 'Plugin Name',
            'description' => 'Description'
        ];
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="plugin[]" value="%s" />',
            $item['slug']
        );
    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name] ?? '';
    }

    public function column_name($item)
    {
        return sprintf('<strong>%s</strong>', $item['name']);
    }

    protected function get_bulk_actions()
    {
        return [
            'install_activate' => 'Install and Activate'
        ];
    }

    protected function get_table_classes()
    {
        return array('widefat', 'plugins');
    }

    public function process_bulk_action()
    {
        if ('install_activate' === $this->current_action()) {
            $plugins_to_install = $_POST['plugin'] ?? [];

            foreach ($plugins_to_install as $plugin_slug) {
                $this->install_and_activate_plugin($plugin_slug);
                ob_flush();
                flush();
            }
        }
    }

    private function install_and_activate_plugin($slug)
    {
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        $api = plugins_api('plugin_information', ['slug' => $slug, 'fields' => ['sections' => false, 'tags' => false]]);
        if (is_wp_error($api)) {
            echo "<p>Error retrieving plugin information: " . $api->get_error_message() . "</p>";
            return false;
        }

        $upgrader = new Plugin_Upgrader();
        $install_result = $upgrader->install($api->download_link);
        if (is_wp_error($install_result)) {
            echo "<p>Installation failed: " . $install_result->get_error_message() . "</p>";
            return false;
        }

        $plugin_to_activate = $upgrader->plugin_info(); // Get plugin info from the upgrader
        if (!$plugin_to_activate) {
            echo "<p>Error locating installed plugin.</p>";
            return false;
        }

        $activate_result = activate_plugin($plugin_to_activate);
        if (is_wp_error($activate_result)) {
            echo "<p>Activation failed: " . $activate_result->get_error_message() . "</p>";
            return false;
        }

        // Enable auto-updates for this plugin
        $this->ensure_auto_updates($plugin_to_activate);

        echo "<p>Successfully installed and activated {$slug}.</p>";

        return true;
    }

    private function ensure_auto_updates($plugin)
    {
        $auto_updates = get_site_option('auto_update_plugins', array()); // Get currently auto-updated plugins
        if (!in_array($plugin, $auto_updates)) {
            $auto_updates[] = $plugin;
            update_site_option('auto_update_plugins', $auto_updates); // Update the option with new list
            echo "<p>Auto-updates enabled for {$plugin}.</p>";
        }
    }
}
