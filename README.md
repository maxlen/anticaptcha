# anticaptcha-php

Wrapper for anti-captcha.com

### Use:

// $html - it's html-code from $url
// $html = file_get_contents($url) or something like that

if (Captcha::isCaptcha($html)) {
    $html = self::CaptchaProc($url, $html);
    die('CAPTCHA RESOLVED');
}

private function CaptchaProc($url, $html)
{
    $resCaptcha = new CaptchaService(
        $apiKey, // your apiKey from anti-captcha.com
        $url,
        CaptchaService::TYPE_NO_CAPTCHA_PROXYLESS,
        $html
    );

//        echo PHP_EOL . "getWebSiteKey: " . $resCaptcha->getWebSiteKey();
//        echo PHP_EOL . "getWebSiteSToken: " . $resCaptcha->getWebSiteSToken();
//        echo PHP_EOL;
//        var_dump($resCaptcha->getResolveGetParams());

    if (!$resCaptcha->check()) {
        return false;
    }

    $resolveUrl = $url . '&g-recaptcha-response=' . $resCaptcha->hashResult;
    return file_get_contents($resolveUrl); // curl or something like that
}