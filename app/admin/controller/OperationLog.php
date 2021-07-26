<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\service\OperationLog as OperationLogService;
use think\response\Json;
use think\response\View;

class OperationLog extends Admin
{
    /**
     * 显示资源列表
     *
     * @return View
     */
    public function index(): View
    {
        return view();
    }

    /**
     * 获取操作日志列表
     *
     * @return Json
     */
    public function getOperationLogList(): Json
    {
        $data = $this->request->get();
        $result = (new OperationLogService())->getOperationLogList($data);

        return show($result);
    }

    /**
     * 删除操作日志
     *
     * @return Json
     */
    public function delete(): Json
    {
        if ($this->request->isPost()) {
            $ids = $this->request->post('ids');
            if (empty($ids)) {
                return error_code(10003);
            }

            $operationLogService = new OperationLogService();

            $result = $operationLogService->del($ids);

            return show($result);
        } else {
            return error_code(100);
        }
    }
}
