<?php

declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class Operation extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'parent_id'      => 'require|integer',
        'name'           => 'require|length:3,20',
        'code'           => 'require|length:3,20',
        'parent_menu_id' => 'require|integer',
        'type'           => 'require',
        'perm_type'      => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'parent_id.require'      => '父节点ID不能为空',
        'parent_id.integer'      => '父节点ID必须是整数',
        'name.require'           => '请输入名称',
        'name.length'            => '名称长度为3~20位',
        'code.require'           => '请输入编码',
        'code.length'            => '编码长度为3~20位',
        'parent_menu_id.require' => '菜单节点ID不能为空',
        'parent_menu_id.integer' => '菜单节点ID必须是整数',
        'type.require'           => '请输入类型',
        'perm_type.require'      => '请输入权限许可',
    ];
}
