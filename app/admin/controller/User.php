<?php

declare(strict_types=1);

namespace app\admin\controller;

use think\Response;
use app\admin\validate\User as UserValidate;
use app\admin\service\User as UserService;
use think\response\Json;
use think\response\View;

/**
 * 用户管理
 *
 * Class User
 * @package app\admin\controller
 */
class User extends Admin
{
    /**
     * 显示资源列表
     *
     * @return View
     */
    public function index(): View
    {
        return view();
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
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * 管理员新增
     *
     * @return mixed|string|Json
     */
    public function save()
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
     * 显示指定的资源
     *
     * @param int $id
     *
     * @return Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @return Response
     */
    public function update(): Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['delete_time'] = 0;// 防止删除用户

            $userValidate = new UserValidate();
            if (!$userValidate->scene('edit')->check($data)) {
                return error_code(10001, $userValidate->getError());
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
     * @return Response
     */
    public function delete(): Response
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');

            $userValidate = new UserValidate();
            if (!$userValidate->scene('del')->check($id)) {
                return error_code(10001, $userValidate->getError());
            }

            if ($id <= 0) {
                return error_code(10003);
            }

            $userService = new UserService();
            $result = $userService->del($id);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 用户修改密码
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
}
