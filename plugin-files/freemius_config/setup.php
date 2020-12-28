<?php

if ( ! function_exists( 'exlog_freemius' ) ) {
    // Create a helper function for easy SDK access.
    function exlog_freemius() {
        global $exlog_freemius;

        if ( ! isset( $exlog_freemius ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/../freemius/start.php';

            $exlog_freemius = fs_dynamic_init( array(
                'id'                  => '7315',
                'slug'                => 'external-login',
                'premium_slug'        => 'external-login-pro',
                'type'                => 'plugin',
                'public_key'          => 'pk_8c2f00ba9fb0c1e12201131ad6289',
                'is_premium'          => true,
                'premium_suffix'      => 'Pro',
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'external-login',
                    'parent'         => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ) );
        }

        return $exlog_freemius;
    }

    // Init Freemius.
    exlog_freemius();
    // Signal that SDK was initiated.
    do_action( 'exlog_freemius_loaded' );
}
