<?php 

namespace ext\jhy;

/**
 *  手机短信发送基类
 * @author Administrator
 *
 */

class  Sms {
    
    private $type='';
    private $param='';
    
    public function __construct($type=''){
        
        if($type == ''){
            $info = j_model('admin_smsconfig')->where('is_open',1)->find();
            if(isset($info['name']) && !empty($info['name']) && isset($info['configs']) && !empty($info['configs'])){
                $this->type = $info['name'];
                $this->param = unserialize($info['configs']);
            }else{
                return err('短信接口参数配置错误！');
            }
            
        }
        
    }
    
  
    /**
     * 发送手机短信
     * @param $mobiles 手机号码
     * @param string $content 短信内容，如使用模板则可以空
     * @param array $params   变量列表
     * @param number $templateid  模板ID
     */
    public function sendMsg($mobiles, $templateid=0, $params=array(), $content='', $type='', $s_id=0 /*多系统时不为0*/){
        if($type == ''){
            $type = $this->type;
        }
        if(empty($type)) return err('接口类型不能为空');
        //验证限制
        $setting = j_getSetting('syssms');
        $f_mobile_d = (int)$setting['f_mobile_d']['val']; 
        $f_ip_d     = (int)$setting['f_ip_d']['val'];
        $sleep      = (int)$setting['sleep']['val'];
        $needcode   = (int)$setting['needcode']['val'];
        
        if($needcode == 1){
            //图形验证码，暂未做功能
        }
        
        $today = strtotime(date("Y-m-d"),time());
        $night = strtotime('+ 1 day',$today) -1;
        if($f_mobile_d > 0){
            if(j_model('admin_smslog')->where('mobile',$mobiles)->where('create_time','between',[$today,$night])->count() > $f_mobile_d){
                return err('已超过每天最大发送次数限制！');
            }
        }
        if($f_ip_d > 0){
            if(j_model('admin_smslog')->where('clientip', request()->ip(1))->where('create_time','between',[$today,$night])->count() > $f_ip_d){
                return err('已超过每天最大发送次数的限制！！');
            }
        }
        
        if($sleep > 0){
            $lastlog = j_model('admin_smslog')->where('mobile', $mobiles)->find();
            if(!empty($lastlog) && ($lastlog['create_time'] + $sleep) >= request()->time()){
                return err('发送太频繁，过一会再发吧！');
            }
        }
        if(!!($sms = $this->getSmsClass($type))){
            $result = $sms->sendMsg($mobiles, $content, $params, $templateid);

            if($type == 'yunxin' && $result['stat'] == 1){
                $code = $result['msg'];
            }else{
                $code = $params['code'];
            }
            $key = 'code_'.$mobiles;
            \think\Session::set($key,$code);
            //存记录
            j_model('admin_smslog')->isUpdate(false)->save([
                's_id'=>$s_id,
                'mobile' => $mobiles,
                'vcode' => $code,
                'content' => $content, 
                'stat' => $result['stat'],
                'clientip' => request()->ip(1)
            ]);
            if(is_suc($result)){
                return suc($code);
            }
            return err($result['msg']);
        }
        return err('出错了');

    }

    public function verifycode($mobile,$code){
        $key = 'code_'.$mobile;
        $checkcode = \think\Session::get($key);
        
        if((int)$code == (int)$checkcode){
            return suc('');
        }else if($this->type == 'yunxin'){

            if(!!($sms = $this->getSmsClass($this->type))){
                $result = $sms->verifycode($mobile,$code);

                if($result['stat'] == 1){
                    return suc($result['msg']);
                }
                return err($result['msg']);
            }
        }else{
            return err('验证码错误！');
        }
        
    }
    
    
    public function getSmsClass($type=''){
        $file = EXTEND_PATH . 'ext/jhy/sms/'.$type.'/' . ucfirst($type). 'sms.php';
        if(is_file($file)){
            $class = '\\ext\\jhy\\sms\\'.$type.'\\' . ucfirst($type).'sms';
            return new $class($this->param);
        }else{
            return null;
        }
    }
    
    
    
    
    /**
     * 所有SMS的插件
     */
    public function getsmslist(){
        $path = EXTEND_PATH . 'ext/jhy/sms/';
        $dirs = scandir($path);
        $list = [];

        foreach($dirs as $k => $v){
            if($v == '.'|| $v=='..') continue;
            if(!is_dir($path . $v)) continue;
            if(!!($sms = $this->getSmsClass($v))){
                $list[$v] = $sms->getinfo();
                $list[$v]['en_name'] = $v;
            }
         
        }
        return $list;
    }
    
    
    
}