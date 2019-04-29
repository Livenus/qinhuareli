<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 小区
 * @type menu
 * @menudisplay false
 *
 */
class Community extends Home{

    /**
     * @title 小区列表
     * @type interface
     * @login 1
     * @param area_id 区域id  1  1  小区的上一级
     * @return total   返回的条数  0   1  为0时没有data参数
     * @return data   查询获取的小区列表   array   0   
     */
    public function get_listAc(){
        $request = request();
        $area_id   = $request->post('area_id/s','');
        if(empty($area_id)){
            $this->err('参数错误！');
        }

        $r = $this->get_community_list('',$area_id);

        if($r === false){
            $res = ['total' => 0];
        }else{
            $res = [
                'total' => count($r),
                'data' => $r
            ];
        }
        $this->suc($res);
    }
}