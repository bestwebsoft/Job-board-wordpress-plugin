<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Jbbrd_Settings_Tabs' ) ) {
    class Jbbrd_Settings_Tabs extends Bws_Settings_Tabs {
        public $cstmsrch_options;

        /**
         * Constructor.
         *
         * @access public
         *
         * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
         *
         * @param string $plugin_basename
         */
        public function __construct( $plugin_basename ) {
            global $jbbrd_options, $jbbrd_plugin_info, $jbbrd_BWS_demo_data;

            $tabs = array(
                'settings'      => array( 'label' => __( 'Settings', 'job-board' ) ),
                'misc'          => array( 'label' => __( 'Misc', 'job-board' ) ),
                'custom_code'   => array( 'label' => __( 'Custom Code', 'job-board' ) ),
            );

            parent::__construct(array(
                'plugin_basename'       => $plugin_basename,
                'plugins_info'          => $jbbrd_plugin_info,
                'prefix'                => 'jbbrd',
                'default_options'       => jbbrd_get_options_default(),
                'options'               => $jbbrd_options,
                'is_network_options'    => is_network_admin(),
                'tabs'                  => $tabs,
                'demo_data'			    => $jbbrd_BWS_demo_data,
                'wp_slug'               => 'job-board'
            ));
            $this->all_plugins = get_plugins();
            $this->cstmsrch_options = get_option( 'cstmsrch_options' );
            add_action( get_parent_class( $this ) . '_additional_import_export_options', array( $this, 'additional_import_export_options' ) );
            add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
            add_filter( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
        }

        /**
         * Function for display job-board settings page in the admin area.
         * @access public
         * @param  void
         * @return array
         */
        public function save_options() {
            $message = $notice = $error = '';

            if ( ! ( array_key_exists( 'sender/sender.php', $this->all_plugins ) || array_key_exists('sender-pro/sender-pro.php', $this->all_plugins ) ) ) {
                $jbbrd_sender_not_found = sprintf( __( '%sSender Plugin%s is not found.%s', 'job-board' ), ( '<a target="_blank" href="' . esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/sender/' ) . '">' ), '</a>', '<br />');
                $jbbrd_sender_not_found .= sprintf( __( 'If you want to give "send CV possibility" to Job candidates, you need to install and activate Sender plugin.%s', 'job-board' ), '</br>' );
                $jbbrd_sender_not_found .= __( 'You can download Sender Plugin from', 'job-board' ) . ' <a href="' . esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/sender/' ) . '" title="' . __('Developers website', 'job-board' ) . '"target="_blank">' . __( 'website of plugin Authors', 'job-board' ) . ' </a>';
                $jbbrd_sender_not_found .= __( 'or', 'job-board' ) . ' <a href="' . esc_url( 'https://wordpress.org/plugins/sender/' ) . '" title="Wordpress" target="_blank">' . __( 'Wordpress.', 'job-board' ) . '</a>';
            } else {
                if ( ! ( is_plugin_active( 'sender/sender.php' ) || is_plugin_active( 'sender-pro/sender-pro.php' ) ) ) {
                    $jbbrd_sender_not_active = sprintf( __( '%sSender Plugin%s is not active.%sIf you want to give "send CV possibility" to Job candidates, you need to %sactivate Sender plugin.%s', 'job-board' ), ( '<a target="_blank" href="' . esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/sender/' ) . '">' ), '</a>', '<br />', ( '<a href="' . esc_url('plugins.php') . '">' ), '</a>' );
                }
                if ( is_plugin_active(  'sender/sender.php' ) && isset( $this->all_plugins['sender/sender.php']['Version'] ) && $this->all_plugins['sender/sender.php']['Version'] < '0.5' ) {
                    $jbbrd_sender_not_found = __( 'Sender Plugin has old version.', 'job-board' ) . '</br>' . __( 'You need to update this plugin for sendmail function correct work.', 'job-board' );
                }
            }

            $this->options['post_per_page'] = absint( $_POST['jbbrd_post_per_page'] );
            $this->options['show_organization_name'] = ( isset( $_POST['jbbrd_show_orgname'] ) ) ? 1 : 0;
            $this->options['show_salary_info'] = ( isset( $_POST['jbbrd_show_salary'] ) ) ? 1 : 0;
            $this->options['money_choose'] = in_array( $_POST['jbbrd_money_choose'], $this->default_options['money'] , true ) ? $_POST['jbbrd_money_choose'] : $this->default_options['money_choose'];
            $this->options['time_period_choose'] = in_array( $_POST['jbbrd_time_period_choose'], $this->default_options['time_period'] , true ) ? $_POST['jbbrd_time_period_choose'] : $this->default_options['time_period_choose'];
            $this->options['logo_position'] = in_array( $_POST['jbbrd_logo_position'], array( 'left', 'right' ) , true ) ? $_POST['jbbrd_logo_position'] : $this->default_options['logo_position'];
            $this->options['frontend_form'] = isset( $_POST['jbbrd_frontend_form'] ) ? 1 : 0;
            $this->options['location_select'] = isset( $_POST['jbbrd_location_select'] ) ? 1 : 0;
            $this->options['vacancy_reply_text'] = sanitize_text_field( $_POST['jbbrd_vacancy_reply_text'] );
            $this->options['archieving_period'] = absint( $_POST['jbbrd_archieving_period'] );
            $this->options['gdpr'] = isset( $_POST['jbbrd_gdpr'] ) ? 1 : 0;
            $this->options['gdpr_cb_name'] = isset( $_POST['jbbrd_gdpr_cb_name'] ) ? sanitize_text_field( $_POST['jbbrd_gdpr_cb_name'] ) : __( 'I consent to having this site collect my personal data.', 'job-board' );
            $this->options['gdpr_text'] = isset( $_POST['jbbrd_gdpr_text'] ) ?sanitize_text_field( $_POST['jbbrd_gdpr_text'] ) : '';
            $this->options['gdpr_link'] = isset( $_POST['jbbrd_gdpr_link'] ) ? sanitize_text_field( $_POST['jbbrd_gdpr_link'] ) : '';

            if ( ! empty( $this->cstmsrch_options['output_order'] ) ) {
                if ( isset( $this->cstmsrch_options ) ) {
                    $is_enabled = isset( $_POST['jbbrd_add_to_search'] ) ? 1 : 0;

                    foreach ( $this->cstmsrch_options['output_order'] as $key => $search_item ) {
                        if ( isset( $search_item['name'] ) && 'vacancy' == $search_item['name'] ) {
                            if ( $search_item['enabled'] != $is_enabled ) {
                                $this->cstmsrch_options['output_order'][$key]['enabled'] = $is_enabled;
                                $vacancy_exist = true;
                            }
                        }
                    }
                    if ( ! isset( $vacancy_exist ) ) {
                        $this->cstmsrch_options['output_order'][] = array( 'name' => 'vacancy', 'type' => 'post_type', 'enabled' =>  $is_enabled );
                    }
                    update_option('cstmsrch_options', $this->cstmsrch_options);
                }
            }

            if ( '' == $error ) {
                /* Update options in the database. */
                update_option( 'jbbrd_options', $this->options );
                $message = __( 'Settings saved.', 'job-board' );
            } else {
                $error .= __( 'Settings are not saved.', 'job-board' );
            }

            /* Warning if not found sender plugin. */
            if ( ! empty( $jbbrd_sender_not_found ) ) {
                $notice .= $jbbrd_sender_not_found . '<br />';
            }
            if ( ! empty( $jbbrd_sender_not_active ) ) {
                $notice .= $jbbrd_sender_not_active . '<br />';
            }

            return compact( 'message', 'notice', 'error' );
        }

        public function tab_settings() { ?>
            <h3 class="bws_tab_label"><?php _e( 'Job Board Settings', 'job-board' ); ?></h3>
            <?php $this->help_phrase(); ?>
            <hr>
            <table class="form-table jbbrd_settings_form">
                <tr valign="top">
                    <th><?php _e( 'Number of Jobs', 'job-board' ); ?></th>
                    <td><input class="small-text" type="number" min="0" max="99" step="1" name="jbbrd_post_per_page" value="<?php if ( isset( $this->options['post_per_page'] ) ) echo stripslashes( $this->options['post_per_page'] ); else echo stripslashes( $this->default_options['post_per_page'] ); ?>"/> <?php _e( 'per page', 'job-board' ); ?><br />
                        <span class="bws_info"><?php _e( 'Set to “0” to display all jobs.', 'job-board' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><?php _e( 'Display', 'job-board' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="jbbrd_show_orgname" value="1" <?php checked( $this->options['show_organization_name'], 1 ); ?> />
                                <?php _e( 'Organization', 'job-board' ); ?>
                            </label><br />
                            <span class="bws_info"><?php _e( 'Enable to display the organization name in the frontend.' , 'job-board' ); ?></span><br />
                            <label>
                                <input class="bws_option_affect" data-affect-show="#salary_unit, #salary_period" type="checkbox" name="jbbrd_show_salary" value="1" <?php checked( $this->options['show_salary_info'], 1 ); ?> />
                                <?php _e( 'Salary', 'job-board' ); ?>
                            </label><br />
                            <span class="bws_info"><?php _e( 'Enable to display salary info in the frontend.' , 'job-board' ); ?></span>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top" id="salary_unit">
                    <th><?php _e( 'Currency', 'job-board' ); ?></th>
                    <td>
                        <select name="jbbrd_money_choose">
                            <?php foreach ( $this->options['money'] as $money_unit ) {
                                /* Output each select option line, check against the last $_GET to show the current option selected. */
                                echo '<option value="' . $money_unit . '"';
                                selected( $money_unit == $this->options['money_choose'] );
                                echo '">' . $money_unit . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top" id="salary_period">
                    <th><?php _e( 'Payment Period', 'job-board' ); ?></th>
                    <td>
                        <select name="jbbrd_time_period_choose">
                            <?php foreach ( $this->options['time_period'] as $key => $period_unit ) {
                                /* Output each select option line, check against the last $_GET to show the current option selected. */
                                echo '<option value="' . $period_unit . '"';
                                selected( $period_unit == $this->options['time_period_choose'] );
                                echo '">' . $period_unit . '</option>';
                            } ?>
                        </select><br />
                        <span class="bws_info"><?php _e( 'Time period for current salary.', 'job-board' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th><?php _e( 'Featured Image Alignment', 'job-board' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="jbbrd_logo_position" value="left" <?php checked( $this->options['logo_position'], 'left' ); ?> />
                                <?php _e( 'Left', 'job-board' ); ?>
                            </label><br />
                            <label>
                                <input id="jbbrd_logo_position_right" type="radio" name="jbbrd_logo_position" value="right" <?php checked( $this->options['logo_position'], 'right' ); ?> />
                                <?php _e( 'Right', 'job-board' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th><?php _e( 'Filters', 'job-board' ); ?></th>
                    <td>
                        <fieldset>
                            <label><input class="bws_option_affect" data-affect-show='#jbbrd_non-registered-users' type="checkbox" name="jbbrd_frontend_form" value="1" <?php checked( $this->options['frontend_form'], 1 ); ?> />
                                <span class="bws_info"><?php _e( 'Enable to display job filters.' , 'job-board' ); ?></span>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th><?php _e( 'Filter Location Field Type', 'job-board' ); ?></th>
                    <td>
                        <input type="checkbox" name="jbbrd_location_select" value="1" <?php checked( $this->options['location_select'], 1 ); ?> />
                        <span class="bws_info"><?php _e( 'Change location field in the frontend sorting form to select box of all locations which already add to jobs base.' , 'job-board' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th><?php _e( 'Template Text for CV Sending', 'job-board' ); ?></th>
                    <td>
                        <textarea name="jbbrd_vacancy_reply_text"><?php if ( isset( $this->options['vacancy_reply_text'] ) ) echo stripslashes( $this->options['vacancy_reply_text'] ); else echo stripslashes( $this->default_options['vacancy_reply_text'] ); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th><?php _e( 'Job Expiration Period', 'job-board' ); ?></th>
                    <td>
                        <input class="small-text" type="number" min="0" max="999" step="1" name="jbbrd_archieving_period" value="<?php if ( isset( $this->options['archieving_period'] ) ) echo stripslashes( $this->options['archieving_period'] ); else echo stripslashes( $this->default_options['archieving_period'] ); ?>"/>
                        <?php _e( 'days', 'job-board' ); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="jbbrd_gdpr"><?php _e( 'GDPR Compliance', 'job-board' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="jbbrd_gdpr" name="jbbrd_gdpr" value="1" <?php checked( '1', $this->options['gdpr'] ); ?> />
                        <span class="bws_info"><?php _e( 'Enable to display a GDPR Compliance checkbox.', 'job-board' ); ?></span>
                        <div id="jbbrd_gdpr_link_options" >
                            <label class="jbbrd_privacy_policy_text" >
                                <?php _e( 'Checkbox label', 'job-board' ); ?><br>
                                <input type="text" id="jbbrd_gdpr_cb_name" size="29" name="jbbrd_gdpr_cb_name" value="<?php echo $this->options['gdpr_cb_name']; ?>"/>
                            </label>
                            <label class="jbbrd_privacy_policy_text" >
                                <?php _e( "Link to Privacy Policy Page", 'job-board' ); ?><br>
                                <input type="url" id="jbbrd_gdpr_link" placeholder="http://" name="jbbrd_gdpr_link" value="<?php echo $this->options['gdpr_link']; ?>" />
                            </label>
                            <label class="jbbrd_privacy_policy_text" >
                                <?php _e( "Text for Privacy Policy Link", 'job-board' ); ?><br>
                                <input type="text" id="jbbrd_gdpr_text" name="jbbrd_gdpr_text" value="<?php echo $this->options['gdpr_text']; ?>" />
                            </label>
                        </div>
                    </td>
                </tr>
            </table>
    <?php }

    /**
     * Custom content for Misc tab
     * @access public
     */
    public function additional_misc_options_affected() { ?>
        <tr valign="top">
            <th><?php _e( 'Search Jobs', 'job-board' ); ?></th>
            <td>
                <?php $checked = '';

                if ( isset( $this->cstmsrch_options['output_order'] ) ) {
                    foreach ( $this->cstmsrch_options['output_order'] as $key => $search_item ) {
                        if ( isset( $search_item['name'] ) && 'vacancy' == $search_item['name'] ) {
                            if ( $search_item['enabled'] )
                                $checked = ' checked="checked"';
                        }
                    }
                }
                if ( array_key_exists( 'custom-search-plugin/custom-search-plugin.php', $this->all_plugins ) || array_key_exists( 'custom-search-pro/custom-search-pro.php', $this->all_plugins ) ) {
                    if ( is_plugin_active( 'custom-search-plugin/custom-search-plugin.php' ) || is_plugin_active( 'custom-search-pro/custom-search-pro.php' ) ) { ?>
                        <input type="checkbox" name="jbbrd_add_to_search" value="1" <?php echo $checked; ?> />
                        <span class="bws_info"> <?php _e( 'Enable to include jobs to your website search. Custom Search plugin is required.', 'job-board' ); ?></span>
                    <?php } else { ?>
                        <input disabled="disabled" type="checkbox" name="jbbrd_add_to_search" value="1" />
                        <span class="bws_info"><?php _e( 'Enable to include jobs to your website search. Custom Search plugin is required.', 'job-board' ); ?> <a href="<?php echo bloginfo( "url" ); ?>/wp-admin/plugins.php"><?php _e( 'Activate Now', 'job-board' ); ?></a></span>
                    <?php }
                } else { ?>
                    <input disabled="disabled" type="checkbox" />
                    <span class="bws_info"><?php _e( 'Enable to include jobs to your website search. Custom Search plugin is required.', 'job-board' ); ?> <a href="https://bestwebsoft.com/products/wordpress/plugins/custom-search/" target="_blank"><?php _e( 'Download Now', 'job-board' ); ?></a></span>
                <?php } ?>
            </td>
        </tr>
    <?php }

    public function display_metabox() { ?>
        <div class="postbox">
            <h3 class="hndle">
                <?php _e( 'Job Board Shortcode', 'job-board' ); ?>
            </h3>
                <div class="inside">
                    <?php _e( 'Add the "Jobs List" to your pages or posts using the following shortcode:', 'job-board' ); ?>
                    <?php bws_shortcode_output( "[jbbrd_vacancy]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( 'Add the "Registration Form" to your pages or posts using the following shortcode:', 'job-board' ); ?>
                    <?php bws_shortcode_output( "[jbbrd_registration]" ); ?>
                </div>
        </div>
    <?php }
    }
}
