<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * 后台管理员模型
 *
 * @mixin Model
 */
class User extends Common
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    //设置软删除字段的默认值
    protected $defaultSoftDelete = 0;

    public const TYPE_SUPER_ID = 1; //超级管理员 id
    public const STATUS_NORMAL = 1; //用户状态 正常
    public const STATUS_DISABLE = 2;//用户状态 停用

    /**
     * 获取管理员列表
     *
     * @param $data
     *
     * @return array
     */
    public function getUserList($data): array
    {
        $tableWhere = $this->tableWhere($data);
        $list = $this->field($tableWhere['field'] . ',group_concat(ur.name) as role_name')->alias('u')
            ->leftJoin('user_role_rel urr', 'urr.user_id = u.id')
            ->leftJoin('user_role ur', 'ur.id = urr.role_id')
            ->group("u.id")->where($tableWhere['where'])->order($tableWhere['order'])
            ->limit($tableWhere['offset'], $tableWhere['limit'])->select();
        $result['code'] = 0;
        $result['msg'] = '';
        $result['count'] = $this->leftJoin('user_role_rel urr', 'urr.user_id = u.id')
            ->leftJoin('user_role ur', 'ur.id = urr.role_id')
            ->where($tableWhere['where'])->count();
        $result['data'] = $this->tableFormat($list);

        return $result;
    }

    /**
     * where 搜索条件
     *
     * @param $data
     *
     * @return array
     */
    protected function tableWhere($data): array
    {
        $where = ['u.id', '<>', $this::TYPE_SUPER_ID];
        if (isset($data['username']) && $data['username'] != "") {
            $where[] = ['u.username', 'like', '%' . $data['username'] . '%'];
        }
        if (isset($data['name']) && $data['name'] != "") {
            $where[] = ['u.name', 'like', '%' . $data['name'] . '%'];
        }
        if (isset($data['mobile']) && $data['mobile'] != "") {
            $where[] = ['u.mobile', 'like', '%' . $data['mobile'] . '%'];
        }
        if (isset($data['status']) && $data['status'] != "") {
            $where[] = ['u.status', '=', $data['mobile']];
        }

        $order = "u.id asc";
        if (isset($data['sort']) && $data['sort'] != "") {
            $order = "u." . $data['sort'] . " " . $data['order'];
        }

        $page = (int)$data['page'] ?: 1;                  //默认第1页
        $result['limit'] = (int)$data['limit'] ?: 20;     //默认20条数据

        $result['where'] = $where;
        $result['field'] = "u.id,u.username,u.mobile,u.avatar,u.nickname,u.status,u.create_time,u.update_time";
        $result['order'] = $order;
        $result['offset'] = ($page - 1) * $result['limit'];

        return $result;
    }
}
