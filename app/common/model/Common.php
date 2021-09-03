<?php

declare(strict_types=1);

namespace app\common\model;

use Exception;
use think\Model;

/**
 * 公用模型
 *
 * @mixin Model
 */
class Common extends Model
{
    /**
     * 获取数据列表
     *
     * @param $data
     * @param array $where
     *
     * @return array
     */
    public function getTableDataList($data, array $where = []): array
    {
        try {
            $tableWhere = $where ?: $this->tableWhere($data);
            $list = $this->field($tableWhere['field'])->where($tableWhere['where'])->order($tableWhere['order'])
                ->limit($tableWhere['offset'], $tableWhere['limit'])->select();

            $re['code'] = 0;
            $re['msg'] = '数据加载成功';
            $re['count'] = $this->where($tableWhere['where'])->count();
            $re['data'] = $this->tableFormat($list);
        } catch (Exception $e) {
            $re['code'] = 10008;
        }

        return $re;
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
        $result['where'] = [];
        $result['field'] = "*";
        $result['order'] = [];

        return $result;
    }

    /**
     * 根据查询结果，格式化数据
     *
     * @param $list
     *
     * @return mixed
     */
    protected function tableFormat($list)
    {
        return $list;
    }
}
