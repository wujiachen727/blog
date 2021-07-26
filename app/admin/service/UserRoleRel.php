<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\UserRoleRel as UserRoleRelModel;
use Exception;

class UserRoleRel
{
    /**
     * 依据管理员id获取关联角色
     *
     * @param $user_id
     *
     * @return array
     */
    public function getRelByUserId($user_id): array
    {
        try {
            $userRoleRelModel = new UserRoleRelModel();
            $userRoleRel = $userRoleRelModel->where(['user_id' => $user_id])->select();
            if ($userRoleRel->isEmpty()) {
                $userRoleRel = [];
            } else {
                $userRoleRel = $userRoleRel->toArray();
            }
        } catch (Exception $e) {
            $userRoleRel = [];
        }

        return $userRoleRel;
    }
}
