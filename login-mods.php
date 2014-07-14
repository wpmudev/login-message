<?php
/*
Plugin Name: Log In Message
Plugin URI:
Description: Add custom log in messages
Author: WPMU DEV
Version: 1.0.2
Network: true
WDP ID: 256
Text Domain: login_mods
*/

/*
Copyright 2007-2014 Incsub (http://incsub.com)
Author - S H Mohanjith (Incsub)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('LOGIN_MODS_VERSION', '1.0.2');

add_action('init', 'login_mods_action_init');
add_action('admin_init', 'login_mods_action_admin_init');
add_action('admin_enqueue_scripts', 'login_mods_action_admin_enqueue_scripts');
add_action('login_head', 'login_mods_disable_password_reset');
add_action('login_footer', 'login_mods_action_login_footer');
add_action('wpmu_options', 'login_mods_action_wpmu_options');
add_action('update_wpmu_options', 'login_mods_action_update_wpmu_options');

add_filter('login_message', 'login_mods_filter_lost_password', 10, 1);
add_filter('allow_password_reset', 'login_mods_filter_allow_password_reset', 10, 2);
add_filter('login_errors', 'login_mods_filter_login_errors', 10, 1);

function login_mods_action_init() {
    load_plugin_textdomain('login_mods', false, dirname(plugin_basename(__FILE__)).'/languages');

    if (login_mods_get_option('login_message_version', '0.0.0') == '0.0.0') {
        login_mods_add_option('login_message_version', LOGIN_MODS_VERSION);
        login_mods_add_option('login_mods_disable_password_reset', 0);
        login_mods_add_option('login_mods_message', '');
        login_mods_add_option('login_mods_footer_message', '');
        login_mods_add_option('login_mods_password_reset_message', '');
    }
}

function login_mods_action_admin_init() {
    if (!is_multisite()) {
        add_settings_field( 'login_mods_message', __('Login message', 'login_mods' ), 'login_mods_message_output', 'general' );
        add_settings_field( 'login_mods_footer_message', __('Login footer message', 'login_mods' ), 'login_mods_footer_message_output', 'general' );
        add_settings_field( 'login_mods_disable_password_reset', __('Disable password reset?', 'login_mods' ), 'login_mods_disable_password_reset_output', 'general' );
        add_settings_field( 'login_mods_password_reset_message', __('Password reset not allowed message', 'login_mods' ), 'login_mods_password_reset_message_output', 'general' );

        if (isset($_POST['login_mods_disable_password_reset'])) {
            login_mods_update_option('login_mods_disable_password_reset', $_POST['login_mods_disable_password_reset']);
        }
        if (isset($_POST['login_mods_message'])) {
            login_mods_update_option('login_mods_message', stripslashes($_POST['login_mods_message']));
        }
        if (isset($_POST['login_mods_footer_message'])) {
            login_mods_update_option('login_mods_footer_message', stripslashes($_POST['login_mods_footer_message']));
        }
        if (isset($_POST['login_mods_password_reset_message'])) {
            login_mods_update_option('login_mods_password_reset_message', stripslashes($_POST['login_mods_password_reset_message']));
        }
    }
    wp_register_script('login_mods_admin_js', plugins_url('js/login-mods-admin.js', __FILE__), array('jquery'));
}

function login_mods_message_output() {
    echo '<textarea id="login_mods_message" name="login_mods_message" class="large-text" >'.esc_textarea(stripslashes(login_mods_get_option('login_mods_message', ''))).'</textarea>';
}

function login_mods_footer_message_output() {
    echo '<textarea id="login_mods_footer_message" name="login_mods_footer_message" class="large-text" >'.esc_textarea(stripslashes(login_mods_get_option('login_mods_footer_message', ''))).'</textarea>';
}

function login_mods_disable_password_reset_output() {
    echo '<label>';
    echo '<input type="radio" id="login_mods_disable_password_reset_yes" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="1" '.((login_mods_get_option('login_mods_disable_password_reset', 0) == 1)?'checked="checked"':'').' /> ';
    echo __( 'Yes' ).'</label>';
    echo '<label><input type="radio" id="login_mods_disable_password_reset_no" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="0" '.((login_mods_get_option('login_mods_disable_password_reset', 0) == 0)?'checked="checked"':'').' /> ';
    echo __( 'No' ).'</label>';
}

function login_mods_password_reset_message_output() {
    echo '<textarea id="login_mods_password_reset_message" name="login_mods_password_reset_message" class="large-text" >'.esc_textarea(stripslashes(login_mods_get_option('login_mods_password_reset_message', ''))).'</textarea>';
}

function login_mods_action_admin_enqueue_scripts($hook) {
    if (is_multisite() && $hook == 'settings.php') {
        wp_enqueue_script('login_mods_admin_js');
    } else if ($hook == 'options-general.php') {
        wp_enqueue_script('login_mods_admin_js');
    }
}

function login_mods_get_option($option, $default) {
    if (is_multisite()) {
        return get_site_option($option, $default);
    }
    return get_option($option, $default);
}

function login_mods_add_option($option, $value) {
    if (is_multisite()) {
        return add_site_option($option, $value);
    }
    return add_option($option, $value);
}

function login_mods_update_option($option, $value) {
    if (is_multisite()) {
        return update_site_option($option, $value);
    }
    return update_option($option, $value);
}

function login_mods_filter_lost_password($message) {
    if (login_mods_get_option('login_mods_message', '') != '') {
        $message .= '<p class="message">'.__(login_mods_get_option('login_mods_message', ''), 'login_mods').'</p>';
    }

    return $message;
}

function login_mods_filter_login_errors($message) {
    if (strcmp(strip_tags(trim($message)), __('Password reset is not allowed for this user')) == 0
        && login_mods_get_option('login_mods_disable_password_reset', 0) == 1
        && login_mods_get_option('login_mods_password_reset_message', '') != '' ) {
        $message = login_mods_get_option('login_mods_password_reset_message', '');
    }
    return $message;
}

function login_mods_filter_allow_password_reset($allow_password_reset, $user_id) {
    return (login_mods_get_option('login_mods_disable_password_reset', 0) == 0);
}

function login_mods_disable_password_reset() {
    if(login_mods_get_option('login_mods_disable_password_reset', 0))
        echo '<style type="text/css">#nav a:last-child {display:none;}</style>';
}

function login_mods_action_login_footer() {
    if (login_mods_get_option('login_mods_footer_message', '') != '') {
    ?>
    <?php _e(login_mods_get_option('login_mods_footer_message', ''), 'login_mods'); ?>
    <?php
    }
}

function login_mods_action_update_wpmu_options() {
    if (isset($_POST['login_mods_disable_password_reset'])) {
        update_site_option('login_mods_disable_password_reset', $_POST['login_mods_disable_password_reset']);
    }
    if (isset($_POST['login_mods_message'])) {
        update_site_option('login_mods_message', stripslashes($_POST['login_mods_message']));
    }
    if (isset($_POST['login_mods_footer_message'])) {
        update_site_option('login_mods_footer_message', stripslashes($_POST['login_mods_footer_message']));
    }
    if (isset($_POST['login_mods_password_reset_message'])) {
        update_site_option('login_mods_password_reset_message', stripslashes($_POST['login_mods_password_reset_message']));
    }
}

function login_mods_action_wpmu_options() {
    ?>
    <h3><?php _e( 'Log In Mod Settings', 'login_mods' ); ?></h3>
    <table id="menu" class="form-table">

        <tr valign="top">
            <th scope="row"><?php _e( 'Login message', 'login_mods' ); ?></th>
            <td>
                <textarea id="login_mods_message" name="login_mods_message" class="large-text" ><?php echo esc_textarea(stripslashes(login_mods_get_option('login_mods_message', ''))); ?></textarea>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Login footer message', 'login_mods' ); ?></th>
            <td>
                <textarea id="login_mods_footer_message" name="login_mods_footer_message" class="large-text" ><?php echo esc_textarea(stripslashes(login_mods_get_option('login_mods_footer_message', ''))); ?></textarea>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e( 'Disable password reset?', 'login_mods' ); ?></th>
            <td>
                <label><input type="radio" id="login_mods_disable_password_reset_yes" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="1"
                    <?php echo (login_mods_get_option('login_mods_disable_password_reset', 0) == 1)?'checked="checked"':''; ?> /> <?php _e( 'Yes', 'login_mods' ); ?></label>
                <label><input type="radio" id="login_mods_disable_password_reset_no" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="0"
                    <?php echo (login_mods_get_option('login_mods_disable_password_reset', 0) == 0)?'checked="checked"':''; ?> /> <?php _e( 'No', 'login_mods' ); ?></label>
            </td>
        </tr>

        <tr valign="top" class="login_mods_disabled_password_reset">
            <th scope="row"><?php _e( 'Password reset not allowed message', 'login_mods' ); ?></th>
            <td>
                <textarea id="login_mods_password_reset_message" name="login_mods_password_reset_message" class="large-text" ><?php echo esc_textarea(stripslashes(login_mods_get_option('login_mods_password_reset_message', ''))); ?></textarea>
            </td>
        </tr>
    </table>
    <?php
}

global $wpmudev_notices;
$wpmudev_notices[] = array( 'id'=> 256, 'name'=> 'Log In Message', 'screens' => array( 'settings-network' ) );
include_once(plugin_dir_path( __FILE__ ).'external/dash-notice/wpmudev-dash-notification.php');
