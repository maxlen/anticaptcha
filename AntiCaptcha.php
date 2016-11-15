<?php
/**
 * Created by PhpStorm.
 * User: w
 * Date: 15.11.16
 * Time: 15:50
 */

namespace maxlen\anticaptcha;

use maxlen\anticaptcha\api\v2\NoCaptcha;

class AntiCaptcha extends NoCaptcha
{
    CONST REG_SITEKEY = '/(?<=data-sitekey=("|\'))[^\'"]*(?=("|\'))/i';

    private $siteKey = false;

    public function setSiteKey($html)
    {
        preg_match(self::REG_SITEKEY, $html, $matches);

        if (isset($matches[0]) && $matches[0] != '') {
            $this->siteKey = $matches[0];
        }

        return $this->siteKey;
    }

    public function getSiteKey($html)
    {
        if (!$this->siteKey) {
            $this->setSiteKey($html);
        }

        return $this->siteKey;
    }
}
