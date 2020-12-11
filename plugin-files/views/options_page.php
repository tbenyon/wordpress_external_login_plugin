<ul
  class="exlog_options_page"
  data-exlog-wp-base="<?php echo get_site_url(); ?>"
>
  <div class="options_section_outer_container title-section">
    <div class="options_section_container">
      <div class="options_section vert_middle">
        <img src="<?php echo EXLOG_PATH_ASSETS . '/logoClear.svg' ?>">
        <h2><?php echo Exlog_built_plugin_data::Instance()->get_plugin_data()['name'] ?> Options</h2>
      </div>
    </div>
    <div class="options_section_container">
      <div class="options_section">
        <?php
          if ( exlog_freemius()->is_plan__premium_only(EXLOG_PRO_PLAN_NAME) ) {
              include EXLOG_PATH_PLUGIN_VIEWS . "/partials/banner_pro_plan.php";
          } else {
              include EXLOG_PATH_PLUGIN_VIEWS . "/partials/support_free_plan.php";
          }
        ?>
      </div>
    </div>
  </div>

    <form class="options_section_outer_container" method="post" action="options.php">
        <?php
          settings_fields( Exlog_built_plugin_data::Instance()->get_plugin_data()['slug'] . '-option-group' );
          do_settings_fields( Exlog_built_plugin_data::Instance()->get_plugin_data()['slug'] . '-option-group', '' );
        ?>

        <?php foreach (Exlog_built_plugin_data::Instance()->get_option_fields() as $form_section) : ?>
            <div class="options_section_container">
              <div class="options_section <?php echo $form_section['section_slug']; ?>">
                <h3><?php echo $form_section['section_name'] ?></h3>
                <p><?php echo $form_section['section_description'] ?></p>
                <hr>

                <?php Exlog_view_building::render_field_views($form_section['section_fields']); ?>

                <?php submit_button(); ?>
              </div>
            </div>
        <?php endforeach; ?>
    </form>

    <?php include EXLOG_PATH_PLUGIN_VIEWS . '/modal.php'; ?>

</ul>
