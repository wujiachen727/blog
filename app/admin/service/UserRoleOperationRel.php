<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\Operation;
use app\admin\model\User;
use app\admin\model\UserRoleOperationRel as UserRoleOperationRelModel;

class UserRoleOperationRel
{
    /**
     * 判断管理员是否有当前的操作权限
     *
     * @param $user_id
     * @param $ctlName
     * @param $actName
     *
     * @return array
     */
    public function checkPerm($user_id, $ctlName, $actName): array
    {
        $result = ['code' => 10000, 'msg' => ''];

        $operationModel = new Operation();

        //如果是指定的模块直接返回即可，不做权限操作
        $p_id = $operationModel::MENU_MANAGE;
        if ($operationModel->checkNeedPerm($p_id, $ctlName, $actName)) {
            $result['code'] = 0;

            return $result;
        }

        //如果是超级管理员 直接返回
        $userModel = new User();
        if ($user_id == $userModel::TYPE_SUPER_ID) {
            $result['code'] = 0;

            return $result;
        }

        //普通管理员 取所有的角色对应的权限
        $userRoleOperationRelModel = new UserRoleOperationRelModel();
        $list = $userRoleOperationRelModel->getPerm($user_id);
        if (empty($list)) {
            //权限为空
            return $result;
        }
        $newList = array_column($list, 'name', 'id');

        //查控制器所对应的操作记录
        $contWhere = [
            'type'      => 'c',
            'parent_id' => $p_id,
            'code'      => $ctlName
        ];
        $contOperation = $operationModel->where($contWhere)->findOrEmpty();
        if (empty($contOperation)) {
            $result['code'] = 11088;

            return $result;
        }

        //查询方法
        $actWhere['type'] = 'a';
        $actWhere['parent_id'] = $contOperation['id'];
        $actWhere['code'] = $actName;
        $actOperation = $operationModel->where($actWhere)->findOrEmpty();
        if (empty($actOperation)) {
            $result['code'] = 11089;

            return $result;
        }
        //查看是否是是关联权限，如果是关联权限去查找对应的关联操作的权限
        if ($actOperation['perm_type'] == $operationModel::PERM_TYPE_REL) {
            $actOperation = $operationModel->where(['id' => $actOperation['parent_menu_id']])->findOrEmpty();
            if (empty($actOperation)) {
                $result['code'] = 11090;

                return $result;
            }
        }

        if (isset($newList[$actOperation['id']])) {
            $result['code'] = 0;
        }

        return $result;
    }
}
