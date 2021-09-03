<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\UserRole as UserRoleModel;
use app\admin\model\UserRoleOperationRel as UserRoleOperationRelModel;
use app\admin\model\User as UserModel;
use app\admin\model\UserRoleRel as UserRoleRelModel;
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
    public function getRoleList(array $data = []): array
    {
        return (new UserRoleModel())->getRoleList($data);
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function getRoleNameList(): array
    {
        try {
            $userRoleModel = new UserRoleModel();
            //超级管理员角色不允许新增
            $result = $userRoleModel->field('id as value,name')->where('id', '>', UserModel::TYPE_SUPER_ID)
                ->select()->toArray();
        } catch (Exception $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * 角色新增
     *
     * @param array $data
     *
     * @return array
     */
    public function add(array $data): array
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
     * 角色更新
     *
     * @param array $data
     *
     * @return array
     */
    public function edit(array $data): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userRoleModel = new UserRoleModel();
        //判断角色是否存在
        $userRoleInfo = $userRoleModel->where(['id' => $data['id']])->findOrEmpty();
        if (empty($userRoleInfo)) {
            $result['code'] = 12000;

            return $result;
        }
        try {
            $userRoleModel::update($data);
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
    public function del(int $id = 0): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userRoleRelModel = new UserRoleRelModel();
        $count = $userRoleRelModel->where(['role_id' => $id])->count();
        if ($count > 0) {
            $result['code'] = 12001;

            return $result;
        }
        $userRoleModel = new UserRoleModel();
        $userRoleOperationRelModel = new UserRoleOperationRelModel();
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
    public function getRoleById(int $id = 0)
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
    public function getRoleOperation(int $id = 0): array
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
            $userRoleOperationRelModel = new UserRoleOperationRelModel();
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
     * 保存角色权限
     *
     * @param int   $id
     * @param array $data
     *
     * @return array
     */
    public function savePerm(int $id = 0, array $data = []): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            $userRoleOperationRelModel = new UserRoleOperationRelModel();
            $userRoleOperationRelModel->savePerm($id, $data);
            $result['code'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }
}
