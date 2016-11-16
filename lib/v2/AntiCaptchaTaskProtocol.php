<?php
/**
 * Created by PhpStorm.
 * User: rapita
 * Date: 15.11.16
 * Time: 18:59
 */

namespace maxlen\anticaptcha\lib\v2;

interface AntiCaptchaTaskProtocol
{
    public function getPostData();
    public function setTaskInfo($taskInfo);
    public function getTaskSolution();
}