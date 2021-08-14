<?php

declare(strict_types=1);

namespace app\admin\service;

use Exception;
use app\admin\model\User as UserModel;
use app\admin\model\UserLog;
use app\admin\model\UserRoleRel;

class User
{
    /**
     * 获取管理员列表数据
     *
     * @param array $data
     *
     * @return array
     */
    public function getUserList(array $data = []): array
    {
        return (new UserModel())->getUserList($data);
    }

    /**
     * 管理员新增
     *
     * @param $data
     *
     * @return array
     */
    public function add($data): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userModel = new UserModel();
        $data['password'] = $this->encryPassword($data['password']);

        $db = $userModel->db(false);
        $db->startTrans();
        try {
            //插入数据库
            $userModel->save($data);

            //设置角色
            $userRoleRelModel = new UserRoleRel();
            if (isset($data['role_ids'])) {
                $role_ids = explode(',', $data['role_ids']);
                $arr = [];
                foreach ($role_ids as $k => $v) {
                    $row['user_id'] = $userModel->id;
                    $row['role_id'] = $v;
                    $arr[] = $row;
                }
                $userRoleRelModel->saveAll($arr);
            }

            //添加注册日志
            $userLogModel = new UserLog();
            $userLogModel->saveLog($userModel->id, $userLogModel::USER_REGISTER, $data);

            $db->commit();
            $result['code'] = 0;
        } catch (Exception $e) {
            $db->rollback();
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 管理员更新
     *
     * @param $data
     *
     * @return array|int[]
     */
    public function edit($data): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userModel = new UserModel();
        //判断用户是否存在
        $userInfo = $userModel->where('id', $data['id'])->findOrEmpty();
        if (empty($userInfo)) {
            $result['code'] = 11000;

            return $result;
        }

        $db = $userModel->db(false);
        $db->startTrans();
        try {
            //防止用户修改敏感数据 比如密码等
            $userInfo->allowField(['username', 'mobile', 'avatar', 'nickname'])->save($data);

            //设置角色 清空所有的旧角色
            $userRoleRelModel = new UserRoleRel();
            $userRoleRelModel->where(['user_id' => $data['id']])->delete();
            if (isset($data['role_ids'])) {
                $role_ids = explode(',', $data['role_ids']);
                $arr = [];
                foreach ($role_ids as $k => $v) {
                    $row['user_id'] = $userModel->id;
                    $row['role_id'] = $v;
                    $arr[] = $row;
                }
                $userRoleRelModel->saveAll($arr);
            }

            $db->commit();
            $result = ['code' => 0];
        } catch (Exception $e) {
            $db->rollback();
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 管理员软删除
     *
     * @param $id
     *
     * @return array|int[]
     */
    public function del($id): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            UserModel::destroy($id);
            $result = ['code' => 0];
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 管理员登录
     *
     * @param $data
     *
     * @return array
     */
    public function login($data): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userModel = new UserModel();
        // 根据用户名或者手机号码查询
        $userInfo = $userModel->where(['username|mobile' => $data['username']])->findOrEmpty();

        // 没有找到此账号
        if ($userInfo->isEmpty()) {
            $result['code'] = 11000;

            return $result;
        }

        //判断账号状态 1-正常 2-停用
        if ($userInfo->status != $userModel::STATUS_NORMAL) {
            $result['code'] = 11001;

            return $result;
        }

        //判断密码是否相等
        $password = $this->encryPassword($data['password']);
        if ($userInfo['password'] == $password) {
            $result['code'] = 0;
            $result['data'] = $userInfo->toArray();
            unset($result['data']['password']);

            //添加登录日志
            $userLogModel = new UserLog();
            unset($data['password']);
            $userLogModel->saveLog($userInfo->id, $userLogModel::USER_LOGIN, $data);
        } else {
            // 密码错误，请重试
            $result['code'] = 11002;
        }

        return $result;
    }

    /**
     * 管理员退出
     *
     * @param $userId
     *
     * @return array
     */
    public function logout($userId): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            //添加退出日志
            $userLogModel = new UserLog();
            $userLogModel->saveLog($userId, $userLogModel::USER_LOGOUT);
            $result['msg'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }


    /**
     * 密码加密
     *
     * @param $password
     *
     * @return string
     */
    public function encryPassword(string $password): string
    {
        return sha1(md5($password) . config('database.prefix'));
    }

    /**
     * 管理员修改密码
     *
     * @param $user_id
     * @param $oldPassword
     * @param $newPassword
     *
     * @return array
     */
    public function changePwd($user_id, $oldPassword, $newPassword): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        $userModel = new UserModel();
        $info = $userModel->where(['id' => $user_id])->findOrEmpty();
        if (empty($info)) {
            $result['code'] = 11000;

            return $result;
        }
        if ($oldPassword == $newPassword) {
            $result['code'] = 11004;

            return $result;
        }
        if ($info['password'] != $this->encryPassword($oldPassword)) {
            $result['code'] = 11002;

            return $result;
        }
        $re = $info->allowField(['password'])->save(['password' => $this->encryPassword($newPassword)]);
        if ($re) {
            $result['code'] = 0;
            $result['msg'] = '修改成功';
        } else {
            $result['code'] = 10006;
        }

        return $result;
    }
}
