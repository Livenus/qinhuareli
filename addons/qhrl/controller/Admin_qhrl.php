<?php 
namespace addons\qhrl\controller;

/**
 * @title 后台
 * @type menu
 * @menudisplay false
 */
class Admin_qhrl  extends \app\common\controller\Admin_home{

    protected $area_1;
    protected $area_2;
    protected $area_3;
    public function _initialize(){

        $this->area_1 = '27';//陕西省
        $this->area_2 = '438';//西安市
        $this->area_3 = '4677';//雁塔区
        parent::_initialize();
        $this->assign('area_1list',$this->getAreaList(0));//省
    }
    /**
     * @title 后台成功返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function a_suc($msg,$data,$url,$wait){
        $rdata['code'] = 1;
        $rdata['msg']  = $msg;
        $rdata['data']  = $data;
        $rdata['url']  = $url;
        $rdata['wait']  = $wait;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @title 后台失败返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function a_err($msg,$data,$url,$wait){
        $rdata['code'] = 0;
        $rdata['msg']  = $msg;
        $rdata['data']  = $data;
        $rdata['url']  = $url;
        $rdata['wait']  = $wait;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }


    /**
     * @title 获取地区列表
     * @type menu
     * @menudisplay false
     */
    public function getAreaListAc(){
        $request = request();
        $area_id = $request->param('area_id/d','0');
        $type    = $request->param('type/d','1');

        $this->a_suc('',$this->getAreaList($area_id,$type));
    }

    protected function getAreaList($area_id,$type){
        if($type == 3){
            $list = db('qhrl_community')->where('area_id',$area_id)->select();
        }else{
            $list = db('admin_area')->where('pid',$area_id)->select();
        }
        return $list;
    }


    /**
     * @title 查询省市区
     * @type menu
     * @login 1
     * @param $id 区级id
     * @return $return [[省],[市],[区]] 返回三级信息键是各自编号
     * @menudisplay false
     */
    protected function getParent($id){
        if(empty($id)){
            return false;
        }
        $info = [];
        while (!empty($id)) {
            $area = db('admin_area')->find($id);
            $info[] = $area;
            $id = $area['pid'];
        }
        return array_column(array_reverse($info),'id');
    }

}