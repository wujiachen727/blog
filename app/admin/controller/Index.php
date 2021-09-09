<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\Operation as OperationService;
use think\facade\View;

/**
 * 后台首页
 *
 * Class Index
 * @package app\admin\controller
 */
class Index extends Admin
{
    public function index(): string
    {
        return View::fetch('index', [
            'menuList' => $this->getMenuList(),
            'userInfo' => $this->userInfo
        ]);
    }

    /**
     * 获取个人菜单列表
     *
     * @return array
     */
    public function getMenuList(): array
    {
        $userInfo = $this->userInfo;
        if (!cache('user_operation_' . $userInfo['id'])) {
            //缓存为空或缓存到期
            $operationService = new OperationService();
            $menuList = $operationService->userMenu($userInfo['id']);
        } else {
            $menuList = json_decode(cache('user_operation_' . $userInfo['id']));
        }

        return $menuList;
    }
}
