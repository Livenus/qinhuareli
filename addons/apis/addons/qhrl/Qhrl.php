<?php
namespace addons\apis\addons\qhrl;

/**
 * 
 * @internal
 *
 */
class Qhrl extends \addons\apis\addons\Apiparent {

    /**
     * 返回数据
     */
    public function ret(){
        
        return [
            'a' => 'aaa',
            'b' =>'bbb'
        ];
    }
    
    public function set(){
        return 'set';
    }

    public function reta(){
        $request = request();

        $id = $request->param('id');
    
        return [
            'a' => 'aaa',
            'b' =>'bbb',
            'id' => $id
        ];
    }

    /**
     * @title有数据返回
     * @type menu
     * @login 0
     * @menudisplay false
     */
    private function something($data){
        return [
            'stat' => 1,
            'data' => $data
        ];
    }

    /**
     * @title无数据返回
     * @type menu
     * @login 0
     * @menudisplay false
     */
    private function nothing(){
        return [
            'stat' => -1
        ];
    }


    /**
     * @title 添加关联房屋
     * @type inter
     * @login 0
     * @param true_name
     * @param community_id
     * @param mobile
     * @param building
     * @param unit
     * @param room
     * @param code
     */
    public function add_house(){
        $data = $this->_get_house_param();

        $db_house = db('qhrl_household');
        //验证用户名唯一
        $id = $db_house->field('id')->where('code',$data['code'])->where('community_id',$data['community_id'])->find();
        if(!empty($id)){
            $house_id = $id['id'];
        }else{

            $data['type']         = 1;    //住宅
            $data['stat']         = 1;    //生效
            $data['sell_out']     = 1;    //出售

            $house_id = $db_house->insert($data);
        }

        if(empty($house_id)){
            $return = $this->nothing();
        }else{
            $return = $this->something($house_id);
        }
        file_put_contents('sos.txt', 'return--'.json_encode($return)."\r\n\r\n",FILE_APPEND);
        return $return;
    }

    /**
     * @title 修改关联房屋
     * @type inter
     * @login 0
     * @par1am house_id
     * @par1am true_name
     * @par1am community_id
     * @par1am mobile
     * @par1am building
     * @par1am unit
     * @par1am room
     * @par1am code
     */
    // public function edit_house(){
    //     $request = request();
    //     $house_id = $request->param('house_id/d','');
    //     if(empty($house_id)){
    //         $return = $this->nothing();
    //     }

    //     $data = $this->_get_house_param();

    //     $db_house = db('qhrl_household');
    //     //验证用户名唯一
    //     $count = $db_house->where('id',$house_id)->count();

    //     if($count != 1){
    //         $return = $this->nothing();
    //     }else{
    //         $res = $db_house->where('id',$house_id)->update($data);
    //     }

    //     if(empty($house_id) || $res === false){
    //         $return = $this->nothing();
    //     }else{
    //         $return = $this->something($house_id);
    //     }

    //     return $return;
    // }

    /**
     * @title获取传入房屋信息
     * @type menu
     * @login 0
     * @menudisplay false
     */
    private function _get_house_param(){
        $request = request();
        $data = [];
        $data['householder']  = $request->param('true_name/s','');
        $data['community_id'] = $request->param('community_id/s','');
        $data['tel']          = $request->param('mobile/s','');
        $data['building']     = $request->param('building/s','');
        $data['unit']         = $request->param('unit/s','');
        $data['room']         = $request->param('room/s','');
        $data['code']         = $request->param('code/s','');

        return $data;
    }

    /**
     * @title 获取房屋信息
     * @type inter
     * @login 0
     * @param true_name
     * @param community_id
     * @param mobile
     * @param building
     * @param unit
     * @param room
     * @param code
     */
    public function get_house_info(){
        $request = request();
        $ids  = $request->param('ids/s','');
        $stat = $request->param('stat/s','');
        $db_household = db('qhrl_household');
        
        if(empty($ids) || !is_string($ids)){
            $return = $this->nothing();
        }else{
            $ids = explode(',', $ids);
        }

        if($stat){
            $field = 'j.id,j.community_id,j.unit,j.building,j.room,j.code,j.area,j.type,j.householder,j.tel,j.stat,j.sell_out,a.name as community_name,a.address,area_id';
            $house_info = $db_household->alias('j')->field($field)
                        ->join('qhrl_community a','j.community_id = a.id','LEFT')
                        ->where('j.id','in',$ids)->select();

            $com_ids = array_column($house_info,'address','area_id');
            foreach ($com_ids as $k => &$v) {
                $area_str = $this->getParent($k);
                $v = $area_str;
            }
            foreach ($house_info as &$value) {
                $value['area_name'] = $com_ids[$value['area_id']] ?  $com_ids[$value['area_id']] : ''; 
            }
        }else{
            $house_info = $db_household->field('id')->where('id','in',$ids)->select();
        }
        
        

        if(empty($house_info)){
            $return = $this->nothing();
        }else{
            $return = $this->something($house_info);
        }
        
        return $return;
    }


    /**
     * @title 获取小区列表
     * @type inter
     * @login 0
     * @param area_id
     * @param communitys
     */
    public function get_community(){
        $request = request();
        $area_id    = $request->param('area_id/d','');
        $communitys = $request->param('communitys/s','');
        
        if(empty($area_id) && empty($communitys)){
            $return = $this->nothing();
        }else{
            $field = 'id,name,area_id,address';

            if($communitys){
                $community_ids = explode(',',$communitys);
                if(count($community_ids) > 0){
                    $where['id'] = ['in',$community_ids];
                }
            }
            
            if(!empty($area_id)){
                $where['area_id'] = $area_id;
            }
            
            $where['stat']    = 1;
            $com_list = db('qhrl_community')->field($field)->where($where)->select();
            
            if(empty($com_list)){
                $return = $this->nothing();
            }else{
                $return = $this->something($com_list);
            }
        }

        return $return;
    }

    /**
     * @title 查询省市区
     * @type menu
     * @login 1
     * @menudisplay false
     */
    private function getParent($id){
        if(empty($id)){
            return false;
        }
        $info = [];
        while (!empty($id)) {
            $area = db('admin_area')->find($id);
            $info[] = $area;
            $id = $area['pid'];
        }
        return implode(' ',array_column(array_reverse($info),'name','id'));
    }
}