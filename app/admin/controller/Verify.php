<?php

declare(strict_types=1);

namespace app\admin\controller;

use think\captcha\facade\Captcha;
use think\Response;

class Verify
{
    /**
     * 生成验证码
     *
     * @return Response
     */
    public function index(): Response
    {
        return Captcha::create('blogAdmin');
    }
}
