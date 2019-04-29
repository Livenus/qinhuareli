<?php 
namespace addons\apis\controller;

/**
 * @title 测试
 * @internal
 */
class Test extends \app\common\controller\Module_home{
    
    
    public function indexAc(){
        
        $url = 'http://192.168.0.107:8087/index.php/apis/api/index/n/kuaidi/f/ret';
        
        $header =['Content-Type'=>'multipart/form-data;charset=utf-8'] ;
        $postData = [
            'time' => $this->request->time(),
            'key' => '123456'
        ];
        
        
        $secret = 'abcd';
        ksort($postData);
        $signContent = "";
        $i = 0;
        foreach ($postData as $k => $v) {
            if (isset($v) && !empty($v)) {
                $signContent .= (string)($k) . '=' . (string)($v) . '&';
                $i++;
            }
        }
        $signContent  .= 'sign=' . $secret;

        $sign =  md5($signContent);
        
        $postData['sign'] = $sign;
        
        
        
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $return = curl_exec($ch);
        $errmsg = '';
        if(!!($err = curl_errno($ch))){
            $errmsg = $err;
        }
        
        curl_close($ch);
        
        if($errmsg != ''){
            return $errmsg;
        }
        
        
        

        
        $r = json_decode($return, true);
        if(isset($r['data'])){
            $r['data'] = json_decode(base64_decode($r['data']), true);
        }
        dump($r);
      
        
        die;

        
        
        if($this->isLogin() !== true){
            $this->redirect('index/login/login');
        }
        return   $this->view->fetch(j_view_home('index'),[],[],[],true);
        
    }
    
}