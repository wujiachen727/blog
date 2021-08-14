<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use think\Model;

/**
 * 管理员操作日志模型
 *
 * @mixin Model
 */
class UserLog extends Common
{
    public const USER_LOGIN = 1;   //登录
    public const USER_LOGOUT = 2;  //退出
    public const USER_REGISTER = 3;//注册

    /**
     * 添加日志
     *
     * @param       $user_id
     * @param       $state
     * @param array $data
     */
    public function saveLog($user_id, $state, $data = [])
    {
        $data = [
            'user_id' => $user_id,
            'state'   => $state,
            'params'  => json_encode($data),
            'ip'      => get_client_ip()
        ];

        $this->save($data);
    }

    /**
     * 获取管理员日志列表
     *
     * @param $data
     *
     * @return array
     */
    public function getUserLogList($data): array
    {
        $tableWhere = $this->tableWhere($data);
        $list = $this->field($tableWhere['field'] . ',u.username,u.nickname,u.mobile')->alias('ul')
            ->leftjoin('user u', 'u.id = ul.user_id')
            ->where($tableWhere['where'])->order($tableWhere['order'])
            ->limit($tableWhere['offset'], $tableWhere['limit'])->select();
        $result['code'] = 0;
        $result['msg'] = '';
        $result['count'] = $this->alias('ul')->leftjoin('user u', 'u.id = ul.user_id')->where($tableWhere['where'])->count();
        $result['data'] = $this->tableFormat($list);

        return $result;
    }

    /**
     * 按天统计管理员的数据
     *
     * @param $day
     * @param $state
     *
     * @return array
     */
    public function getStatistics($day, $state): array
    {
        $where['state'] = $state;
        $field = 'state,DATE_FORMAT(from_unixtime(ctime),"%Y-%m-%d") as day, count(*) as nums';
        $res = $this->field($field)->where($where)
            ->where("TIMESTAMPDIFF(DAY,from_unixtime(ctime),now()) <7")
            ->group('DATE_FORMAT(from_unixtime(ctime),"%Y-%m-%d")')
            ->select();
        $data = get_lately_days($day, $res);

        return ['day' => $data['day'], 'data' => $data['data']];
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
        $where = [];
        if (isset($data['user_id']) && $data['user_id'] != "") {
            $where[] = ['ul.user_id', '=', $data['user_id']];
        }
        if (isset($data['id']) && $data['id'] != "") {
            $where[] = ['ul.id', '=', $data['id']];
        }
        if (!empty($data['date'])) {
            $date_string = $data['date'];
            $date_array = explode('-', $date_string);
            $sdate = strtotime($date_array[0] . ' 00:00:00');
            $edate = strtotime($date_array[1] . ' 23:59:59');
            $where[] = ['ul.create_time', 'between', [$sdate, $edate]];
        }

        $order = "ul.create_time desc";
        if (isset($data['sort']) && $data['sort'] != "") {
            $order = "ul." . $data['sort'] . " " . $data['order'];
        }

        $page = (int)$data['page'] ?: 1;                  //默认第1页
        $result['limit'] = (int)$data['limit'] ?: 20;     //默认20条数据

        $result['where'] = $where;
        $result['field'] = "ul.*";
        $result['order'] = $order;
        $result['offset'] = ($page - 1) * $result['limit'];

        return $result;
    }

    /**
     * 根据查询结果格式化数据
     *
     * @param $list
     *
     * @return mixed
     */
    protected function tableFormat($list)
    {
        foreach ($list as $k => $v) {
            $list[$k]['state'] = config('params.user')['state'][$v['state']];
        }

        return $list;
    }
}
