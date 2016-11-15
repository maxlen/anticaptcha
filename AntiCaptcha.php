<?php
/**
 * Created by PhpStorm.
 * User: w
 * Date: 15.11.16
 * Time: 15:50
 */

namespace maxlen\anticaptcha;

use maxlen\anticaptcha\api\v2\nocaptcha;


class AntiCaptcha extends nocaptcha
{
    CONST REG_SITEKEY = '/(?<=data-sitekey=("|\'))[^\'"]*(?=("|\'))/i';
}