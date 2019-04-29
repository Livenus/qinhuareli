<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 报修
 * @type menu
 * @menudisplay false
 *
 */
class Repair extends Home{

    protected $grades;
    public function _initialize(){
        $this->grades = [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                ];
        parent::_initialize();
  
    }

    /**
     * @title添加报修
     * @type interface
     * @login 1
     * @param house_id  报修房屋id  1  1 
     * @param repair_pic   报修图片  array   1
     * @param repair_content   报修说明  1   1
     */
    public function addAc(){
        
        // $user = $this->getLoginUserinfo();
        $userid = 9;
        $request = request();
        //用户房屋编号
        $house_id       = $request->post('house_id/s',''); 
        $repair_pic     = $request->post('repair_pic/a','');
        $repair_content = $request->post('repair_content/s','');

        if(empty($house_id)){
            $this->err('报修错误！');
        }

        if(empty($repair_pic) || empty(implode(',',$repair_pic))){
            $this->err('报修图片不能为空！');
        }

        if(empty($repair_content)){
            $this->err('报修说明不能为空！');
        }

        $where['user_id']  = $userid;
        $where['house_id'] = $house_id;
        //检查报修地址是否关联用户
        $count = db('qhrlwx_relation_house')->where($where)->count();
        if(empty($count)){
            $this->err('报修错误！');
        }

        $r = $this->_get_house_info([$house_id]);
        
        if(count($r) != 1){
            $this->err('报修失败！');
        }

        $data = [];
        $data['user_id']        = $userid;
        $data['house_id']       = $house_id;
        $data['community_id']   = $r[0]['community_id'];
        $data['stat']           = 1;
        $data['create_time']    = time();
        $data['repair_pic']     = implode(',',$repair_pic);
        $data['repair_content'] = $repair_content;

        $userid = strlen($userid) > 7 ? substr($userid, 0,7) : $userid;
        $data['order_id']       = 'QH'.sprintf("%07d",$userid) . date('YmdHis') . rand(1000,9999);

        $res = db('qhrlwx_repair')->insert($data);

        if($res !== false){
            $this->suc('报修成功！');
        }else{
            $this->err('提交失败！');
        }
    }

    /**
     * @title 报修查询
     * @type interface
     * @login 1
     * @param repair_id  报修id  1  0  不传表示查询列表
     * @param page  分页  1  0  
     * @param stat  订单状态  1  0  不传查全部状态1到5
     * @return house_info  房屋信息  1  0  单条信息时返回
     * @return total   用户总的报修数据  1  1   返回0时没有其他参数
     * @return total_page  总页数   1  0
     * @return page  当前页   1   0
     * @return data  返回数据  array  0  repairman_info返回维修师傅用户名和手机号
     */
    public function get_infoAc(){
        // $user = $this->getLoginUserinfo();
        $userid = 9;
        $request = request();
        $size = 10;
        //用户房屋编号
        $id   = $request->post('repair_id/d',''); 
        $page = $request->post('page/d','1');
        $stat = $request->post('stat/d','0');
        
        $db_repair = db('qhrlwx_repair');
        $where['user_id']  = $userid;
        
        //获取单条信息
        if($id > 0){
            
            $where['id'] = $id;
            $res = $db_repair->where($where)->find();

            if(!empty($res)){
                //查询报修房间的的信息
                $r = $this->_get_house_info([$res['house_id']]);
                if(!$r){
                    $house_info = [];
                }else{
                    $house_info = $r[0];
                }
                $res['house_info'] = $house_info;

                $this->suc($res);
            }else{
                $this->err('获取失败！');
            }

        //查询列表
        }else{
            if(!empty($stat)){
                $where['stat']  = $stat;
            }
            $count = $db_repair->where($where)->count();
            if($count == 0){
                $this->suc(['total' => 0]);
            }

            $total_page = ceil($count/$size);
            $page = $page > $total_page ? $total_page : $page;
            $start = (($page - 1) < 0 ? 0 : $page - 1) * $size;
            $field = 'id,user_id,house_id,stat,community_id,create_time,repairman_id,repair_money,pay_time,order_id';
            $list = $db_repair->field($field)->where($where)->order('create_time desc')->limit($start,$size)->select();
            
            $repairmans = array_flip(array_column($list,'repairman_id','repairman_id'));
            if(count($repairmans) > 0){
                $man_list = db('qhrlwx_user')->field('id,username,mobile')->where('group_id',2)->where('id','in',$repairmans)->select();
                $man_list = array_column($man_list,null,'id');
                foreach ($list as &$value) {
                    $value['repairman_info'] = isset($man_list[$value['repairman_id']]) ? $man_list[$value['repairman_id']] : '';
                }
            }
            
            $return = [
                'total'      => $count,
                'total_page' => $total_page,
                'page'       => $page,
                'data'       => $list
            ];

            $this->suc($return  );
        }
    }

    /**
     * @title 评价报修
     * @type interface
     * @login 1
     * @param id    报修id  1  1  
     * @param repair_grade   报修评级  1   1  1到5星
     * @param grade_content   评级内容  1   0   不是5星必须添加内容
     */
    public function user_gradeAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $grades = $this->grades;
        $id            = $request->post('id/s',''); 
        $repair_grade  = $request->post('repair_grade/s',''); 
        $grade_content = $request->post('grade_content/s','');
        $db_repair = db('qhrlwx_repair');

        if(empty($id) || $id <= 0){
            $this->err('参数错误！');
        }

        if(!in_array($repair_grade,$grades)){
            $this->err('评级错误！');
        }

        $where['id']     = $id;
        $where['user_id'] = $userid;
        $where['stat']   = ['eq',4];
        $info = $db_repair->where($where)->find();
        if(empty($info)){
            $this->err('订单错误！');
        }

        if($repair_grade != 5 && empty($grade_content)){
            $this->err('请填写评级内容！');
        }

        $res = $db_repair->where('id',$id)->update(
                [
                    'repair_grade'  => $repair_grade,
                    'grade_content' => $grade_content,
                    'stat'          => 5,
                ]
            );

        if($res !== false){
            $this->suc('操作成功！');
        }else{
            $this->err('操作失败！');
        }
    }
}