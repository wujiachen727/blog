<?php

declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\OperationLog as OperationLogModel;
use Exception;

class OperationLog
{
    /**
     * 查询操作日志
     *
     * @param $data
     *
     * @return array
     */
    public function getOperationLogList($data): array
    {
        return (new OperationLogModel())->getTableDataList($data);
    }

    /**
     * 删除操作日志
     *
     * @param $ids
     *
     * @return array
     */
    public function del($ids): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            OperationLogModel::destroy($ids);
            $result['code'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 添加操作日志
     *
     * @param $data
     *
     * @return array
     */
    public function add($data): array
    {
        $result = ['code' => 10000, 'msg' => ''];
        try {
            OperationLogModel::create($data);
            $result['code'] = 0;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }
}
