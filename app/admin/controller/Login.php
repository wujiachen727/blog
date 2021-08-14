<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\admin\validate\User as UserValidate;
use app\admin\service\User as UserService;
use think\response\Json;
use think\response\Redirect;
use think\response\View;

class Login extends BaseController
{
    /**
     * 管理员登录页
     *
     * @return View
     */
    public function index(): View
    {
        return view();
    }

    /**
     * 管理员登录
     *
     * @return Json|Redirect
     */
    public function login()
    {
        // 如果已经登录了，直接跳转到系统首页
        if (session('?user')) {
            return redirect((string)url('index/index'));
        }

        if ($this->request->isPost()) {
            // 获取参数
            $param = $this->request->post();

            // 用户登陆场景验证
            $validate = new UserValidate();
            if (!$validate->scene('login')->check($param)) {
                return error_code(10001, $validate->getError());
            }

            // 失败的次数和锁定的时间
            $fail_num = "user_login_fail_num_" . $param['username'];
            $lock_time = "user_login_fail_lock_" . $param['username'];

            // 连续登录失败次数超过5次，5分钟内禁止登录
            if (session('?' . $lock_time)) {
                if (time() - session($lock_time) < 5 * 60) {
                    return error_code(11003);
                }
            }

            // 保存管理员信息
            $userService = new UserService();
            $result = $userService->login($param);

            if ($result['code'] == 0) {
                // 登录成功
                session($fail_num, null);
                session($lock_time, null);
                session('user', $result['data']);

                return show($result);
            } else {
                if ($result['code'] == 11002) {
                    // 写失败次数到session里
                    if (session('?' . $fail_num)) {
                        // 登录失败次数连续超过5次，5分钟之内该账户冻结
                        session($fail_num, session($fail_num) + 1);
                        if (session($fail_num) > 5) {
                            session($lock_time, time());
                        }
                    } else {
                        session($fail_num, 1);
                    }
                }
            }

            return error_code($result['code']);
        } else {
            return error_code(100);
        }
    }

    /**
     * 管理员退出
     *
     * @return Redirect
     */
    public function logout(): Redirect
    {
        // 增加退出日志
        if (session('user.id')) {
            $userService = new UserService();
            $userService->logout(session('user.id'));
        }

        // 清空session
        session('user', null);

        return redirect((string)url('login/index'));
    }
}
