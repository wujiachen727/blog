<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use think\Model;

/**
 * @mixin Model
 */
class UserRole extends Common
{
    /**
     * 获取角色列表
     *
     * @param $data
     *
     * @return array
     */
    public function getRoleList($data): array
    {
        return $this->getTableDataList($data, $this->tableWhere($data));
    }

    /**
     * 根据输入的查询条件，返回所需要的where
     *
     * @param $data
     *
     * @return array
     */
    protected function tableWhere($data): array
    {
        $where = [];
        if (isset($data['name']) && $data['name'] != "") {
            $where[] = ['name', 'like', '%' . $data['name'] . '%'];
        }

        $order = "update_time desc";
        if (isset($data['sort']) && $data['sort'] != "") {
            $order = $data['sort'] . " " . $data['order'];
        }

        $page = (int)$data['page'] ?: 1;                  //默认第1页
        $result['limit'] = (int)$data['limit'] ?: 20;     //默认20条数据

        $result['where'] = $where;
        $result['field'] = "*";
        $result['order'] = $order;
        $result['offset'] = ($page - 1) * $result['limit'];

        return $result;
    }
}
