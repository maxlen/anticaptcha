<?php
/**
 * Created by PhpStorm.
 * User: maxlen
 * Date: 16.11.16
 * Time: 17:23
 */

namespace maxlen\anticaptcha\helpers;

use maxlen\anticaptcha\CaptchaService;

class Captcha
{
    const MATCH_RECAPTCHA = 'g-recaptcha-response';

    public static function isCaptcha($html)
    {
        $result = false;

        if (strpos($html, self::MATCH_RECAPTCHA)) {
            $result = CaptchaService::TYPE_NO_CAPTCHA_PROXYLESS;
        }

        return $result;
    }
}