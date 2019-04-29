<?php
namespace addons\qhrl\controller;

/**
 * @title 房屋管理
 * @type menu
 *
 */

class Admin_household extends Admin_qhrl{
    
    protected $types;
    protected $stats;
    protected $sell_outs;
    public function _initialize(){

        $this->types = [
                '0' => '商业',
                '1' => '住宅',
                '2' => '其它',
                ];
        $this->stats = [
                '0' => '失效',
                '1' => '有效',
                ];
        $this->sell_outs = [
                '0' => '未售',
                '1' => '已售',
                ];
        parent::_initialize();
        $this->assign('types',$this->types);
        $this->assign('stats',$this->stats);
        $this->assign('sell_outs',$this->sell_outs);
    }
    /**
     * @title 房屋列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        return j_help()->handleList([
            'topbuttons'=>'qhrl/Admin_household/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'qhrl/Admin_household/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id
             $qhrl/Admin_household/del#删除#glyphicon glyphicon-edit#btn btn-warning#del#id',
            'search'    => 'a.name$j.code$j.householder$j.tel',
            'query'     => [
                'db_table' => 'qhrl_household',
                'db_join'  => '__QHRL_COMMUNITY__ a#a.id=j.community_id#left$__ADMIN_ADMIN__ b#b.id=j.update_id#left',
                'db_fields' => 'j.id$j.unit$j.building$j.room$j.code$j.area$j.type$j.householder$j.tel$j.stat$j.sell_out$a.name#community_name$b.username#update_name',
                'db_where'    =>'a.name#like#%#pcommunity_name#like3#string$j.code#like#%#pcode#like3#string$j.householder#like#%#phouseholder#like3#string$j.tel#like#%#ptel#like3#string',
            ]
        ]);       
    }

    /**
     * @title 添加房屋
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();

        if ($request::instance()->isAjax()){
            
            $data = $this->_get_param();
            
            $res = db('qhrl_household')->insert($data);
            if($res > 0){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加失败！');
            }
        }

        //获取默认
        $this->assign('area_2list',$this->getAreaList($this->area_1));//市
        $this->assign('area_3list',$this->getAreaList($this->area_2));//区
        $this->assign('area_4list',$this->getAreaList($this->area_3,3));//小区
        $this->assign('area_1',$this->area_1);
        $this->assign('area_2',$this->area_2);
        $this->assign('area_3',$this->area_3);
        return $this->fetch();
    }

    /**
     * @title 修改房屋
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $db_household = db('qhrl_household');
        $id   = $request->param('id/d','');
        $info = $db_household->find($id);

        if ($request::instance()->isAjax()){
            
            $data = $this->_get_param();
            $res = $db_household->where('id',$id)->update($data);
            if($res > 0){
                $this->a_suc('编辑成功！');
            }else{
                $this->a_err('编辑失败！');
            }
        }

        if($info['area_id'] > 0){
            $area_list = $this->getParent($info['area_id']);
        }

        if(count($area_list) == 3){
            $area_1 = $area_list[0];
            $area_2 = $area_list[1];
            $area_3 = $area_list[2];
        }else{
            $area_1 = $this->area_1;
            $area_2 = $this->area_2;
            $area_3 = $this->area_3;
        }
        //获取默认
        $this->assign('area_2list',$this->getAreaList($area_1));//市
        $this->assign('area_3list',$this->getAreaList($area_2));//区
        $this->assign('area_4list',$this->getAreaList($area_3,3));//区
        $this->assign('area_1',$area_1);
        $this->assign('area_2',$area_2);
        $this->assign('area_3',$area_3);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @title 房屋数据
     * @type menu
     * @menudisplay false
     */
    public function _get_param(){
        $user = $this->getLoginUserinfo();
        $request = request();
        $data = [];
        $stats = $this->stats;
        $types = $this->types;
        $sell_outs = $this->sell_outs;
        $data['householder']  = $request->param('householder/s','');
        $data['tel']          = $request->param('tel/s','');
        $data['community_id'] = $request->param('area_4/d','');
        $data['building']     = $request->param('building/d','');
        $data['unit']         = $request->param('unit/d','');
        $data['room']         = $request->param('room/d','');
        $data['area']         = $request->param('area/s','');
        $data['type']         = $request->param('type/d','');
        $data['stat']         = $request->param('stat/d','');
        $data['sell_out']     = $request->param('sell_out/d','');
        $data['update_id']    = $user['id'];

        if(empty($data['householder'])){
            $this->a_err('户主姓名不为空！');
        }

        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$data['tel']) != 1){
            $this->a_err('请输入正确的手机号！');
        }

        if(empty($data['community_id'])){
            $this->a_err('请选择小区！');
        }
        $data['community_id'] = strlen($data['community_id']) < 3 ? sprintf("%03d",$data['community_id']) : $data['community_id'];

        $data['building'] = strlen($data['building']) == 1 ? sprintf("%02d",$data['building']) : $data['building'];
        if(preg_match('/\d{2}/',$data['building']) != 1){
            $this->a_err('请输入正确的楼号！');
        }

        $data['unit'] = strlen($data['unit']) == 1 ? sprintf("%02d",$data['unit']) : $data['unit'];
        if(preg_match('/\d{2}/',$data['unit']) != 1){
            $this->a_err('请输入正确的单元号！');
        }

        if(preg_match('/\d{4}/',$data['room']) != 1){
            $this->a_err('请输入正确的房间号！');
        }

        $data['code'] = substr($data['community_id'], 0,3) . $data['building'] . $data['unit'] . $data['room'];
        if(preg_match('/\d{11}/',$data['code']) != 1){
            $this->a_err('户代码生成错误！');
        }

        $data['area'] = round($data['area'],2);
        if(empty($data['area'])){
            $this->a_err('房屋面积不为空！');
        }

        if(!isset($stats[$data['stat']])){
            $this->a_err('房屋状态错误！');
        }

        if(!isset($types[$data['type']])){
            $this->a_err('房屋类型错误！');
        }

        if(!isset($sell_outs[$data['sell_out']])){
            $this->a_err('房屋出售状态错误！');
        }
        return $data;
    }

    /**
     * @title 删除房屋
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrl_household']);
    }
}