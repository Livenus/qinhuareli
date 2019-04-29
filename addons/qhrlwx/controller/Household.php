<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 房屋信息
 * @type menu
 * @menudisplay false
 *
 */
class Household extends Home{

    /**
     * @title添加房屋
     * @type interface
     * @login 1
     * @param true_name  真实姓名  1  1 
     * @param community_id  小区id  1   1 
     * @param mobile   手机号   1   1  手机号有格式验证
     * @param building  楼号  1   1   楼号必须为2位
     * @param unit  单元号  1   1   单元号必须为2位
     * @param room   房号  1   1   房号必须为4位
     */
    public function addAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $data = $this->_get_house_param();

        //请求远程数据
        $url  =  '/n/qhrl/f/add_house';
        $r = $this->get_far_data($data,$url);
        
        if($r['stat'] == -1){
            $this->err('房屋添加失败！');
        }else{
            $da = [];
            $da['user_id'] = $userid;
            $da['house_id'] = $r['data'];

            $db_relation_house = db('qhrlwx_relation_house');
            $re = $db_relation_house->where($da)->count();
            
            if($re > 0){
                $this->err('房屋已经存在！');
            }else{
                $da['create_time'] = time();
                $da['stat']        = 0;
                $rest =$db_relation_house->insert($da);

                if($rest === false){
                    $this->err('添加失败！');
                }else{
                    $this->suc('添加成功！');
                }
            }
        }

    }

    /**
     * @title编辑房屋
     * @type interface
     * @login 1
     * @param house_id  房间id  1  1 
     * @param true_name  真实姓名  1  1 
     * @param community_id  小区id  1   1 
     * @param mobile   手机号   1   1  手机号有格式验证
     * @param building  楼号  1   1   楼号必须为2位
     * @param unit  单元号  1   1   单元号必须为2位
     * @param room   房号  1   1   房号必须为4位
     */
    public function editAc(){
        // $user = $this->getLoginUserinfo();
        $userid = 9;
        $request = request();
        $house_id = $request->post('house_id/d',''); 
        if(empty($house_id)){
            $this->err('编辑错误！');
        }

        $da = [];
        $da['user_id'] = $userid;
        $da['house_id'] = $house_id;

        $db_relation_house = db('qhrlwx_relation_house');
        $re = $db_relation_house->field('id')->where($da)->find();

        if(count($re) != 1){
            $this->err('信息不存在！');
        }
        
        $data = $this->_get_house_param();

        //请求远程数据
        $url = '/n/qhrl/f/add_house';
        $r = $this->get_far_data($data,$url);
        
        if($r['stat'] == -1){
            $this->err('修改失败！');
        }else{

            if(!is_numeric($r['data']) || $r['data'] <= 0){
                $this->err('修改错误！');
            }
            //修改前后房屋编号无编号
            if($house_id == $r['data']){
                $this->suc('修改成功！');

            //修改后有新编号    
            }else{
                $da['house_id'] = $r['data'];
                $count = $db_relation_house->where($da)->count();
                if($count > 0){
                    $this->err('房屋已存在！');
                }

                $rest = $db_relation_house->where('id',$re['id'])->update(['house_id' => $r['data']]);

                if($rest !== false){
                    $this->suc('修改成功！');
                }else{
                    $this->err('修改失败！');
                }
            }
            
        }

    }

    /**
     * @title获取传入房屋信息
     * @type menu
     * @login 1
     * @menudisplay false
     */
    private function _get_house_param(){
        $request = request();
        $data = [];
        $data['true_name']    = $request->post('true_name/s',''); 
        $data['community_id'] = $request->post('community_id/s','');
        $data['mobile']       = $request->post('mobile/s','');
        $data['building']     = $request->post('building/s',''); 
        $data['unit']         = $request->post('unit/s','');
        $data['room']         = $request->post('room/s','');

        if(empty($data['true_name'])){
            $this->err('姓名不能为空！');
        }

        if(empty($data['community_id'])){
            $this->err('小区不能为空！');
        }
        $data['community_id'] = strlen($data['community_id']) < 3 ? sprintf("%03d",$data['community_id']) : $data['community_id'];

        if(!$this->check_mobile($data['mobile'])){
            $this->err('请输入正确的手机号！');
        }

        $data['building'] = strlen($data['building']) == 1 ? sprintf("%02d",$data['building']) : $data['building'];
        if(preg_match('/\d{2}/',$data['building']) != 1){
            $this->err('请输入正确的楼号！');
        }

        $data['unit'] = strlen($data['unit']) == 1 ? sprintf("%02d",$data['unit']) : $data['unit'];
        if(preg_match('/\d{2}/',$data['unit']) != 1){
            $this->err('请输入正确的单元号！');
        }

        if(preg_match('/\d{4}/',$data['room']) != 1){
            $this->err('请输入正确的房间号！');
        }

        $data['code'] = substr($data['community_id'], 0,3) . $data['building'] . $data['unit'] . $data['room'];
        if(preg_match('/\d{11}/',$data['code']) != 1){
            $this->err('户代码生成错误！');
        }

        return $data;
    }


    /**
     * @title请求房屋信息
     * @type interface
     * @login 1
     * @param type  查询类型  1  1  1是列表2是详情要带id
     * @param id  房屋id  1  0  查询详情需要
     * @return total  查询到的信息条数 1 1 返回0时不传data参数
     * @return data   房屋信息列表  array 0  
     */
    public function get_house_infoAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $id   = $request->post('id/s','');
        $type = $request->post('type/s','1');
        if($type != 1 && $type != 2){
            $this->err('参数错误！');
        }

        $list = db('qhrlwx_relation_house')->where('user_id',$userid)->select();
        if(count($list) == 0){
            $this->suc(['total' => 0]);
        }

        $list = array_column($list,'house_id','house_id');
        if($type == 2){
            if(empty($id) || empty($list[$id])){
                $this->err('房屋不存在！');
            }
            $ids = [$id];
        }else{
            $ids = $list;
        }

        $r = $this->_get_house_info($ids);
        if(!$r){
            $this->suc(['total' => 0]);
        }else{
            $this->suc([
                'total' => count($r),
                'house_info' => $r
            ]);
        }
    }

    /**
     * @title 删除房屋
     * @type interface
     * @login 1
     * @param house_id  房屋id  1   1
     */
    public function deleteAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $house_id   = $request->post('house_id/s','');

        $where['user_id']  = $userid;
        $where['house_id'] = $house_id;
        $db_relation_house = db('qhrlwx_relation_house');
        $data = $db_relation_house->where($where)->find();

        if(empty($data)){
            $this->err('信息不存在！');
        }

        $rest = $db_relation_house->where('id',$data['id'])->delete();
        if($rest !== false){
            $this->suc('删除成功！');
        }else{
            $this->err('删除成功！');
        }
    }
}