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

defined('ABSPATH') or exit;

add_action('admin_menu', function () {
    add_submenu_page('plugins.php', 'Recommendations', 'Recommendations', 'manage_options', 'custom-plugin-installer', function () {
        
        require_once(plugin_dir_path(__FILE__) . 'class-ktt-recommended-plugins-list-table.php');
        
        $listTable = new KTT_Recommended_Plugins_List_Table();
        $listTable->prepare_items();
        $listTable->process_bulk_action();

        echo '<div class="wrap"><h1>Recommended Plugins</h1>';
        echo '<form method="post">';
        $listTable->display();
        echo '</form>';
        echo '</div>';
    });
});


require_once(plugin_dir_path(__FILE__) . 'updater.php');
$updater = new KttRecommendedPluginsUpdater(__FILE__);
$updater->set_username('KeesCBakker');
$updater->set_repository('ktt-wp-recommended-plugins');
$updater->initialize();
