<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\UserLog as UserLogModel;

class UserLog
{
    /**
     * 获取管理员日志列表
     *
     * @param $data
     *
     * @return array
     */
    public function getUserLogList($data): array
    {
        return (new UserLogModel())->getUserLogList($data);
    }
}
