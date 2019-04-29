<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 收货地址
 * @type menu
 * @menudisplay false
 *
 */
class Address extends Home{
    
    /**
     * @title 查询地址
     * @type interface
     * @login 1
     * @return total   返回的条数  0   1  为0时没有data参数，最多返回一条
     * @return data   收货地址   array   0   
     */
    public function getAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id']=10;
        $request = request();
        $db_address = db('qhrlwx_address')->where('user_id',$userid)->find();

        if(!empty($db_address) && !empty($db_address['area_id'])){
            $db_address['area'] = $this->getParent($db_address['area_id']);
        }
        $this->suc($db_address);
    }

    /**
     * @title 设置
     * @type interface
     * @login 1
     * @param name   收货人姓名  1   1  
     * @param mobile   手机号   1   1   手机号有格式验证
     * @param community_id   小区编号  1   0
     * @param area_id   收货地区   1   1 
     * @param postcode   邮编  1   0
     * @param address   收货地址详情   1   1 
     */
    public function setAc(){
        $user = $this->getLoginUserinfo();
        $userid = $user['id'];
        $request = request();
        $db_address = db('qhrlwx_address'); 
        $data = [];
        $data['user_id']      = $userid;
        $data['name']         = $request->post('name/s',''); 
        $data['mobile']       = $request->post('mobile/s',''); 
        $data['community_id'] = $request->post('community_id/s',''); 
        $data['area_id']      = $request->post('area_id/s',''); 
        $data['postcode']     = $request->post('postcode/s',''); 
        $data['address']      = $request->post('address/s',''); 
        $data['create_time']  = time();

        if(empty($data['name'])){
            $this->err('请输入收货人姓名！');
        }

        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$data['mobile']) != 1){
            $this->err('请输入正确的手机号！');
        }

        if(empty($data['area_id'])){
            $this->err('请选择收货人地区！');
        }

        if(empty($data['address'])){
            $this->err('请输入收货详情地址！');
        }
        
        $info = $db_address->where('user_id',$userid)->find();
        if(empty($info)){
            $rest = $db_address->insert($data);
        }else{
            $rest =$db_address->where('id',$info['id'])->update($data);
        }

        if($rest !== false){
            $this->suc('编辑成功！');
        }else{
            $this->err('编辑失败！');
        }
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
        return implode('/',array_column(array_reverse($info),'name','id'));
    }
}