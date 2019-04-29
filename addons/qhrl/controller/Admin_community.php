<?php
namespace addons\qhrl\controller;

/**
 * @title 秦华小区
 * @type menu
 *
 */

class Admin_community extends Admin_qhrl{

    protected $stats;
    public function _initialize(){

        $this->stats = [
                '1' => '有效',
                '0' => '失效',
                ];
        parent::_initialize();
    }
    /**
     * @title 小区列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        return j_help()->handleList([
            'topbuttons'=>'qhrl/Admin_community/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'qhrl/Admin_community/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id
             $qhrl/Admin_community/del#删除#glyphicon glyphicon-edit#btn btn-warning#del#id',
            'search'    => 'a.name',
            'query'     => [
                'db_table' => 'qhrl_community',
                'db_join'  => '__ADMIN_AREA__ a#a.id=j.area_id#left$__ADMIN_ADMIN__ b#b.id=j.update_id#left',
                'db_fields' => 'j.id$j.name$j.stat$j.address$j.update_id$a.name#area_name$b.username#update_name',
                'db_where'    =>'a.name#like#%#parea_name#like3#string',
            ]
        ]);       
    }

    /**
     * @title 添加小区
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();

        if ($request::instance()->isAjax()){
            
            $data = $this->_get_param();
            
            $res = db('qhrl_community')->insert($data);
            if($res > 0){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加失败！');
            }
        }

        //获取默认
        $this->assign('area_2list',$this->getAreaList($this->area_1));//市
        $this->assign('area_3list',$this->getAreaList($this->area_2));//区
        $this->assign('area_1',$this->area_1);
        $this->assign('area_2',$this->area_2);
        $this->assign('area_3',$this->area_3);
        return $this->fetch();
    }

    /**
     * @title 修改小区
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $db_community = db('qhrl_community');
        $id   = $request->param('id/d','');
        $info = $db_community->find($id);

        if ($request::instance()->isAjax()){
            
            $data = $this->_get_param();
            $res = $db_community->where('id',$id)->update($data);
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
        $this->assign('area_1',$area_1);
        $this->assign('area_2',$area_2);
        $this->assign('area_3',$area_3);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @title 小区数据
     * @type menu
     * @menudisplay false
     */
    public function _get_param(){
        $user = $this->getLoginUserinfo();
        $request = request();
        $data = [];
        $stats = $this->stats;
        $data['name']    = $request->param('name/s','');
        $data['area_id'] = $request->param('area_3/d','');
        $data['stat']    = $request->param('stat/d','');
        $data['address'] = $request->param('address/s','');
        $data['update_id'] = $user['id'];

        if(empty($data['name'])){
            $this->a_err('小区名不为空！');
        }

        if(empty($data['area_id'])){
            $this->a_err('上级地区不为空！');
        }

        if(!isset($stats[$data['stat']])){
            $this->a_err('状态错误！');
        }

        if(empty($data['address'])){
            $this->a_err('小区地址不为空！');
        }

        return $data;
    }

    /**
     * @title 删除小区
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrl_community']);
    }

    
}