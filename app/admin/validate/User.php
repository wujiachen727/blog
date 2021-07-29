<?php

declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 用户校验规则
     *
     * @var array
     */
    protected $rule = [
        'id'         => 'require|integer',
        'username'   => 'require|length:3,20|alphaDash|unique:user,delete_time^username',
        'password'   => 'require|length:6,16|checkPwd',
        'rePassword' => 'require|confirm:password',
        'mobile'     => 'require|regex:mobile',
    ];

    /**
     * 定义错误信息
     *
     * @var array
     */
    protected $message = [
        'id.require'         => '用户ID不能为空',
        'id.integer'         => '用户ID必须是整数',
        'username.require'   => '请输入用户名',
        'username.length'    => '用户名长度为3~20位',
        'username.alphaDash' => '用户名只能是字母、数字或下划线组成',
        'username.unique'    => '用户名重复',
        'password.require'   => '请输入密码',
        'password.length'    => '密码长度为6~16位',
        'password.checkPwd'  => '密码必须由大写字母、小写字母、数字组成',
        'rePassword.require' => '请输入确认密码',
        'rePassword.confirm' => '密码跟确认密码不一致',
        'mobile.require'     => '请输入手机号码',
        'mobile.regex'       => '请输入一个合法的手机号码',
        'newPwd.require'     => '请输入新密码',
        'newPwd.length'      => '新密码长度为6~16位',
        'newPwd.checkPwd'    => '新密码必须由大写字母、小写字母、数字组成',
        'rePwd.require'      => '请输入确认密码',
        'rePwd.confirm'      => '新密码跟确认密码不一致',
    ];

    // 场景验证
    protected $scene = [
        //用户添加场景验证
        'add'  => ['username', 'password', 'mobile'],
        //用户编辑场景验证
        'edit' => ['id', 'username', 'mobile'],
        //用户删除场景验证
        'del'  => ['id']
    ];

    // 自定义手机号码校验规则
    protected $regex = ['mobile' => '^1[3|4|5|6|7|8][0-9]\d{4,8}$'];

    /**
     * 自定义密码复杂度验证
     *
     * @param       $value
     * @param       $rule
     * @param array $data
     *
     * @return bool
     */
    protected function checkPwd($value, $rule, $data = []): bool
    {
        if ((preg_match('/[a-z]/', $value)) && (preg_match('/[0-9]/', $value)) && (preg_match('/[A-Z]/', $value))) {
            return true;
        }

        return false;
    }

    /**
     * 登录场景自定义
     *
     * @return User
     */
    public function sceneLogin(): User
    {
        // remove移除场景中的字段的部分验证规则
        // append给场景中的字段需要追加验证规则
        return $this->only(['username', 'password'])
            ->remove('username', 'length|unique')
            ->remove('password', 'length|checkPwd');
    }

    /**
     * 修改密码场景定义
     *
     * @return User
     */
    public function sceneEditPwd(): User
    {
        // remove移除场景中的字段的部分验证规则
        // append给场景中的字段需要追加验证规则
        return $this->only(['password', 'newPwd', 'rePwd'])
            ->remove('password', 'length|checkPwd')
            ->append('newPwd', 'require|length:6,16|checkPwd')
            ->append('rePwd', 'require|confirm:newPwd');
    }
}
