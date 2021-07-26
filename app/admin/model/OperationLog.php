<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use think\Model;

/**
 * 后台操作日志模型
 *
 * @mixin Model
 */
class OperationLog extends Common
{
    /**
     * where搜索条件
     *
     * @param $data
     *
     * @return array
     */
    protected function tableWhere($data): array
    {
        $where = [];

        if (!empty($data['date'])) {
            $date_string = $data['date'];
            $date_array = explode('-', $date_string);
            $start_date = strtotime($date_array[0] . ' 00:00:00');
            $end_date = strtotime($date_array[1] . ' 23:59:59');
            $where[] = ['create_time', 'between', [$start_date, $end_date]];
        }

        if (isset($data['id']) && $data['id'] != '') {
            $where[] = ['id', 'in', $data['id']];
        }
        $result['where'] = $where;
        $result['field'] = "*";
        $result['order'] = "id desc";

        return $result;
    }
}
