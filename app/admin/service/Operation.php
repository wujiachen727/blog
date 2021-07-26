<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\Operation as OperationModel;
use Exception;
use think\App;
use app\admin\model\User;
use app\admin\model\UserRoleRel;
use think\Model;

class Operation
{
    /**
     * 保存节点
     *
     * @param array $data
     *
     * @return array
     */
    public function add($data = []): array
    {
        $result = ['code' => 10000, 'msg' => ''];

        //如果是方法，code调整为小写
        if ($data['type'] == 'a') {
            $data['code'] = strtolower($data['code']);
        }

        $operationModel = new OperationModel();
        //校验父节点和当前类型
        if ($data['parent_id'] != $operationModel::MENU_START) {
            //判断是否是合法父节点 父节点不可能是方法
            $where = [
                ['type', '<>', 'a'],
                ['id', '=', $data['parent_id']]
            ];
            $parentInfo = $operationModel->where($where)->findOrEmpty();
            if (empty($parentInfo)) {
                return $result;
            }
            //判断当前类型和父类型是否对应
            if ($parentInfo['type'] == 'm') {
                if ($data['type'] != 'c') {
                    $result['code'] = 11093;

                    return $result;
                }
            }
            if ($parentInfo['type'] == 'c') {
                if ($data['type'] != 'a') {
                    $result['code'] = 11094;

                    return $result;
                }
            }
        } else {
            if ($data['type'] != 'm') {
                $result['code'] = 11095;

                return $result;
            }
        }
        //判断当前编码是否重复
        $where = [
            ['parent_id', '=', $data['parent_id']],
            ['code', '=', $data['code']]
        ];
        if ($data['id'] != "") {
            $where[] = ['id', '<>', $data['id']];
        }
        $info = $operationModel->where($where)->findOrEmpty();
        if ($info) {
            $result['code'] = 11096;

            return $result;
        }

        //判断父菜单节点是否存在
        if ($data['parent_menu_id'] != $operationModel::MENU_START) {
            $menuParentInfo = $operationModel->where('id', '=', $data['parent_menu_id'])->findOrEmpty();
            if (empty($menuParentInfo)) {
                return $result;
            }
        }

        if ($data['id'] != "") {
            //当前是修改，就需要判断是否会陷入死循环
            if (!$operationModel->checkDie($data['id'], $data['parent_id'], 'parent_id')) {
                $result['code'] = 11097;

                return $result;
            }
            if (!$operationModel->checkDie($data['id'], $data['parent_menu_id'], 'parent_menu_id')) {
                $result['code'] = 11098;

                return $result;
            }
            $id = $data['id'];
            unset($data['id']);
            $re = $operationModel->update($data, ['id' => $id]);
        } else {
            $re = $operationModel->save($data);
        }

        if ($re) {
            $result['code'] = 0;
        }

        return $result;
    }

    /**
     * 删除节点
     *
     * @param int $id
     *
     * @return array
     */
    public function del($id = 0): array
    {
        try {
            $result = ['code' => 10000, 'msg' => ''];

            $operationModel = new OperationModel();

            //如果没有下级了，就可以删了
            $children = $operationModel->where(['parent_id' => $id])->select();
            if ($children->isEmpty()) {
                $re = $operationModel->where(['id' => $id])->delete();
                if ($re) {
                    $result['code'] = 0;
                } else {
                    $result['code'] = 10007;
                }
            } else {
                $result['code'] = 11091;
            }
        } catch (Exception $e) {
            $result['code'] = 10007;
            $result['data'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 根据用户ID获取用户所拥有的菜单
     *
     * @param int $user_id
     *
     * @return array
     */
    public function userMenu($user_id = 0): array
    {
        $menu = cache('user_operation_' . $user_id);
        if ($menu) {
            //有缓存值直接获取即可
            return json_decode($menu, true);
        }
        //缓存不存在或已过期，重新取值
        $userModel = new User();
        $operationModel = new OperationModel();
        $userRoleRelModel = new UserRoleRel();
        if ($user_id == $userModel::TYPE_SUPER_ID) {
            //超级管理员直接返回
            $menu = $operationModel->userMenu(true);
        } else {
            //取当前管理员的所有角色
            try {
                $roles = $userRoleRelModel->where('user_id', $user_id)->select();
                if ($roles->isEmpty()) {
                    $roles = [];
                } else {
                    $roles = array_column($roles->toArray(), 'role_id');
                }
            } catch (Exception $e) {
                $roles = [];
            }

            $menu = $operationModel->userMenu(false, $user_id, $roles);
        }
        //写入缓存
        cache('user_operation_' . $user_id, json_encode($menu), 3600);

        return $menu;
    }

    /**
     * 获取操作名称
     *
     * @param string $ctl
     * @param string $act
     *
     * @return array
     */
    public function getOperationInfo($ctl = 'index', $act = 'index'): array
    {
        $result = ['code' => 10000, 'msg' => ''];

        $operationModel = new OperationModel();
        $model_id = $operationModel::MENU_MANAGE;
        $where = [
            'type'      => 'c',
            'code'      => $ctl,
            'parent_id' => $model_id
        ];
        $ctlInfo = $operationModel->where($where)->findOrEmpty();
        if (empty($ctlInfo)) {
            $result['code'] = 11088;

            return $result;
        }

        $where = [
            'type'      => 'a',
            'code'      => $act,
            'parent_id' => $ctlInfo['id']
        ];
        $actInfo = $operationModel->where($where)->findOrEmpty();
        if (empty($actInfo)) {
            $result['code'] = 11089;

            return $result;
        }

        $result['code'] = 0;
        $result['data'] = [
            'ctl' => $ctlInfo,
            'act' => $actInfo,
        ];

        return $result;
    }

    /**
     * 获取节点信息列表
     *
     * @param $data
     *
     * @return array
     */
    public function getOperationList($data): array
    {
        $operationModel = new OperationModel();
        if (!isset($data['parent_id']) || $data['parent_id'] == "") {
            $data['parent_id'] = $operationModel::MENU_MANAGE;
        }

        return $operationModel->getOperationList($data);
    }

    /**
     * 获取菜单全树
     *
     * @return array
     */
    public function getTree(): array
    {
        try {
            $operationModel = new OperationModel();
            $list = $operationModel->where('type', '<>', 'a')->order('sort asc')->select()->toArray();

            return $operationModel->createTree($list, $operationModel::MENU_START, 'parent_id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 获取菜单树
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        try {
            $operationModel = new OperationModel();
            $menuList = $operationModel->where('perm_type', '<', 3)->order('sort asc')->select()->toArray();

            return $operationModel->createTree($menuList, $operationModel::MENU_START, 'parent_menu_id');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 获取单个节点信息
     *
     * @param int $id
     *
     * @return OperationModel|array|Model
     */
    public function getInfo($id = 0)
    {
        return OperationModel::where('id', $id)->findOrEmpty();
    }
}
