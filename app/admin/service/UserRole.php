<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\UserRole as UserRoleModel;
use app\admin\model\UserRoleOperationRel;
use app\admin\model\Operation;
use Exception;
use think\Model;

class UserRole
{
    /**
     * 获取角色列表数据
     *
     * @param array $data
     *
     * @return array
     */
    public function getRoleList($data = []): array
    {
        return (new UserRoleModel())->getRoleList($data);
    }

    /**
     * 角色新增
     *
     * @param array $data
     *
     * @return array
     */
    public function add($data = []): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            UserRoleModel::create($data);
            $result['code'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 角色删除
     *
     * @param int $id
     *
     * @return array
     */
    public function del($id = 0): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userRoleModel = new UserRoleModel();
        $userRoleOperationRelModel = new UserRoleOperationRel();

        $db = $userRoleModel->db(false);
        $db->startTrans();
        try {
            $userRoleModel->where(['id' => $id])->delete();
            $userRoleOperationRelModel->where(['user_role_id' => $id])->delete();
            $db->commit();
            $result['code'] = 0;
        } catch (Exception $e) {
            $db->rollback();
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 根据角色ID获取角色信息
     *
     * @param int $id
     *
     * @return UserRoleModel|array|Model
     */
    public function getRoleById($id = 0)
    {
        $userRoleModel = new UserRoleModel();

        return $userRoleModel->where(['id' => $id])->findOrEmpty();
    }

    /**
     * 根据角色ID获取权限
     *
     * @param int $id
     *
     * @return array
     */
    public function getRoleOperation($id = 0): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            $userRoleModel = new UserRoleModel();
            $roleInfo = $userRoleModel->where(['id' => $id])->findOrEmpty();
            if (empty($roleInfo)) {
                $result['code'] = 11071;

                return $result;
            }

            //查找角色拥有的节点
            $userRoleOperationRelModel = new UserRoleOperationRel();
            $permList = $userRoleOperationRelModel->where(['user_role_id' => $id])->select();
            if ($permList->isEmpty()) {
                $nodeList = [];
            } else {
                $nodeList = array_column($permList->toArray(), 'user_role_id', 'operation_id');
            }

            $operationModel = new Operation();
            $result['code'] = 0;
            $result['data'] = $operationModel->menuTree($operationModel::MENU_MANAGE, $nodeList);
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 保存角色
     *
     * @param int   $id
     * @param array $data
     *
     * @return array
     */
    public function savePerm($id = 0, $data = []): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            $userRoleOperationRelModel = new UserRoleOperationRel();
            $userRoleOperationRelModel->savePerm($id, $data);
            $result['code'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }
}
