<?php

declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class UserRole extends Validate
{
    /**
     * 定义验证规则
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require|length:3,20|unique:user_role'
    ];

    /**
     * 定义错误信息
     *
     * @var array
     */
    protected $message = [
        'name.require' => '请输入角色名称',
        'name.length'  => '角色名称长度为3~20位',
        'name.unique'  => '角色名称不能重复'
    ];

    // 场景验证
    protected $scene = [
        // 角色添加场景验证
        'add' => ['name'],
        // 角色编辑场景验证
        'edit' => ['id', 'name'],
    ];
}
