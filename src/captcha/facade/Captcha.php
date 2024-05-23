<?php

namespace dfer\captcha\facade;

use think\Facade;

/**
 * Class Captcha
 * @package dfer\captcha\facade
 * @mixin \dfer\captcha\Captcha
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \dfer\captcha\Captcha::class;
    }
}
