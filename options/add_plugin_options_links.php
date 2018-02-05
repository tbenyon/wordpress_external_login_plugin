<?php
function exlog_plugin_action_links( $links ) {
    error_log("got here");
    $links = array_merge( array(
        '<a href="' . esc_url( admin_url( '/options-general.php?page=' . EXLOG_PLUGIN_DATA['slug']) ) . '">Settings</a>'
    ), $links );
    return $links;
}

add_action( 'plugin_action_links_' . plugin_basename(EXLOG_PLUGIN_FILE_PATH), 'exlog_plugin_action_links' );
