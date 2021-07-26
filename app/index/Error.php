<?php
declare (strict_types=1);

namespace app\index;

/**
 * 控制器不存在
 *
 * @package app\index
 */
class Error
{
    public function __call($name, $arguments)
    {
        return show(10000);
    }
}