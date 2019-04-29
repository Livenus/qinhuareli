<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 维修师傅
 * @type menu
 * @menudisplay false
 *
 */
class Repairman extends Home{

    protected $stats;
    public function _initialize(){
        $this->stats = [
                '-1'=> '全部',
                '1' => '派单中',
                '2' => '已派单',
                '3' => '待付款',
                '4' => '待评价',
                '5' => '已完成',
                ];
        parent::_initialize();
  
    }

    /**
     * @title 师傅维修列表
     * @type interface
     * @login 1
     * @param stat  维修订单状态  1  0  -1和1到5
     * @param page  页数  1  0 
     * @return total   师傅维修总的报修数据  1  1   返回0时没有其他参数
     * @return total_page  总页数   1  0
     * @return page  当前页   1   0
     * @return data  返回数据  array  0
     */
    public function get_listAc(){
        // $user = $this->getLoginUserinfo();
        $userid = 9;
        $request = request();
        $stats = $this->stats;
        $stat    = $request->post('stat/s','');
        $size    = 10;
        $page    = $request->post('page/d','1');

        if(!$stats[$stat]){
            $this->err('参数错误！');
        }

        $db_repair = db('qhrlwx_repair');
        $where['a.repairman_id'] = $userid;
        if($stat != '-1'){
            $where['a.stat'] = $stat;
        }

        $count = $db_repair->alias("a")->join("qhrlwx_user b","a.user_id = b.id","left")->where($where)->count();
        if(empty($count)){
            $this->suc(['total' => 0]);
        }

        $total_page = ceil($count/$size);
        $page = $page > $total_page ? $total_page : $page;
        $start = (($page - 1) < 0 ? 0 : $page - 1) * $size;
        $field = 'a.id,a.order_id,a.user_id,a.house_id,a.stat,a.community_id,a.create_time,a.repairman_id,a.repair_money,a.pay_time,b.username,b.mobile';
        $list = $db_repair->alias("a")->field($field)
                ->join("qhrlwx_user b","a.user_id = b.id","left")
                ->where($where)
                ->order('create_time desc')
                ->limit($start,$size)
                ->select();
        $users = array_column($list,'house_id','house_id');
        foreach ($list as &$v) {
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }

        $return = [
            'total'      => $count,
            'total_page' => $total_page,
            'page'       => $page,
            'data'       => $list
        ];

        $this->suc($users);

    }


    /**
     * @title 师傅维修单条信息
     * @type interface
     * @login 1
     * @param id  报修id
     * @return house_info  房屋信息  1  0  单条信息时返回
     */
    public function repair_detailAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $db_repair = db('qhrlwx_repair');
        $id = $request->post('id/s','');

        if(empty($id) || $id <= 0){
            $this->err('参数错误！');
        }

        $where['id']            = $id;
        $where['repairman_id']  = $userid;
        $info = $db_repair->where($where)->find($id);
        if(empty($info) || empty($info['house_id'])){
            $this->err('订单错误！');
        }
        $r = $this->_get_house_info([$info['house_id']]);
        if($r !== false){
            $info['house_info'] = $r[0];
        }else{
            $info['house_info'] = [];
        }

        $this->suc($info);
    }


    /**
     * @title 师傅是否接单
     * @type interface
     * @login 1
     * @param id
     * @param stat 接单选择 1  1  -1拒绝1同意
     * @param refuse_reason  拒绝说明  1  0  拒绝时填写
     */
    public function repair_chooseAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $db_repair = db('qhrlwx_repair');
        $id            = $request->post('id/s','');
        $stat          = $request->post('stat/s','1');
        $refuse_reason = $request->post('refuse_reason/s','');

        if(empty($id) || $id <= 0){
            $this->err('参数错误！');
        }

        $where['id']            = $id;
        $where['repairman_id']  = $userid;
        $where['stat']          = ['eq',1];
        $info = $db_repair->where($where)->find();
        if(empty($info)){
            $this->err('订单错误！');
        }

        if($stat == -1 && empty($refuse_reason)){
            $this->err('请填写拒绝理由！');
        }

        $data = [];
        if($stat == -1){
            $data['refuse_reason'] = $info['refuse_reason'].'/维修'.$info['repairman_id'].':'.$refuse_reason;
            $data['repairman_id']  = 0;
        }else{
            $data['stat'] = 2;
        }

        $res = $db_repair->where('id',$id)->update($data);

        if($res !== false){
            $this->suc('操作成功！');
        }else{
            $this->err('操作失败！');
        }
    }


    /**
     * @title 师傅填写付款金额
     * @type interface
     * @login 1
     * @param id  维修id  1  1  
     * @param repair_money   维修金额  1  1  两位小数
     */
    public function repair_moneyAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $db_repair = db('qhrlwx_repair');
        $id           = $request->post('id/s','');
        $repair_money = $request->post('repair_money/s','');

        if(empty($id) || $id <= 0){
            $this->err('参数错误！');
        }

        $where['id']            = $id;
        $where['repairman_id']  = $userid;
        $where['stat']          = ['eq',2];
        $info = $db_repair->where($where)->find();
        if(empty($info)){
            $this->err('订单错误！');
        }

        if(!is_numeric($repair_money) || $repair_money <= 0){
            $this->err('金额错误！');
        }
        $repair_money = round($repair_money,2);

        $res = $db_repair->where('id',$id)->update(
                [
                    'repair_money'  => $repair_money,
                    'stat'          => 3,
                ]
            );

        if($res !== false){
            $this->suc('操作成功！');
        }else{
            $this->err('操作失败！');
        }
    }
}