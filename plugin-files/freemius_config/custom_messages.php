<?php

function exlog_freemius_custom_connect_message_on_update(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __('Hey %1$s') . ',<br>' .
        __('Please help me improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine. Your support is appreciated :)', 'external-login'),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

function exlog_freemius_custom_connect_message(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __('Hey %1$s') . ',<br>' .
        __('I\'d appreciate you helping me improve %2$s! Opt-in to security and feature updates notifications, and non-sensitive diagnostic tracking with %5$s. Thanks :)', 'external-login'),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

exlog_freemius()->add_filter('connect_message_on_update', 'exlog_freemius_custom_connect_message_on_update', 10, 6);
exlog_freemius()->add_filter('connect_message', 'exlog_freemius_custom_connect_message', 10, 6);
