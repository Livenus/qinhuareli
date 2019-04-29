<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 留言
 * @type menu
 * @menudisplay false
 *
 */
class Message extends \app\common\controller\Home{

    public function suc($data){
        $rdata['stat'] = 1;
        $rdata['data']  = $data;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @title失败返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function err($msg){
        $rdata['stat'] = 0;
        $rdata['msg']  = $msg;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * @title 添加留言
     * @type interface
     * @login 0
     * @param content  留言内容  msg 1  
     */
    public function addAc(){
        $request = request();
        $data = [];

        if($this->isLogin() !== true){
            $data['user_id'] = -1;
            $data['username'] = '游客';
        }else{
            $user = $this->getLoginUserinfo();
            $data['user_id'] = $user['id'];
            $data['username'] = $user['username'];
        }
    	
        $data["content"]     = $request->post('content/s','');
        $data["create_time"] = time();
        $data["stat"] = 1;
        if(empty($data["content"] )){
        	$this->err('建议不能为空！');
        }
        $res = db("qhrlwx_message")->insert($data);
        if($res!== false){
        	$this->suc('建议成功！');
        }else{
        	$this->err('建议失败！');
        }
    }

  
}