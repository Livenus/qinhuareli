<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 维修管理
 * @type menu
 * @icon fa fa-th-list
 */
class Admin_repair  extends Admin_qhrlwx{
    
    protected $stats;
    public function _initialize(){
        $this->stats = [
                '1' => '派单中',
                '2' => '已派单',
                '3' => '待付款',
                '4' => '待评价',
                '5' => '已完成',
                ];
        parent::_initialize();
  
    }
    
    /**
     * @title 维修列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        $request = request();
        $stats = $this->stats;
        $db_repair = db('qhrlwx_repair');
        $all = $db_repair->field('community_id')->group('community_id')->select();
        $all = array_column($all, 'community_id');
        $coms_info = $this->get_community_list($all);

        if ($request::instance()->isAjax()){

            $p      = $request->param('p/d','1');
            $limit  = $request->param('limit/d','20');
            $offset = $request->param('offset/d','0');
            $order  = $request->param('order/s','asc');

            $where = [];
            $order_id     = $request->param('order_id/s','');
            $community_id = $request->param('community_id/d','');
            $stat         = $request->param('stat/d','');
            $time_s       = $request->param('time_s/s','');
            $time_e       = $request->param('time_e/s','');
            if(!empty($time_s)){
                $time_s = empty(strtotime($time_s)) ? '' : strtotime($time_s);
            }
            if(!empty($time_e)){
                $time_e = empty(strtotime($time_e)) ? '' : strtotime($time_e);
            }

            if($order_id > 0){
                $where['j.order_id'] = ['like',"%".$order_id."%"];
            }

            if($community_id > 0){
                $where['j.community_id'] = $community_id;
            }

            if($stat > 0){
                $where['j.stat'] = $stat;
            }

            if($time_s > 0 && $time_e > 0 && $time_s < $time_e){
                $where['j.create_time'] = ['between',[$time_s,$time_e]];
            }else{
                if($time_s > 0){
                    $where['j.create_time'] = ['gt',$time_s];
                }

                if($time_e > 0){
                    $where['j.create_time'] = ['lt',$time_e];
                }
            }

            $total = $db_repair->alias('j')->where($where)->join('qhrlwx_user a','j.user_id = a.id','LEFT')->count();
            $list = $db_repair->alias('j')->field('j.*,a.username as username,b.username as repairname')
                    ->join('qhrlwx_user a','j.user_id = a.id','LEFT')
                    ->join('qhrlwx_user b','j.repairman_id = b.id','LEFT')
                    ->where($where)
                    ->limit($offset,$limit)
                    ->order('id desc')->select();

            $house_infos = $this->_get_house_info(array_column($list,'house_id','house_id'));
            $house_infos = array_column($house_infos,'code','id');

            // file_put_contents('sos.txt', 'house_infos--'.json_encode($house_infos)."\r\n\r\n",FILE_APPEND);
                    
            foreach ($list as &$v) {
                $v['stat']        = $stats[$v['stat']] ? $stats[$v['stat']] : '--' ;
                $v['create_time'] = $v['create_time'] > 0 ? date('Y-m-d H:i:s',$v['create_time']) : '--' ;
                $v['pay_time']    = $v['pay_time'] > 0 ? date('Y-m-d H:i:s',$v['pay_time']) : '--' ;
                $v['house_id']    = $house_infos[$v['house_id']] ? $v['house_id'] .'--'. $house_infos[$v['house_id']] : $v['house_id'] ;
                if($coms_info){
                    $v['community_id']= $v['community_id'] > 0 ? $coms_info[$v['community_id']]['name'] : '--' ;
                }
                
            }
            return ['rows' => $list,'total' => $total];
        }
        $this->assign('stats',$stats);
        $this->assign('coms_info',$coms_info);
        return $this->fetch();
    }

    /**
     * @title 添加维修
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();

        if ($request::instance()->isAjax()){
            $username       = $request->param('username/s','');
            $house_id       = $request->param('house_id/s','');
            $community_id   = $request->param('community_id/s','');
            $repair_pic     = $request->param('repair_pic/a','');
            $repair_content = $request->param('repair_content/s','');

            if(empty($username)){
                $this->a_err('请输入用户名！');
            }

            if(empty($house_id)){
                $this->a_err('请输入报修房子！');
            }

            $info = db('qhrlwx_user')->alias('a')->field("b.house_id,a.id")->join('qhrlwx_relation_house b','a.id = b.user_id','LEFT')
            ->where('a.username',$username)->where('a.group_id','1')->where('b.house_id',$house_id)->find();
            if(empty($info)){
                $this->a_err('报修错误！');
            }

            if(empty($community_id)){
                $this->a_err('请输入小区！');
            }

            if(empty($repair_pic)){
                $this->a_err('请输入报修图片！');
            }else{
                $repair_pic = implode(",",$repair_pic);
            }

            if(empty($repair_content)){
                $this->a_err('请输入报修说明！');
            }
            $userid = $info['id'];
            $data = [];
            $data['user_id']        = $userid;
            $data['house_id']       = $house_id;
            $data['community_id']   = $community_id;
            $data['stat']           = 1;
            $data['create_time']    = time();
            $data['repair_pic']     = $repair_pic;
            $data['repair_content'] = $repair_content;
            
            $userid = strlen($userid) > 7 ? substr($userid, 0,7) : $userid;
            $data['order_id']       = 'QH'.sprintf("%07d",$userid) . date('YmdHis') . rand(1000,9999);

            $res = db('qhrlwx_repair')->insert($data);

            if($res !== false){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加错误！');
            }

        }
        return $this->fetch();
    }

    /**
     * @title 编辑维修
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $id = $request->param('id','');

        if ($request::instance()->isAjax()){

            $repairman_id  = $request->param('repairman_id/d','');
            $stat          = $request->param('stat/d','');
            $repair_grade  = $request->param('repair_grade/s','');
            $grade_content = $request->param('grade_content/s','');

            $rest = db('qhrlwx_repair')->where('id',$id)->update(
                        [
                            'repairman_id'  => $repairman_id,
                            'stat'          => $stat,
                            'repair_grade'  => $repair_grade,
                            'grade_content' => $grade_content,
                        ]
                    );

            if($rest !== false){
                $this->a_suc('修改成功！');
            }else{
                $this->a_err('修改错误！');
            }
        }else{
            $info = db('qhrlwx_repair')->alias('j')->field('j.*,a.username')
                    ->join('qhrlwx_user a','j.user_id = a.id','LEFT')
                    ->where('j.id',$id)
                    ->find();

            $community_id = $info['community_id'];    
            $house_infos = $this->_get_house_info([$info['house_id']]);
            $house_infos = array_column($house_infos,null,'id');
            
            if($house_infos){
                $info['community_id'] = $house_infos[$info['house_id']]['community_name'];
                $info['house_id'] = $info['house_id'] .'--'. $house_infos[$info['house_id']]['code'];
            }

            if($info['repair_pic']){
                $info['repair_pic'] = explode(',', $info['repair_pic']);;
            }

            $repairnames = db('qhrlwx_user')->field('id,username')->where('group_id','2')->where('repair_list','like','%/'.$community_id.'/%')->select();
            
            $this->assign('stats',$this->stats);
            $this->assign('info',$info);
            $this->assign('repairnames',$repairnames);

            return $this->fetch();
        }
        
    }

    /**
     * @title 删除维修
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrlwx_repair']);
    }

    /**
     * @title 用户房屋
     * @type menu
     * @menudisplay false
     */
    public function getHousesAc(){
        $request = request();
        $username = $request->param('username/s','');
        if(empty($username)){
            $this->a_err('请输入用户名！');
        }

        $db_user = db('qhrlwx_user');
        $info = $db_user->alias('a')->field("b.house_id")->join('qhrlwx_relation_house b','a.id = b.user_id','LEFT')
        ->where('a.username','like','%'.$username."%")->where('a.group_id','1')->select();
        if(!empty($info)){
            $house_infos = $this->_get_house_info(array_column($info,'house_id','house_id'));
            if(!empty($house_infos)){
                $this->a_suc('',$house_infos);
            }
        }
        

        $this->a_err('房屋不存在！');
    }

}
        