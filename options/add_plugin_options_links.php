<?php
function exlog_plugin_action_links( $links ) {
	$plugin_options = unserialize(EXLOG_PLUGIN_OPTIONS);
    $links = array_merge( array(
        '<a href="' . esc_url( admin_url( '/options-general.php?page=' . $plugin_options['slug']) ) . '">Settings</a>'
    ), $links );
    return $links;
}

add_action( 'plugin_action_links_' . plugin_basename(EXLOG_PLUGIN_FILE_PATH), 'exlog_plugin_action_links' );
