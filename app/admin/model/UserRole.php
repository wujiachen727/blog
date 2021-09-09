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
        $tableWhere = $this->tableWhere($data);
        $list = $this->field($tableWhere['field'])->alias('a')
            ->leftJoin('user b', 'a.create_id = b.id')
            ->where($tableWhere['where'])->order($tableWhere['order'])
            ->limit($tableWhere['offset'], $tableWhere['limit'])->select();
        $result['code'] = 0;
        $result['msg'] = '';
        $result['count'] = $this->alias('a')->leftJoin('user b', 'a.create_id = b.id')
            ->where($tableWhere['where'])->count();
        $result['data'] = $this->tableFormat($list);

        return $result;
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
            $where[] = ['a.name', 'like', '%' . $data['name'] . '%'];
        }

        $order = "a.update_time desc";
        if (isset($data['sort']) && $data['sort'] != "") {
            $order = "a." . $data['sort'] . " " . $data['order'];
        }

        if (isset($data['page']) && $data['page'] != "") {
            $page = (int)$data['page'];
        } else {
            $page = 1;
        }
        if (isset($data['limit']) && $data['limit'] != "") {
            $result['limit'] = (int)$data['limit'];
        } else {
            $result['limit'] = 20;
        }

        $result['where'] = $where;
        $result['field'] = "a.id,a.name,a.create_time,a.update_time,b.username as create_name";
        $result['order'] = $order;
        $result['offset'] = ($page - 1) * $result['limit'];

        return $result;
    }
}
