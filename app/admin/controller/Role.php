<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\UserRole;
use app\admin\validate\UserRole as UserRoleValidate;
use think\Response;
use app\admin\service\UserRole as UserRoleService;
use think\response\Json;
use think\response\View;

/**
 * 后台管理员角色类
 */
class Role extends Admin
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
     * 获取角色列表
     *
     * @return Json
     */
    public function getRoleList(): Json
    {
        $data = $this->request->get();
        $result = (new UserRoleService())->getRoleList($data);

        return show($result);
    }

    /**
     * 获取角色名称列表
     *
     * @return Json
     */
    public function getRoleNameList(): Json
    {
        $result = (new UserRoleService())->getRoleNameList();

        return show($result);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return View
     */
    public function create(): View
    {
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @return Json
     */
    public function save(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $userRoleValidate = new UserRoleValidate();
            if (!$userRoleValidate->scene('add')->check($data)) {
                return error_code(10001, $userRoleValidate->getError());
            }

            $userRoleService = new UserRoleService();
            unset($data['id']);
            $data['create_id'] = $this->userInfo['id'];
            $data['update_id'] = $this->userInfo['id'];
            $result = $userRoleService->add($data);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 显示编辑资源表单页
     *
     * @return View
     */
    public function edit(): View
    {
        return view();
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

            $userValidate = new UserRoleValidate();
            if (!$userValidate->scene('edit')->check($data)) {
                return error_code(10001, $userValidate->getError());
            }

            if ($data['id'] <= 0) {
                return error_code(10003);
            }

            if ($data['id'] == 1) {
                //超级管理员
                return error_code(12005);
            }

            //更新管理员信息
            $userService = new UserRoleService();
            $data['update_id'] = $this->userInfo['id'];
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
            $id = $this->request->post('id');

            if ($id <= 0) {
                return error_code(10003);
            }

            $userRoleService = new UserRoleService();
            $result = $userRoleService->del($id);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 获取权限信息
     *
     * @return Json
     */
    public function getOperation(): Json
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');

            if ($id <= 0) {
                return error_code(10003);
            }

            $userRoleService = new UserRoleService();
            $result = $userRoleService->getRoleOperation($id);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 保存角色权限
     *
     * @return Json
     */
    public function savePerm(): Json
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!isset($data['id'])) {
                return error_code(10003);
            }
            if (!isset($data['data'])) {
                return error_code(10003);
            }

            $userRoleService = new UserRoleService();
            $info = $userRoleService->getRoleById($data['id']);
            if (empty($info)) {
                return error_code(11071);
            }
            $result = $userRoleService->savePerm($data['id'], $data['data']);

            return show($result);
        } else {
            return error_code(100);
        }
    }
}
