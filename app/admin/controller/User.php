<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\UserRole as UserRoleService;
use app\admin\validate\User as UserValidate;
use app\admin\service\User as UserService;
use think\response\Json;
use think\facade\View;

/**
 * 后台管理员类
 */
class User extends Admin
{
    /**
     * 管理员列表
     *
     * @return string
     */
    public function index(): string
    {
        $userRoleService = new UserRoleService();
        $roleList = $userRoleService->getRoleNameList();

        return View::fetch('index', [
            'roleList' => json_encode($roleList),
            'userInfo' => $this->userInfo
        ]);
    }

    /**
     * 获取管理员列表
     *
     * @return Json
     */
    public function getUserList(): Json
    {
        $data = $this->request->get();
        $result = (new UserService())->getUserList($data);

        return show($result);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return string
     */
    public function create(): string
    {
        $userRoleService = new UserRoleService();
        $roleList = $userRoleService->getRoleNameList();

        return View::fetch('create', [
            'roleList' => json_encode($roleList)
        ]);
    }

    /**
     * 管理员新增
     *
     * @return Json
     */
    public function save(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            unset($data['id']);      // 防止用户传入id
            $data['delete_time'] = 0;// 防止插入删除用户

            $userValidate = new UserValidate();
            if (!$userValidate->scene('add')->check($data)) {
                return error_code(10001, $userValidate->getError());
            }

            //保存管理员信息
            $userService = new UserService();
            $result = $userService->add($data);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @return string
     */
    public function edit(): string
    {
        $userRoleService = new UserRoleService();
        $roleList = $userRoleService->getRoleNameList();

        return View::fetch('edit', [
            'roleList' => json_encode($roleList)
        ]);
    }

    /**
     * 保存更新的资源
     *
     * @return Json
     */
    public function update(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $userValidate = new UserValidate();
            if (!$userValidate->scene('edit')->check($data)) {
                return error_code(10001, $userValidate->getError());
            }

            if ($data['id'] <= 0) {
                return error_code(10003);
            }

            if ($data['id'] == 1) {
                //超级管理员
                return error_code(11005);
            }

            //更新管理员信息
            $userService = new UserService();
            $result = $userService->edit($data);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 管理员状态更新
     *
     * @return Json
     */
    public function updateStatus(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            if ($data['id'] <= 0) {
                return error_code(10003);
            }

            if ($data['id'] == 1) {
                //超级管理员
                return error_code(11005);
            }

            //更新管理员信息
            $userService = new UserService();
            $result = $userService->edit($data);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 删除指定资源
     *
     * @return Json
     */
    public function delete(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $userValidate = new UserValidate();
            if (!$userValidate->scene('del')->check($data)) {
                return error_code(10001, $userValidate->getError());
            }

            if ($data['id'] <= 0) {
                return error_code(10003);
            }

            if ($data['id'] == 1) {
                //超级管理员
                return error_code(11005);
            }

            $userService = new UserService();
            $result = $userService->del($data['id']);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 管理员修改密码
     *
     * @return Json
     */
    public function editPwd(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $userValidate = new UserValidate();
            if (!$userValidate->scene('editPwd')->check($data)) {
                return error_code(10001, $userValidate->getError());
            }

            $userService = new UserService();
            $result = $userService->changePwd(session('user.id'), $data('password'), $data('newPwd'));

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 管理员重置密码
     *
     * @return Json
     */
    public function resetPwd(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $userValidate = new UserValidate();
            if (!$userValidate->scene('del')->check($data)) {
                //借用删除场景检验
                return error_code(10001, $userValidate->getError());
            }

            if ($data['id'] <= 0) {
                return error_code(10003);
            }

            if ($data['id'] == 1) {
                //超级管理员
                return error_code(11005);
            }

            $userService = new UserService();
            $result = $userService->resetPwd($data['id']);

            return show($result);
        } else {
            return error_code(100);
        }
    }
}
