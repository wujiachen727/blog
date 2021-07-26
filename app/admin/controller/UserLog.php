<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\UserLog as UserLogService;
use think\response\Json;
use think\response\View;

class UserLog extends Admin
{
    /**
     * 管理员日志列表
     *
     * @return View
     */
    public function index(): View
    {
        return view();
    }

    /**
     * 获取管理员日志列表
     *
     * @return Json
     */
    public function getUserLogList(): Json
    {
        $data = $this->request->get();
        $result = (new UserLogService())->getUserLogList($data);

        return show($result);
    }
}
