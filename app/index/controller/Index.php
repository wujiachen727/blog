<?php
declare (strict_types=1);

namespace app\index\controller;

use app\BaseController;
use think\response\View;

class Index extends BaseController
{
    public function index(): View
    {
        return view();
    }
}
