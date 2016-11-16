<?php
/**
 * Created by PhpStorm.
 * User: rapita
 * Date: 15.11.16
 * Time: 18:58
 */

namespace maxlen\anticaptcha;

use maxlen\anticaptcha\lib\v2\AntiCaptcha;
use maxlen\anticaptcha\lib\v2\ImageToText;
use maxlen\anticaptcha\lib\v2\NoCaptcha;
use maxlen\anticaptcha\lib\v2\NoCaptchaProxyless;

/**
 * Class CaptchaService
 * @package maxlen\anticaptcha
 */
class CaptchaService
{
    const TYPE_IMAGE_TO_TEXT = 'imageToText';
    const TYPE_NO_CAPTCHA = 'noCaptcha';
    const TYPE_NO_CAPTCHA_PROXYLESS = 'noCaptchaProxyless';
    const REG_WEBSITEKEY = '/(?<=data-sitekey=("|\'))[^\'"]*(?=("|\'))/i';
    const REG_WEBSITESTOKEN = '/(?<=data-stoken=("|\'))[^\'"]*(?=("|\'))/i';
    const REG_GET_ID = '/(?<=data-ray=("|\'))[^\'"]*(?=("|\'))/i';

    /** @var string $webSiteUrl */
    public $webSiteUrl;
    /** @var string $html */
    public $html;
    /** @var string $type */
    public $type;
    /** @var string $apiKey */
    public $apiKey;

    public $hashResult = false;
    /** @var string $siteKey */
    private $websiteKey;
    private $websiteSToken;
    private $resolveGetParams = [];
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
     * @param $webSiteUrl
     * @param $type
     * @param $html
     * @throws \Exception
     */
    public function __construct($apiKey, $webSiteUrl, $type, $html)
    {
        if (!in_array($type, self::getTypes())) {
            throw new \Exception("Unknown type: {$type}. Must be one of " . implode(',', self::getTypes()));
        }

        $this->webSiteUrl = $webSiteUrl;
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
            $this->hashResult = $this->api->getTaskSolution();
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
     * Return private siteKey
     * @return string
     */
    public function getWebSiteKey()
    {
        return $this->websiteKey;
    }

    /**
     * Search data-sitekey on html
     * @return bool
     */
    private function initSiteKey()
    {
        preg_match(self::REG_WEBSITEKEY, $this->html, $matches);

        if (isset($matches[0]) && $matches[0] != '') {
            $this->websiteKey = $matches[0];
            return $this->websiteKey;
        }

        return false;
    }

    /**
     * Return private siteKey
     * @return string
     */
    public function getWebSiteSToken()
    {
        return $this->websiteSToken;
    }

    /**
     * Search data-stoken on html
     * @return bool
     */
    private function initSiteSToken()
    {
        preg_match(self::REG_WEBSITESTOKEN, $this->html, $matches);

        if (isset($matches[0]) && $matches[0] != '') {
            $this->websiteSToken = $matches[0];
            return $this->websiteSToken;
        }

        return false;
    }

    /**
     * Return private resolveGetParams
     * @return array
     */
    public function getResolveGetParams()
    {
        return $this->resolveGetParams;
    }

    private function initSetGetparams()
    {
        preg_match(self::REG_GET_ID, $this->html, $matches);

        if (isset($matches[0]) && $matches[0] != '') {
            $this->resolveGetParams['id'] = $matches[0];
            return $this->resolveGetParams;
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
                $this->api->setWebsiteURL($this->webSiteUrl);
                break;

            case self::TYPE_NO_CAPTCHA_PROXYLESS:
                $this->api = new NoCaptchaProxyless();
                $this->api->setWebsiteURL($this->webSiteUrl);

                $this->api->setWebsiteKey($this->initSiteKey());

                $this->initSiteSToken(); // if source-site use old reCAPTCHA code
                if ($this->websiteSToken) {
                    $this->api->setWebsiteSToken($this->websiteSToken);
                }

                $this->resolveGetParams = $this->initSetGetparams();
                break;
        }

        return $this->api ? true : false;
    }
}
