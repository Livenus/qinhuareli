<?php 
namespace addons\apis\controller;

/**
 * @title API请求
 *
 */
class Api extends \app\common\controller\Module_home{
    
    /**
     * @title 接口请求入口
     * @type menu
     * @display false
     * @login 0
     */
    public function indexAc(){
//         \think\Config::set('default_return_type', 'json');
        $this->request->get(['_ajax'=>1]);
        
        $name = $this->request->param('n');
        $func = $this->request->param('f', 'ret');
        
        if(empty($name)) return $this->error('接口地址错误(1)', null, null, -1);
        if(empty($func)) return $this->error('接口地址错误(2)', null, null, -1);
        if(is_file(ROOT_PATH . DS . 'addons/apis/addons/' . $name . '/' . ucfirst($name) . '.php')){
            $class_name = 'addons\\apis\\addons\\' . $name . '\\' . ucfirst($name);
            $class = new $class_name();
        }
        if(!class_exists($class_name)){
            return $class->error('接口不存在(3)');
        }
        
        //鉴权
        if( $class->auth() !== true ){
            return $class->error($class->get_errmsg());
        }
        
        return $class->success($class->$func());
        
    }
    
    /**
     * @title 接口回调
     * @type menu
     * @display false
     * @login 0
     */
    public function notifyAc(){
        return 'uncode';
    }
    
    
    
    public function success($data=[]){
        $rdata = [
            'code'=>'SUCCESS',
//             'key' => $this->key,
            'time' => request()->time(),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ];
//         $data['sign'] = $this->gen_sign($data, $this->secret);
            
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }
    
    public function error($data=[]){
        $rdata = [
            'code'=>'ERROR',
            'key' => $this->key,
//             'time' => request()->time(),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ];
//         $data['sign'] = $this->gen_sign($data, $this->secret);
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }
    
}