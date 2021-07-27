<?php

// 应用公共文件
use think\response\Json;

/**
 * 通用返回错误码，同时刷新token
 *
 * @param int    $code
 * @param string $msg
 *
 * @return Json
 */
function error_code($code = 10000, $msg = ""): Json
{
    //如果消息提示为空，并且业务状态码定义了，那么就显示默认定义的消息提示
    if (empty($msg) && !empty(config("status." . $code))) {
        $msg = config("status." . $code);
    }

    $result = [
        'code'  => $code,
        'msg'   => $msg,
        'token' => request()->buildToken('__token__', 'sha1')//每次请求token都不同，防止重复提交
    ];

    return json($result);
}

/**
 * 通用返回数据
 *
 * @param $result
 *
 * @return Json
 */
function show($result): Json
{
    //如果消息提示为空，并且业务状态码定义了，那么就显示默认定义的消息提示
    if (isset($result['msg']) && empty($result['msg']) && !empty(config("status." . $result['code']))) {
        $result['msg'] = config("status." . $result['code']);
    }

    if ($result['code'] != 0) {
        return error_code($result['code'], $result['msg']);
    }

    return json($result);
}

/**
 * 生成令牌
 * @return string
 */
function buildToken(): string
{
    $data = request()->buildToken('__token__', 'sha1');

    return '<input type="hidden" name="__token__" value="' . $data . '" class="token">';
}

/**
 * 获取客户端IP地址
 *
 * @return mixed
 */
function get_client_ip(): string
{
    $forwarded = request()->header("x-forwarded-for");
    if ($forwarded) {
        $ip = explode(',', $forwarded)[0];
    } else {
        $ip = request()->ip();
    }

    return $ip;
}

/**
 * 获取最近天数的日期和数据
 *
 * @param $day
 * @param $data
 *
 * @return array[]
 */
function get_lately_days($day, $data): array
{
    $day = $day - 1;
    $days = [];
    $d = [];
    for ($i = $day; $i >= 0; $i--) {
        $d[] = date('d', strtotime('-' . $i . ' day')) . '日';
        $days[date('Y-m-d', strtotime('-' . $i . ' day'))] = 0;
    }
    foreach ($data as $v) {
        $days[$v['day']] = $v['nums'];
    }
    $new = [];
    foreach ($days as $v) {
        $new[] = $v;
    }

    return ['day' => $d, 'data' => $new];
}
