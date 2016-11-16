<?php
/**
 * Created by PhpStorm.
 * User: rapita
 * Date: 15.11.16
 * Time: 18:58
 */

namespace maxlen\anticaptcha;

use maxlen\anticaptcha\lib\AntiCaptcha;
use maxlen\anticaptcha\lib\ImageToText;
use maxlen\anticaptcha\lib\NoCaptcha;
use maxlen\anticaptcha\lib\NoCaptchaProxyless;

/**
 * Class CaptchaService
 * @package maxlen\anticaptcha
 */
class CaptchaService
{
    const TYPE_IMAGE_TO_TEXT = 'imageToText';
    const TYPE_NO_CAPTCHA = 'noCaptcha';
    const TYPE_NO_CAPTCHA_PROXYLESS = 'noCaptchaProxyless';
    const REG_SITEKEY = '/(?<=data-sitekey=("|\'))[^\'"]*(?=("|\'))/i';

    /** @var string $url */
    public $url;
    /** @var string $html */
    public $html;
    /** @var string $type */
    public $type;
    /** @var string $apiKey */
    public $apiKey;
    /** @var string $siteKey */
    private $siteKey;
    /** @var AntiCaptcha $api */
    private $api;

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [self::TYPE_IMAGE_TO_TEXT, self::TYPE_NO_CAPTCHA, self::TYPE_NO_CAPTCHA_PROXYLESS];
    }

    /**
     * CaptchaService constructor.
     * @param $apiKey
     * @param $url
     * @param $html
     * @param $type
     * @throws \Exception
     */
    public function __construct($apiKey, $url, $html, $type)
    {
        if (!in_array($type, self::getTypes())) {
            throw new \Exception('Unknown type.');
        }

        $this->url = $url;
        $this->html = $html;
        $this->type = $type;
        $this->apiKey = $apiKey;

        $this->init();
    }

    /**
     * Main handle
     * @return bool
     */
    public function check()
    {
        if (!$this->api->createTask()) {
            $this->api->debout("API v2 send failed - ". $this->api->getErrorMessage(), "red");
            return false;
        }

        $taskId = $this->api->getTaskId();

        if (!$this->api->waitForResult()) {
            $this->api->debout("could not solve captcha", "red");
            $this->api->debout($this->api->getErrorMessage());
        } else {
            return "\nhash result: ".$this->api->getTaskSolution()."\n\n";
        }

        return false;
    }

    /**
     * Initialized needed info
     * @return bool
     */
    public function init()
    {
        if ($this->initSiteKey() && $this->initApi()) {
            $this->api->setVerboseMode(true);
            $this->api->setKey($this->apiKey);
            return true;
        }

        return false;
    }

    /**
     * Search site key on html
     * @return bool
     */
    private function initSiteKey()
    {
        preg_match(self::REG_SITEKEY, $this->html, $matches);

        if (isset($matches[0]) && $matches[0] != '') {
            $this->siteKey = $matches[0];
            return true;
        }

        return false;
    }

    /**
     * Initialized api object
     * @return bool
     */
    private function initApi()
    {
        switch ($this->type) {
            case self::TYPE_IMAGE_TO_TEXT:
                $this->api = new ImageToText();
                break;
            case self::TYPE_NO_CAPTCHA:
                $this->api = new NoCaptcha();
                break;
            case self::TYPE_NO_CAPTCHA_PROXYLESS:
                $this->api = new NoCaptchaProxyless();
                break;
        }

        return $this->api ? true : false;
    }
}
