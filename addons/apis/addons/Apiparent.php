<?php
namespace addons\apis\addons;

/**
 * 所有接口都应该实现的
 * @internal
 *
 */
abstract class Apiparent {
    
    protected $errmsg='';
    
    protected $key = '';
    
    protected $secret = '';
    
    protected $postData = [];
    
    protected $responseData = [];
    
    
    public function __construct(){
        $request = request();
        $this->key = $request->post('key/s', '');
        $this->secret = $request->post('time/d', 0);
        $this->postData = $request->post();
        
    }
    
    
    
    /**
     * 鉴权，返回TRUE|false
     */
    public function auth(){
        
        $key = $this->key;
        $time = $this->secret;
        $msg = '';
        try{
            $requestTime = request()->time();
            if(empty($key)){
                throw new \think\Exception('key不能为空');
            }elseif( ($time + 300 < $requestTime) || ($time-300 > $requestTime) ){
                throw new \think\Exception('时间设置错误。请设置5分钟内');
            }
            
            $postData = $this->postData;
            $sign = $postData['sign'];
            if(empty($sign) ){
                throw new \think\Exception('SIGN_EMPTY');
            }
            $signData = $postData;
            unset($signData['sign']);
            
            $keyinfo = j_model('apis_keys')->where('key', $key)->find();
            if(!$keyinfo){
                throw new \think\Exception('INVALID_KEY');
            }
            
            //IP白名单 
            if(!empty($keyinfo['ips'])){
                $iparr = explode("\r\n", $keyinfo['ips']);
                if(!in_array(request()->ip(), $iparr)){
                    throw new \think\Exception('INVALID_IP');
                }
                
            }
            
            
            if($sign !=  $this->gen_sign($signData, $keyinfo['secret'])){
                throw new \think\Exception('SIGN_ERR');
            }
        }catch (\think\Exception $e){
            $msg = $e->getMessage();
        }
        
        if($msg == ''){
            return true;
        }        
        
        $this->errmsg = $msg;
        return false;
        
    }
    
    /**
     * 返回出错消息
     */
    public function get_errmsg(){
        return is_string($this->errmsg)?$this->errmsg:'';
    }
    
    /**
     * 生成签名
     * @param unknown $data
     */
    public function gen_sign($data, $secret=''){
        if(is_string($data)) return $data;
        if(!is_array($data)) return '';
        ksort($data);
        $signContent = "";
        $i = 0;
        foreach ($data as $k => $v) {
            if (false === $this->check_empty($v)) {
                $signContent .= (string)($k) . '=' . (string)($v) . '&';
                $i++;
            }
        }
        $signContent .= 'sign=' . $secret;
        return md5($signContent);
    }
    
    private function check_empty($v) {
        if (!isset($v)){
            return true;
        }elseif (trim($v) === ""){
            return true;
        }
        return false;
    }
    
    
    /**
     * 返回数据
     */
    abstract function ret();
    
    /**
     * 
     */
    public function success($data=[]){
        $rdata = [
            'code'=>'SUCCESS',
            'key' => $this->key,
            'time' => request()->time(),
            'data' => base64_encode(is_string($data)?$data:json_encode($data, JSON_UNESCAPED_UNICODE))
        ];
        $this->responseData = $rdata;
        $rdata['sign'] = $this->gen_sign($rdata, $this->secret);
            
        echo json_encode($rdata, JSON_UNESCAPED_UNICODE);
        $rdata['data'] = $data;
        $this->responseData = $rdata;
        $this->responselog($rdata['code']);
        die;
    }
    
    public function error($errmsg){

        $rdata = [
            'code'=>'ERROR',
            'key' => $this->key,
            'time' => request()->time(),
            'errmsg' => $errmsg
        ];
        $rdata['sign'] = $this->gen_sign($rdata, $this->secret);
        echo json_encode($rdata, JSON_UNESCAPED_UNICODE);
        $this->responseData = $rdata;
        $this->responselog($rdata['code']);
        die;
    }
    
    public function responselog($stat){
        
        //计算记录表的名称
        $tablename =  'apis_response_log_'.date('Ym', request()->time());
        $fulltablename = \think\Config::get('database.prefix') . $tablename;
        $cachekey = 'apis_hastable_' . $fulltablename;
        
        if( !(\think\Cache::get($cachekey, null))){
            //是否有表
            $hastable = \think\Db::query('show tables like "'.$fulltablename.'"');
            if(empty($hastable)){
                $createsql = "CREATE TABLE `$fulltablename`(
                              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '#',
                              `uri` varchar(255) NOT NULL DEFAULT '' COMMENT '接口地址',
                              `requestdata` text NOT NULL COMMENT '请求参数',
                              `response` text NOT NULL COMMENT '返回参数',
                              `status` enum('ERROR','SUCCESS') NOT NULL COMMENT '结果:cSUCCESS=成功,ERROR=失败',
                              `create_time` int(11) NOT NULL COMMENT '请求时间',
                              `key` int(11) NOT NULL COMMENT '用户的KEY',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='接口被调用的月记录'";
                \think\Db::execute($createsql);
            }
        
            \think\Cache::set($cachekey, '1');
        }

        $data = [
            'uri' => request()->url(),
            'requestdata' => json_encode($this->postData, JSON_UNESCAPED_UNICODE),
            'response' => json_encode($this->responseData, JSON_UNESCAPED_UNICODE),
            'status' => $stat,
            'key'    => $this->responseData['key']
        ];
        j_model($tablename)->save($data);
    }
}