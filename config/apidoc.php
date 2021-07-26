<?php
return [
    // 文档标题
    'title'              => 'Wujiachen-Blog Api接口文档',
    // 文档描述
    'desc'               => '',
    // 版权申明
    'copyright'          => 'Powered 2021 By wujiachen.com.cn',
    // 默认作者
    'default_author'     => 'wujiachen',
    // 默认请求类型
    'default_method'     => 'GET',
    // 设置应用/版本（必须设置）
    'apps'               => [
        ['title' => '前台管理', 'path' => 'app\index\controller', 'folder' => 'v1']
    ],
    // 控制器分组
    'groups'             => [],
    // 指定公共注释定义的文件地址
    'definitions'        => "app\Definitions",
    //指定生成文档的控制器
    'controllers'        => [],
    // 过滤，不解析的控制器
    'filter_controllers' => [],
    // 缓存配置
    'cache'              => [
        // 是否开启缓存
        'enable' => false,
        // 缓存文件路径
        'path'   => '../runtime/apidoc/',
        // 最大缓存文件数
        'max'    => 5,  //最大缓存数量
    ],
    // 权限认证配置
    'auth'               => [
        // 是否启用密码验证
        'enable'     => false,
        // 验证密码
        'password'   => "123456",
        // 密码加密盐
        'secret_key' => "wujiachen260727#",
    ],
    // 统一的请求Header
    'headers'            => [],
    // 统一的请求参数Parameters
    'parameters'         => [],
    // 统一的请求响应体，仅显示在文档提示中
    'responses'          => [
        ['name' => 'code', 'desc' => '状态码', 'type' => 'int'],
        ['name' => 'message', 'desc' => '操作描述', 'type' => 'string'],
        ['name' => 'data', 'desc' => '业务数据', 'main' => true, 'type' => 'object'],
    ],
    // md文档
    'docs'               => [
        'menu_title' => '开发文档',
        'menus'      => []
    ],
    'crud'               => []

];
