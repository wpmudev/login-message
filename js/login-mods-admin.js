jQuery(document).ready(
    function () {
        jQuery('.login_mods_disable_password_reset').click(function() {
            if (jQuery('#login_mods_disable_password_reset_yes:checked').length > 0) {
                jQuery('#login_mods_password_reset_message').parent().parent().show();
            } else {
                jQuery('#login_mods_password_reset_message').parent().parent().hide();
            }
        });
        if (jQuery('#login_mods_disable_password_reset_yes:checked').length > 0) {
            jQuery('#login_mods_password_reset_message').parent().parent().show();
        } else {
            jQuery('#login_mods_password_reset_message').parent().parent().hide();
        }
    }
);
