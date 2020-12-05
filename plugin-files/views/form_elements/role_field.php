<div class="option-container">
    <div class="role">
        <input
            class="external_role"
            type="text"
            value="<?php echo $exlog_external_role[$EXLOG_JSON_KEY_EXTERNAL_VALUE]; ?>"
            name="<?php echo $exlog_external_role[$EXLOG_JSON_KEY_EXTERNAL_NAME]; ?>"
        >

        <select class="wordpress_role" name="<?php echo $exlog_external_role[$EXLOG_JSON_KEY_WORDPRESS_NAME]; ?>">
            <?php
              error_log('-------EXLOG LOGS FOR RENE role_field ---------');

              error_log('Roles from exlog_get_wp_role_types():');
              error_log(var_export(exlog_get_wp_role_types(), true));

              error_log('Roles from $EXLOG_WORDPRESS_AVAILABLE_ROLES:');
              error_log(var_export($EXLOG_WORDPRESS_AVAILABLE_ROLES, true));

              error_log('Is external role set?');
              error_log(var_export(isset($exlog_external_role), true));
//              error_log('Re-populating $EXLOG_WORDPRESS_AVAILABLE_ROLES incase this is the issue:');
//              $EXLOG_WORDPRESS_AVAILABLE_ROLES = exlog_get_wp_role_types();
//
//            error_log('Roles from $EXLOG_WORDPRESS_AVAILABLE_ROLES re populated:');
//            error_log(var_export($EXLOG_WORDPRESS_AVAILABLE_ROLES, true));


            ?>
            <?php $EXLOG_WORDPRESS_AVAILABLE_ROLES[EXLOG_ROLE_BLOCK_VALUE] = "-- BLOCK --"; //Add ability to block based on role ?>


            <?php
              error_log('Roles from $EXLOG_WORDPRESS_AVAILABLE_ROLES after adding blocked option:');
              error_log(var_export($EXLOG_WORDPRESS_AVAILABLE_ROLES, true));
            ?>


            <?php foreach ($EXLOG_WORDPRESS_AVAILABLE_ROLES as $key => $value) : ?>
                <?php
                  error_log('In loop:');
                  error_log(var_export($key, true));
                  error_log(var_export($value, true));
                ?>
                <option
                    <?php if (isset($exlog_external_role) && $exlog_external_role[$EXLOG_JSON_KEY_WORDPRESS_VALUE] == $key) :?>
                        selected="selected"
                    <?php endif; ?>
                    value="<?php echo $key; ?>"
                >
                    <?php echo $value; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php
          error_log('-------EXLOG LOGS FOR RENE END ---------');
        ?>
        <input
            class="remove_role_pairing button button-primary"
            value="Delete"
            type="button"
        />

    </div>
</div>
