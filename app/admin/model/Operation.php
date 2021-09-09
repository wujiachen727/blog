<?php

declare(strict_types=1);

namespace app\admin\model;

use app\common\model\Common;
use Exception;
use think\Model;
use think\model\relation\HasOne;
use think\response\Json;
use think\route\Url;

/**
 * @mixin Model
 */
class Operation extends Common
{
    public const MENU_START = 1; //起始节点
    public const MENU_MANAGE = 2;//管理平台起始菜单id

    public const PERM_TYPE_SUB = 1;    //主体权限，在权限菜单、左侧菜单都体现（一般指父菜单，列表的查询方法）
    public const PERM_TYPE_HALFSUB = 2;//半主体权限，在权限菜单上体现，但是不在左侧菜单上体现（一般指增删改的方法）
    public const PERM_TYPE_REL = 3;    //附属权限，在权限菜单、左侧菜单都不体现（一般指子菜单，权限附属列表的查询方法）

    //不需要权限判断的控制器和方法
    private $noPerm = [
        self::MENU_MANAGE => [
            'Index' => ['index'],
            'Login' => ['index', 'login', 'logout'],
        ],
    ];

    public function parentInfo(): HasOne
    {
        return $this->hasOne('Operation', 'id', 'parent_id')->bind([
            'parent_name' => 'name'
        ]);
    }

    public function parentMenuInfo(): HasOne
    {
        return $this->hasOne('Operation', 'id', 'parent_menu_id')->bind([
            'parent_menu_name' => 'name'
        ]);
    }

    /**
     * 获取权限列表
     * //todo 后续根据前台需求做出变更
     *
     * @param $data
     *
     * @return array
     */
    public function getOperationList($data): array
    {
        try {
            $tableWhere = $this->tableWhere($data);
            $list = $this::with(['parentInfo', 'parentMenuInfo'])->field($tableWhere['field'])
                ->where($tableWhere['where'])->order($tableWhere['order'])
                ->limit($tableWhere['offset'], $tableWhere['limit'])->select();
            $list = $this->tableFormat($list);

            $result['code'] = 0;
            $result['msg'] = '';
            $result['count'] = $this::with(['parentInfo', 'parentMenuInfo'])->where($tableWhere['where'])->count();
            $result['data'] = $list;
            //取所有的父节点，构建路径
            if (isset($data['parent_id'])) {
                $result['parents'] = $this->getParents($data['parent_id']);
            } else {
                $result['parents'] = [];
            }
        } catch (Exception $e) {
            $result['code'] = 10008;
            $result['msg'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 根据传值获取where条件
     *
     * @param $data
     *
     * @return array
     */
    public function tableWhere($data): array
    {
        $where = [];
        if (isset($data['parent_id']) && $data['parent_id'] != "") {
            $where[] = ['parent_id', '=', $data['parent_id']];
        }

        $order = "id asc";
        if (isset($data['sort']) && $data['sort'] != "") {
            $order = $data['sort'] . " " . $data['order'];
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
        $result['field'] = "*";
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
            if (isset($v['type'])) {
                $list[$k]['type'] = config('params.operation.type')[$v['type']];
            }
            if (isset($v['perm_type']) && isset($v['parent_menu_id'])) {
                if ($v['parent_menu_id'] != '0') {
                    $list[$k]['perm_type'] = config('params.operation.perm_type')[$v['perm_type']];
                } else {
                    $list[$k]['perm_type'] = "";
                }
            }
        }

        return $list;
    }

    /**
     * 递归取得节点以及父菜单节点的信息(导航展示）
     *
     * @param int  $id
     * @param bool $recursion
     *
     * @return array|false
     */
    public function getNoteUrl(int $id = 0, bool $recursion = true)
    {
        $info = $this->where(['id' => $id])->findOrEmpty();
        if (empty($info)) {
            return false;
        }
        $result['name'] = $info['name'];
        switch ($info['type']) {
            case 'm':
                $result['url'] = url($info['code'] . "/index/index");
                break;
            case 'c':
                $result['url'] = url($info['code'] . "/index");
                break;
            case 'a':
                $result['url'] = "";
                break;
        }
        if ($recursion) {
            $pinfo = $this->where(['id' => $info['parent_id']])->findOrEmpty();
            if (!empty($pinfo)) {
                $pdata = $this->getNoteUrl($info['parent_menu_id']);
            }
            $pdata[] = $result;

            return $pdata;
        } else {
            return $result;
        }
    }

    /**
     * 递归获取所有的父节点
     *
     * @param int $operation_id
     *
     * @return array
     */
    public function getParents(int $operation_id = 0): array
    {
        $result = [];
        $info = $this->where(['id' => $operation_id])->findOrEmpty();
        if (empty($info)) {
            return $result;
        }
        //判断是否还有父节点，如果有，就取父节点，如果没有，就返回了
        if ($info['parent_id'] != self::MENU_START) {
            $result = $this->getParents($info['parent_id']);
        }
        array_push($result, $info->toArray());

        return $result;
    }

    /**
     * 获取后台菜单信息
     *
     * @param false $is_super
     * @param array $roles
     *
     * @return array
     */
    public function userMenu(bool $is_super = false, array $roles = []): array
    {
        //普通管理员，取所有的角色所对应的权限
        try {
            if ($is_super) {
                $list = $this->where(['perm_type' => self::PERM_TYPE_SUB])->order('sort asc')->select();
            } else {
                $list = $this->distinct(true)->field('0.*')->alias('o')
                    ->join(config('database.prefix') . 'user_role_operation_rel uror', 'o.id = uror.operation_id')
                    ->where('uror.user_role_id', 'IN', $roles)
                    ->where('o.perm_type', self::PERM_TYPE_SUB)
                    ->order('o.sort asc')
                    ->select();
            }

            if ($list->isEmpty()) {
                $list = [];
            } else {
                $list = $list->toArray();
            }
        } catch (Exception $e) {
            $list = [];
        }

        //创建菜单树并返回
        return $this->createTree($list, self::MENU_MANAGE, 'parent_menu_id', []);
    }

    /**
     * 依据传递数据构建节点菜单树
     *
     * @param       $list
     * @param       $parent_menu_id
     * @param       $p_str
     * @param array $onMenu
     * @param array $allOperation
     *
     * @return array
     */
    public function createTree($list, $parent_menu_id, $p_str, array $onMenu = [], array $allOperation = []): array
    {
        $result = [];
        try {
            //判断所有节点的值是否有，若无全部取出来
            if (!$allOperation) {
                $allOperation = $this->select();
                if ($allOperation->isEmpty()) {
                    $allOperation = [];
                } else {
                    $allOperation = $allOperation->toArray();
                }
                $nallOperation = [];
                foreach ($allOperation as $item) {
                    $nallOperation[$item['id']] = $item;
                }
                $allOperation = $nallOperation;
            }

            foreach ($list as $k => $v) {
                if ($v[$p_str] == $parent_menu_id) {
                    $row = $v;
                    //判断是否是选中状态
                    if (isset($onMenu[$v['id']])) {
                        $row['selected'] = true;
                    } else {
                        $row['selected'] = false;
                    }
                    //取当前节点url
                    $row['url'] = $this->getUrl($v['id'], $allOperation);

                    $row['children'] = $this->createTree($list, $v['id'], $p_str, $onMenu, $allOperation);

                    $result[] = $row;
                }
            }
        } catch (Exception $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * 根据当前节点，取当前节点对应url
     *
     * @param $operation_id
     * @param $list
     *
     * @return string|Url
     */
    private function getUrl($operation_id, $list)
    {
        if (!isset($list[$operation_id])) {
            return "";
        }
        if ($list[$operation_id]['type'] == 'm') {
            return url($list[$operation_id]['code'] . '/index/index');//正常情况下，模型基本无url情况
        }
        if ($list[$operation_id]['type'] == 'c') {
            if (isset($list[$list[$operation_id]['parent_id']])) {
                return url($list[$list[$operation_id]['parent_id']]['code'] . '/' .
                    $list[$operation_id]['code'] . '/index');
            } else {
                return "";
            }
        }
        if ($list[$operation_id]['type'] == 'a') {
            //取控制器
            if (
                isset($list[$list[$operation_id]['parent_id']]) &&
                isset($list[$list[$list[$operation_id]['parent_id']]['parent_id']])
            ) {
                return url($list[$list[$list[$operation_id]['parent_id']]['parent_id']]['code'] . '/' .
                    $list[$list[$operation_id]['parent_id']]['code'] . '/' . $list[$operation_id]['code']);
            } else {
                return "";
            }
        }

        return "";
    }

    /**
     * 获取操作名称
     *
     * @param string $ctl
     * @param string $act
     * @param int    $model_id
     *
     * @return array|Json
     */
    public function getOperationInfo(string $ctl = 'index', string $act = 'index', int $model_id = self::MENU_MANAGE)
    {
        $result = ['msg' => '', 'data' => '', 'status' => false];
        $where = ['type' => 'c', 'code' => $ctl, 'parent_id' => $model_id];
        $ctlInfo = $this->where($where)->findOrEmpty();
        if (empty($ctlInfo)) {
            return error_code(11088);
        }
        $where = ['type' => 'a', 'code' => $act, 'parent_id' => $ctlInfo['id']];
        $actInfo = $this->where($where)->findOrEmpty();
        if (empty($actInfo)) {
            return error_code(11089);
        }
        $result['status'] = true;
        $result['data'] = [
            'ctl' => $ctlInfo,
            'act' => $actInfo
        ];

        return $result;
    }

    /**
     * 递归取得节点下面的所有操作，按照菜单的展示来取
     *
     * @param       $pid
     * @param array $defaultNode
     * @param int   $level
     *
     * @return array
     */
    public function menuTree($pid, array $defaultNode = [], int $level = 1): array
    {
        try {
            $area_tree = [];
            $where = [
                ['parent_menu_id', '=', $pid],
                ['perm_type', '<>', self::PERM_TYPE_REL]//不是附属权限的
            ];
            $list = $this->where($where)->order('sort asc')->select()->toArray();
            foreach ($list as $k => $v) {
                $isChecked = 0;
                //判断是否选中
                if (isset($defaultNode[$v['id']])) {
                    $isChecked = 1;
                }

                $isLast = false;
                unset($where);
                $where = [
                    ['parent_menu_id', '=', $v['id']],
                    ['perm_type', '<>', self::PERM_TYPE_REL]//不是附属权限的
                ];
                $childCount = $this->where($where)->count();
                if (!$childCount) {
                    $isLast = true;
                }

                $area_tree[$k] = [
                    'id'       => $v['id'],
                    'title'    => $v['name'],
                    'isLast'   => $isLast,
                    'level'    => $level,
                    'parentId' => $v['parent_id'],
                    "checkArr" => [
                        'type'      => '0',
                        'isChecked' => $isChecked,
                    ]
                ];
                if ($childCount) {
                    $level = $level + 1;
                    $area_tree[$k]['children'] = $this->menuTree($v['id'], $defaultNode, $level);
                }
            }
        } catch (Exception $e) {
            $area_tree = [];
        }

        return $area_tree;
    }

    /**
     * 判断控制器和方法是否不需要校验
     *
     * @param $p_id
     * @param $cont_name
     * @param $act_name
     *
     * @return bool
     */
    public function checkNeedPerm($p_id, $cont_name, $act_name): bool
    {
        if (isset($this->noPerm[$p_id][$cont_name])) {
            if (in_array(strtolower($act_name), $this->noPerm[$p_id][$cont_name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 预先判断死循环
     *
     * @param     $id
     * @param     $p_id
     * @param     $p_str
     * @param int $n
     *
     * @return bool
     */
    public function checkDie($id, $p_id, $p_str, int $n = 10): bool
    {
        //设置计数器，防止极端情况下陷入死循环了（其他地方如果设置的有问题死循环的话，这里就报错了）
        if ($n <= 0) {
            return false;
        }
        if ($id == $p_id) {
            return false;
        }
        if ($id == self::MENU_START || $p_id == self::MENU_START) {
            return true;
        }
        $pinfo = $this->where(['id' => $p_id])->findOrEmpty();
        if (empty($pinfo)) {
            return false;
        }
        if ($pinfo[$p_str] == self::MENU_START) {
            return true;
        }
        if ($pinfo[$p_str] == $id) {
            return false;
        }

        return $this->checkDie($id, $pinfo[$p_str], $p_str, --$n);
    }
}
