<?php

declare(strict_types=1);

namespace app\admin\controller;

use think\response\View;

class Home
{
    /**
     * 主页工作台
     *
     * @return View
     */
    public function index()
    {
        return view();
    }

    /**
     * 便签页
     *
     * @return View
     */
    public function note()
    {
        return view();
    }

    /**
     * 消息页
     *
     * @return View
     */
    public function message()
    {
        return view();
    }

    /**
     * 主题页
     *
     * @return View
     */
    public function theme()
    {
        return view();
    }
}
