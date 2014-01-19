<?php
/**
 * @package XLT Auth
 * @version 0.1
 */
/*
  Plugin Name: Auth using Google Authenticator
  Plugin URI: http://xlt.pl/
  Description: WordPress Login Addon using Google Authenticator
  Author: XLT Lukasz Pawlik
  Version: 1.0
  Author URI: http://xlt.pl/
 */

require_once 'base32.php';
require_once 'xlttotpauthclass.php';

define('XLTTOTPAuth', '1.0');

function xlttotpauth_register_my_setting() {
    register_setting('xlttotpauth', 'xlttotpauth_enabled');
}

function xlttotpauth_filter_plugin_actions($links, $file) {
    $settings_link = '<a href="options-general.php?page=xlttotpauth">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link); // before other links
    return $links;
}

function xlttotpauth_activate() {
    update_option('xlttotpauth_enabled', 'true');
}

add_action('admin_init', 'xlttotpauth_register_my_setting');
add_action('admin_menu', 'xlttotpauth_adminmenu');
register_activation_hook(__FILE__, 'xltadincode_activate');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'xlttotpauth_filter_plugin_actions', 10, 2 );

function xlttotpauth_adminmenu() {
    add_options_page('XLT TOTP Auth', __('XLT TOTP Auth'), 'level_10', 'xlttotpauth', 'xlttotpauth_main');
}

function xlttotpauth_main() {
    $act = (isset($_GET['act']) ? $_GET['act'] : null);
    switch ($act) {
        case null:
        default:
            xlttotpauth_main_overview();
            break;
        case 'users':
            xlttotpauth_users();
            break;
        case 'generatenew':
            xlttotpauth_generatenewtoken();
            break;
    }
}

function xlttotpauth_main_overview() {
    print "<div class='wrap'>";
    screen_icon();
    print "<h2>" . __('XLT TOTP Auth Settings', 'xlttotpauth') . "</h2>";
    if (isset($_GET['settings-updated']) && @$_GET['settings-updated'] == 'true') {
        print "<h3>" . __("Changes has been saved.", "xlttotpauth") . "</h3>";
    }
    print "<form method=\"post\" action=\"options.php\"> ";
    @settings_fields('xlttotpauth');
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php print __("Token authorization enabled", "xlttotpauth"); ?></th>
            <td><input type="checkbox" name="xlttotpauth_enabled" <?php echo (get_option('xlttotpauth_enabled')) ? "checked" : ""; ?> /></td>
        </tr>  
    </table>
    <?php
    submit_button("Save changes");
    print "</form>";
	?><h2>You like this plugin? Don't want me to starve ;)? You can send me a dollar ;) or just forget it ;)</h2><br/><br/>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="FGXNWPRMXVDBA">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
	</form>	
	<?php
	print "</div>";
}

function xlttotpauth_login_form() {
    ?><p>
        <label for="user_token"><?php _e('Google Authenticator token') ?><br />
            <input type="text" name="token" id="token" class="input" value="" size="20" autocomplete="off"/></label>
    </p><?php
}

$enabled = (bool) get_option('xlttotpauth_enabled');
if ($enabled) {
    add_action('login_form', 'xlttotpauth_login_form');
    add_filter('authenticate', 'xlttotpauth_auth', 30, 2);
}

function xlttotpauth_auth($user, $login) {
    if (is_a($user, 'WP_Error')) {
        return $user;
    }

    if (is_a($user, 'WP_User')) {
        $enabled = (bool) get_option('xlttotpauth_enabled');
        if (!$enabled) {
            return $user;
        } else {
            return XltTOTPAuthClass::Authorize($user, $_POST['token']);
        }
    }
}

function xlttotpauth_users() {
    print "<div class='wrap'>";
    screen_icon('users');
    print "<h2>" . __('XLT TOTP User list', 'xlttotpauth') . "</h2>";
    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => '',
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '',
        'meta_query' => array(),
        'include' => array(),
        'exclude' => array(),
        'orderby' => 'login',
        'order' => 'ASC',
        'offset' => '',
        'search' => '',
        'number' => '',
        'count_total' => false,
        'fields' => array('ID', 'user_login', 'user_nicename'),
        'who' => ''
    );
    print var_export(get_users($args), true);

    print "</div>";
}

add_action('show_user_profile', 'xlttotpauth_profile');
add_action('edit_user_profile', 'xlttotpauth_profile');

function xlttotpauth_profile($user) {
    $enabled = get_user_meta($user->data->ID, 'xlttotpauth_enabled');
    if (isset($enabled[0])) {
        $enabled = (bool) $enabled[0];
    }
    $token = get_user_meta($user->data->ID, 'xlttotpauth_seckey');
    if (isset($token[0])) {
        $token = $token[0];
    } else {
        $token = '';
    }
    wp_enqueue_script('jquery');
    echo "<h3>" . __("XLT TOTP Auth", "xlttotpauth") . "</h3>";
    echo "<table class=\"form-table\"><tbody>";
    echo "<tr><th><label for=\"token_auth\">" . __('Enabled TOTP Auth', 'xlttotpauth') . "</label></th>";
    echo "<td><input type=\"checkbox\" name=\"token_auth\" id=\"token_auth\" value=\"true\" onchange=\"return xlttotpauthchange(this);\" " . ($enabled ? "checked" : "") . "></td>";
    echo "</tr>";
    $hide = "";
    if (!$enabled) {
        $hide = " style=\"display: none;\"";
    }
    echo "<tr{$hide} id=\"secrow\"><th><label for=\"token_auth_code\">" . __('Secret code', 'xlttotpauth') . "</label></th>";
    echo "<td><input type=\"text\" name=\"token_auth_code\" id=\"token_auth_code\" value=\"{$token}\" class=\"regular-text\" maxlength=\"10\">
<button class=\"button button-primary\" id=\"newtoken\">" . __("Generate new") . "</button><span id=\"waito\" style=\"display: none;\">" . __("Please wait...") . "</span>
</td>";
    echo "</tr>";
    if ($enabled && (strlen($token) == 10)) {
        echo "<tr><th><label for=\"manual_token_auth_code\">" . __('Secret code for Google Authenticator', 'xlttotpauth') . "</label></th>";
        echo "<td>" . Base32::encode($token) . "</td>";
        echo "</tr>";
        echo "<tr><th>" . __("QRCode", "xlttotpauth") . "</th>";
        $login = sprintf("%s %s", get_option('blogname'), $user->data->user_login);
        $secret = Base32::encode($token);
        echo "<td><img src=\"https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=otpauth://totp/{$login}?secret={$secret}&choe=UTF-8\" /></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "<script type=\"text/javascript\">";
    echo "function xlttotpauthchange(obj) {";
    echo "var checked = jQuery(obj).is(\":checked\");";
    echo "if (checked) { jQuery('#secrow').show(); } else { jQuery('#secrow').hide(); }";
    echo "}";
    echo "jQuery(function() {";
    echo "jQuery('#submit').click(function() {";
    echo "if (jQuery('#token_auth').is(\":checked\")) {";
    echo "var l = jQuery('#token_auth_code').val().length;";
    echo "if (l != 10) { alert('" . __('Secret code must be 10 chars long.', 'xlttotpauth') . "'); return false; } else { return true; } ";
    echo "} else { return true; }";
    echo "});";
    echo "jQuery('#newtoken').click(function() {";
    echo "jQuery('#waito').show();";
    echo "jQuery.post(ajaxurl, { action: 'xlttotpauth_newtoken'}, function(data) { if (data.res == 0) jQuery('#token_auth_code').val(data.code); jQuery('#waito').hide(); }, \"json\").error(function() { alert('" . __("Communication error") . "'); jQuery('#waito').hide(); });";
    echo "return false;";
    echo "});";
    echo "});";
    echo "</script>";
}

add_action('personal_options_update', 'xlttotpauth_user_update');
add_action('edit_user_profile_update', 'xlttotpauth_user_update');

function xlttotpauth_user_update($uid) {
    $checked = (isset($_POST['token_auth']));
    $token = @$_POST['token_auth_code'];
    if ($token == null) {
        $checked = false;
    }
    update_user_meta($uid, 'xlttotpauth_enabled', (bool) $checked);
    update_user_meta($uid, 'xlttotpauth_seckey', $token);
}

add_action('wp_ajax_xlttotpauth_newtoken', 'xlttotpauth_generatenewtoken');

function xlttotpauth_generatenewtoken() {
    $string = 'qwertyuiopasdfghjklzxcvbnm0987654321QWERTYUIOPASDFGHJKLZXCVBNM';
    $code = '';
    for ($i = 0; $i < 10; $i++) {
        $code .= substr(str_shuffle($string), 0, 1);
    }

    wp_send_json(array('res' => 0, 'code' => $code));
}
?>
