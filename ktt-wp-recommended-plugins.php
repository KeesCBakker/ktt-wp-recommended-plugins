<?php

/**
 * Plugin Name: KeesTalksTech Recommended Plugins
 * Plugin URI: https://github.com/KeesCBakker/ktt-wp-recommended-plugins
 * Description: Manages the installation and management of recommended plugins.
 * Version: 1.3.0
 * Author: Kees C. Bakker
 * Author URI: https://keestalkstech.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

defined('ABSPATH') or exit;

add_action('admin_menu', function () {
    add_submenu_page('plugins.php', 'Recommendations', 'Recommendations', 'manage_options', 'custom-plugin-installer', 'ktt_add_recommended_plugins_menu');
});

function ktt_add_recommended_plugins_menu() {
    require_once(plugin_dir_path(__FILE__) . 'class-ktt-recommended-plugins-list-table.php');

    $listTable = new KTT_Recommended_Plugins_List_Table();
    $listTable->prepare_items();
    $listTable->process_bulk_action();

    echo '<div class="wrap"><h1>Recommended Plugins</h1>';
    echo '<form method="post">';
    $listTable->display();
    echo '</form>';
    echo '</div>';

    echo '<style>
    .check-column {
        padding-left: 6px !important;
    }
    .description::before {
        display: none !important;
    }
    </style>';
}



require_once(plugin_dir_path(__FILE__) . 'updater.php');
$updater = new KttRecommendedPluginsUpdater(__FILE__);
$updater->set_username('KeesCBakker');
$updater->set_repository('ktt-wp-recommended-plugins');
$updater->initialize();
