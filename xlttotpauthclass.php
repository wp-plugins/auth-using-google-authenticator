<?php

class XltTOTPAuthClass {

    public static function Authorize(WP_User $user, $token) {
        $uid = $user->data->ID;
        $key = get_user_meta($uid, 'xlttotpauth_seckey');
        $enabled = get_user_meta($user->data->ID, 'xlttotpauth_enabled');
        $enabled = (bool)$enabled[0];
        if ($enabled) {
            if (isset($key[0])) {
                $genTokens = self::GetTokenByTimeRange($key[0]);
                if (in_array(trim($token), $genTokens)) {
                    $lastUsedToken = get_user_meta($uid, 'xlttotpauth_lastseckey');
                    if (isset($lastUsedToken[0]) && $lastUsedToken[0] == trim($token)) {
                        $wpe = new WP_Error();
                        $wpe->add(-1, 'You can\'t use last token more than once. Wait for new token and try again.');
                        return $wpe;
                    } else {
                        update_user_meta($uid, 'xlttotpauth_lastseckey', trim($token));
                        return $user;
                    }
                } else {
                    $wpe = new WP_Error();
                    $wpe->add(-1, 'Login failed.');
                    return $wpe;
                }
            } else {
                $wpe = new WP_Error();
                $wpe->add(-1, 'Login failed.');
                return $wpe;
            }
        } else {
            return $user;
        }
    }

    public static function GetTokenByCounter($key, $counter) {
        $key = pack("A*", $key);
        $cc = array(0, 0, 0, 0, 0, 0, 0, 0);
        for ($i = 7; $i >= 0; $i--) {
            $cc[$i] = pack("C*", $counter);
            $counter = $counter >> 8;
        }
        $binc = implode($cc);
        $binc = str_pad($binc, 8, chr(0), STR_PAD_RIGHT);
        $hex = hash_hmac('sha1', $binc, $key);
        $hmac_result = array();
        foreach (str_split($hex, 2) as $vv) {
            $hmac_result[] = hexdec($vv);
        }

        $offset = $hmac_result[19] & 0xf;

        $v = (
                (($hmac_result[$offset + 0] & 0x7f) << 24 ) |
                (($hmac_result[$offset + 1] & 0xff) << 16 ) |
                (($hmac_result[$offset + 2] & 0xff) << 8 ) |
                ($hmac_result[$offset + 3] & 0xff)
                ) % pow(10, 6);

        return str_pad($v, 6, "0", STR_PAD_LEFT);
    }

    public static function GetTokenByTime($key) {
        $counter = floor(time() / 30);
        return self::GetTokenByCounter($key, $counter);
    }

    public static function GetTokenByTimeRange($key) {
        $counter = floor(time() / 30);
        $pool = array();
        $pool[] = self::GetTokenByCounter($key, $counter);
        $pool[] = self::GetTokenByCounter($key, $counter + 1);
        if ($counter > 1) {
            $pool[] = self::GetTokenByCounter($key, $counter - 1);
        }

        return $pool;
    }

}

?>
