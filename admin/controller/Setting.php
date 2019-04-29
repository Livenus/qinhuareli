<?php 
namespace app\admin\controller;
/**
 * @title 设置
 * @icon fa fa-gears
 */
class Setting extends Home{

    /**
     * @title 验证码配置
     * @type menu
     * @icon fa fa-gear
     */
    public function capthcaAc(){
        return $this->set('capthca', '验证码');
    }
    /**
     * @title 上传配置
     * @type menu
     * @icon fa fa-gear
     */
    public function uploadAc(){
        return $this->set('upload', '上传');
    }
    
    
    /**
     * @title 全部配置
     * @type menu
     * @icon fa fa-gear
     */
    public function allAc(){
        return $this->set('', '全部');
    }
    
    
    public function set($class='', $name=''){
        $list = j_getSetting($class);
        
        $param = [];
        if($list){
            foreach($list as $k => $v){
                $param[$v['name']] = [
                    'id'             => $v['id'],
                    'column_name'    => $v['name'],
                    'column_default' => $v['val']?$v['val']:$v['default_val'],
                    'is_nullable'    => 'NO',
                    'data_type'      => $v['field_type'],
                    'column_comment' => $name.'_'.$v['comment'].( $v['default_val']?(':h默认值：'.$v['default_val']):'')
                ];
            }
        }
        $updatecount = 0;
        if($this->request->isPost()){
            if(!!($post = $this->request->post())){
                foreach($post as $k => $v){
                    if(array_key_exists($k, $param)){
                        j_model('jhy_setting')->where('id',$param[$k]['id'])->update(['val'=>$v]);
                        $updatecount ++ ;
                    }
                }
            }
        
        
            return $this->success('更新成功' . $updatecount . '个配置');
        }
        if(empty($param)){
            return $this->error('无此配置。确认需要此配置，请在表jhy_setting中加入再来配置',null,null,'sss');
        }
        
        return j_help()->handleEdit([
            'name'  => $name . '配置',
        
            'notablefields' => $param,
            //             'savefields' => 'password'
        ]);
    }
    
}