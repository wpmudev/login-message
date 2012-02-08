<?php
/*
Plugin Name: Log In Message
Plugin URI: 
Description: Add custom log in messages
Author: S H Mohanjith (Incsub)
Version: 1.0.0
Network: true
WDP ID: 256
Text Domain: login_mods
*/

define('LOGIN_MODS_VERSION', '1.0.0');

add_action('init', 'login_mods_action_init');
add_action('admin_enqueue_scripts', 'login_mods_action_admin_enqueue_scripts');
add_action('login_footer', 'login_mods_action_login_footer');
add_action('wpmu_options', 'login_mods_action_wpmu_options');
add_action('update_wpmu_options', 'login_mods_action_update_wpmu_options');

add_filter('login_message', 'login_mods_filter_lost_password', 10, 1);
add_filter('allow_password_reset', 'login_mods_filter_allow_password_reset', 10, 2);
add_filter('login_errors', 'login_mods_filter_login_errors', 10, 1);

function login_mods_action_init() {
    load_plugin_textdomain('login_mods', false, dirname(plugin_basename(__FILE__)).'/languages');
    
    if (get_site_option('login_message_version', '0.0.0') == '0.0.0') {
        add_site_option('login_message_version', LOGIN_MODS_VERSION);
        add_site_option('login_mods_disable_password_reset', 0);
        add_site_option('login_mods_footer_message', '');
        add_site_option('login_mods_password_reset_message', '');
    }
    wp_register_script('login_mods_admin_js', plugins_url('js/login-mods-admin.js', __FILE__), array('jquery'));
}

function login_mods_action_admin_enqueue_scripts($hook) {
    if ($hook == 'settings.php') {
        wp_enqueue_script('login_mods_admin_js');
    }
}

function login_mods_filter_lost_password($message) {
    if (get_site_option('login_mods_message', '') != '') {
        $message .= '<p class="message">'.__(get_site_option('login_mods_message', ''), 'login_mods').'</p>';
    }
    
    return $message;
}

function login_mods_filter_login_errors($message) {
    if (strcmp(strip_tags(trim($message)), __('Password reset is not allowed for this user')) == 0
        && get_site_option('login_mods_disable_password_reset', 0) == 1
        && get_site_option('login_mods_password_reset_message', '') != '' ) {
        $message = get_site_option('login_mods_password_reset_message', '');
    }
    return $message;
}

function login_mods_filter_allow_password_reset($allow_password_reset, $user_id) {
    return (get_site_option('login_mods_disable_password_reset', 0) == 0);
}

function login_mods_action_login_footer() {
    if (get_site_option('login_mods_footer_message', '') != '') {
    ?>
    <?php _e(get_site_option('login_mods_footer_message', ''), 'login_mods'); ?>
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
    <h3><?php _e( 'Log In Mod Settings' ); ?></h3>
    <table id="menu" class="form-table">
        
        <tr valign="top">
            <th scope="row"><?php _e( 'Login message' ); ?></th>
            <td>
                <textarea id="login_mods_message" name="login_mods_message" class="large-text" ><?php echo esc_textarea(stripslashes(get_site_option('login_mods_message', ''))); ?></textarea>
            </td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e( 'Login footer message' ); ?></th>
            <td>
                <textarea id="login_mods_footer_message" name="login_mods_footer_message" class="large-text" ><?php echo esc_textarea(stripslashes(get_site_option('login_mods_footer_message', ''))); ?></textarea>
            </td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e( 'Disable password reset?' ); ?></th>
            <td>
                <label><input type="radio" id="login_mods_disable_password_reset_yes" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="1"
                    <?php echo (get_site_option('login_mods_disable_password_reset', 0) == 1)?'checked="checked"':''; ?> /> <?php _e( 'Yes' ); ?></label>
                <label><input type="radio" id="login_mods_disable_password_reset_no" name="login_mods_disable_password_reset" class="login_mods_disable_password_reset" value="0"
                    <?php echo (get_site_option('login_mods_disable_password_reset', 0) == 0)?'checked="checked"':''; ?> /> <?php _e( 'No' ); ?></label>
            </td>
        </tr>
        
        <tr valign="top" class="login_mods_disabled_password_reset">
            <th scope="row"><?php _e( 'Password reset not allowed message' ); ?></th>
            <td>
                <textarea id="login_mods_password_reset_message" name="login_mods_password_reset_message" class="large-text" ><?php echo esc_textarea(stripslashes(get_site_option('login_mods_password_reset_message', ''))); ?></textarea>
            </td>
        </tr>
    </table>
    <?php
}
