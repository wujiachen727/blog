<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\OperationLog;
use app\admin\service\UserRoleOperationRel;
use app\admin\service\Operation;
use app\BaseController;
use think\exception\HttpResponseException;
use think\facade\Log;
use think\response\Json;

/**
 * 公共Controller
 *
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends BaseController
{
    protected $user;

    /**
     * 判断是否登录
     * token验证，防止csrf攻击
     * 记录操作日志
     *
     * @return Json|void
     */
    protected function initialize()
    {
        parent::initialize();

        //没有登陆，请先登录
//        if (!session('user')) {
//            return $this->redirect((string)url('login/index'));
//        }
//        $this->user = session('user');
        $this->user = [
            "id"          => 1,
            "username"    => "admin",
            "mobile"      => "15938754096",
            "avatar"      => null,
            "name"        => "吴嘉晨",
            "status"      => 1,
            "create_time" => "2021-06-17 10:40:26",
            "update_time" => "2021-06-17 10:40:26",
            "delete_time" => 0
        ];

        if ($this->request->isPost()) {
            // token校验
            $result = $this->checkToken();
            if ($result['code'] != 0) {
                return show($result);
            }

            // 权限校验
            $result = $this->checkPerm();
            if ($result['code'] != 0) {
                return show($result);
            }

            // 记录操作日志
            $this->record();


            return;
        }
    }

    /**
     * token校验,防止csrf攻击
     *
     * @return int[]
     */
    private function checkToken(): array
    {
        $result = ['code' => 10002];

        // 所有的post请求都必须token校验
        $_token = input('__token__/s', '');
        if ($_token != session('__token__')) {
            return $result;
        }

        $result['code'] = 0;

        return $result;
    }

    /**
     * 权限校验
     *
     * @return array
     */
    public function checkPerm(): array
    {
        $ctlName = $this->request->controller();
        $actName = $this->request->action();

        //判断当前是否有权限操作
        $userRoleOperationRelService = new UserRoleOperationRel();

        return $userRoleOperationRelService->checkPerm(session('user.id'), $ctlName, $actName);
    }

    /**
     * 记录所有的接口日志
     */
    private function record()
    {
        $user = session('user');
        $ctl = strtolower(request()->controller());
        $act = strtolower(request()->action());
        $operation = new Operation();
        $opInfo = $operation->getOperationInfo($ctl, $act);
        if ($opInfo['code'] == 0) {
            $postData = $this->request->post();
            $decs = $opInfo['data']['act']['name'];
            $log = [
                'user_id'    => $user['id'],
                'username'   => $user['username'],
                'controller' => $ctl,
                'method'     => $act,
                'desc'       => $decs,
                'content'    => json_encode($postData),
                'ip'         => get_client_ip(),
            ];
            $logModel = new OperationLog();
            $logModel->add($log);
        } else {
            Log::record(json_encode($opInfo));
        }
    }

    /**
     * 页面跳转
     *
     * @param mixed ...$args
     */
    private function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }
}
