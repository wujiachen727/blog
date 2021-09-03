<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use Exception;
use think\Model;

/**
 * @mixin Model
 */
class UserRoleOperationRel extends Common
{
    /**
     * 保存角色权限
     *
     * @param int   $role_id
     * @param array $operations
     *
     * @return bool
     */
    public function savePerm(int $role_id = 0, array $operations = []): bool
    {
        $db = $this->db(false);
        $db->startTrans();
        try {
            //先删除当前角色的所有权限
            $this->where(['user_role_id' => $role_id])->delete();

            $data = [];
            $row['user_role_id'] = $role_id;
            foreach ($operations as $v) {
                $role_id['operation_id'] = $v['id'];
                $data[] = $row;
            }
            $this->saveAll($data);
            $db->commit();
            $result = true;
        } catch (Exception $e) {
            $db->rollback();
            $result = false;
        }

        return $result;
    }

    /**
     * 获取角色的权限列表
     * role_id==0时 为超级管理员 取所有
     *
     * @param int $role_id
     *
     * @return array|mixed
     */
    public function getTree($role_id = 0): array
    {
        $operationModel = new Operation();
        if ($role_id == 0) {
            $tree = $operationModel->menuTree($operationModel::MENU_START);
        } else {
            $tree = $this->permTree($role_id, $operationModel::MENU_START);
        }

        return $tree;
    }

    /**
     * 递归获取当前角色权限列表树
     *
     * @param $role_id
     * @param $pid
     *
     * @return mixed
     */
    public function permTree($role_id, $pid): array
    {
        $operationModel = new Operation();
        $where = [
            'o.parent_menu_id'  => $pid,
            'o.perm_type'       => $operationModel::PERM_TYPE_SUB,
            'uror.user_role_id' => $role_id
        ];
        $list = $this->field('o.*')->alias('uror')
            ->join(config('database.prefix') . 'operation o', 'o.id = uror.operation_id')
            ->where($where)->select();

        if ($list->isEmpty()) {
            $list = [];
        } else {
            $list = $list->toArray();
        }

        foreach ($list as $k => $v) {
            $list[$k]['checkboxValue'] = $v['id'];
            $list[$k]['checked'] = true;
            $list[$k]['children'] = $this->permTree($role_id, $v['id']);
        }

        return $list;
    }

    /**
     * 获取当前用户所有操作权限
     *
     * @param int $user_id
     *
     * @return array
     */
    public function getPerm($user_id = 0): array
    {
        $list = $this->distinct(true)->field('o.*')->alias('uror')
            ->join(config('database.prefix') . 'operation o', 'o.id = uror.operation_id')
            ->join(config('database.prefix') . 'user_role_rel urr', 'uror.user_role_id = urr.role_id')
            ->where('urr.user_id', '=', $user_id)
            ->select();
        if ($list->isEmpty()) {
            $list = [];
        } else {
            $list = $list->toArray();
        }

        return $list;
    }
}
