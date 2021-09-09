<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\validate\Operation as OperationValidate;
use app\admin\service\Operation as OperationService;
use think\Response;
use think\response\Json;
use think\response\View;

class Operation extends Admin
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
     * 获取节点列表
     *
     * @return Json
     */
    public function getOperationList(): Json
    {
        $data = $this->request->get();
        $result = (new OperationService())->getOperationList($data);

        return show($result);
    }

    /**
     * 保存新建的资源
     *
     * @return Response
     */
    public function save(): Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $operationValidate = new OperationValidate();
            if (!$operationValidate->check($data)) {
                return error_code(10001, $operationValidate->getError());
            }

            $operationService = new OperationService();
            $result = $operationService->add($data);

            return show($result);
        } else {
            return error_code(100);
        }
    }

    /**
     * 删除指定资源
     *
     * @return Response
     */
    public function delete(): Response
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');

            if ($id <= 0) {
                return error_code(10003);
            }

            $operationService = new OperationService();
            $result = $operationService->del($id);

            return show($result);
        } else {
            return error_code(100);
        }
    }
}
