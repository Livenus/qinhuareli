<?php 

namespace ext\jhy;
use \think\Cache;
class Wx {
    
    protected $account=[];
    
    protected $errmsg='';
    
    public function __construct($account = []){
        $account['id'] = $account['user_id'];
        $this->account = $account;
        if(empty($account['token'])){
            exit('wx account token empty!');
        }
    }
    
    
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
//     public function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
//     {
//         //排序
//         try {
//             $array = array($encrypt_msg, $token, $timestamp, $nonce);
//             sort($array, SORT_STRING);
//             $str = implode($array);
//             return sha1($str);
//         } catch (Exception $e) {
//             //print $e . "\n";
//             return false;
//         }
//     }
    /**
     * 验证服务器地址的有效性         开发者提交信息后，微信服务器将发送GET请求到填写的服务器地址URL上，
     * @return boolean
     */
    public function checkSign() {
        $request = request();
        $token = $this->account['token'];
        $signkey = array($token, $request->get('timestamp'), $request->get('nonce'));
        sort($signkey, SORT_STRING);
        $signString = implode($signkey);
        $signString = sha1($signString);
        return ($signString == $request->get('signature'));
    }
    
    /**
     * 获取access_token
     * @param string $usrcache 是否使用cache
     * @return false|token
     */
    public function getAccess_token($usrcache=true){
        $cache_id = 'wx_mp_token_' . $this->account['id'];
        if(empty($this->account['access_token'])){
            $this->account['access_token'] = Cache::get($cache_id, '');
        }
        
        if(empty($this->account['key']) || empty($this->account['secret'])){
            $this->errmsg = ('invalidate appid or appsecret');
            return false;
        }
        
        //强制不使用缓存，或无缓存时，从微信服务器获取
        if(!$usrcache || empty($this->account['access_token'])){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->account['key'].'&secret=' . $this->account['secret'];
            $response = j_http($url);
            if(is_suc($response)){
                $r = json_decode($response['msg'], true);
                $this->account['access_token'] = $r['access_token'];
                Cache::set($cache_id, $r['access_token'], $r['expires_in']-200);
            }else{
                $this->errmsg = 'WX_ERR:' . $response['msg'];
                return false;
            }
        }
        
        
        return $this->account['access_token'];
        
    }
    
    
    /**
     * 数据转换
     * @param unknown_type $message
     * @return Ambigous <string, multitype:multitype: string boolean NULL mixed >
     */
//     public function parse($message) {
//         $packet = array();
//         if (!empty($message)){
//             $obj = $this->xmlToArray($message);
// //             $obj = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
//             if(!empty($obj)) {
//                 $packet['from'] = strval($obj['FromUserName']);
//                 $packet['to'] = strval($obj['ToUserName']);
//                 $packet['time'] = strval($obj['CreateTime']);
//                 $packet['type'] = strval($obj['MsgType']);
//                 $packet['event'] = strval($obj['Event']);
                
//                 foreach ($obj as $variable => $property) {
//                     $packet[strtolower($variable)] = (string)$property;
//                 }
//                 if($packet['type'] == 'text') {
//                     $packet['content'] = strval($obj->Content);
//                     $packet['redirection'] = false;
//                     $packet['source'] = null;
//                 }
                
//             }
//         }
//         return $packet;
//     }
    
    /**
     * 发送微信模板消息
     * @param string $touser      接收人
     * @param string $template_id 模板ID
     * @param array $postdata     数据array('param1'=>array('value'=>'v1','color'=>'#ff0000'),...)
     * @param string $url         URL地址
     * @param string $topcolor    模板颜色
     * @return true | errmsg
     */
    public function sendTplNotice($touser, $template_id, $postdata, $url = '', $topcolor = '#AAA') {
        if(empty($touser)) {
            return ('参数错误,粉丝openid不能为空');
        }elseif(empty($template_id)) {
            return ('参数错误,模板标示不能为空');
        }elseif(empty($postdata) || !is_array($postdata)) {
            return ('参数错误,请根据模板规则完善消息内容');
        }
    
        $token = $this->getAccess_token();
        if (false === ($token)) {
            return ('tokenerr:' . $this->getMessage());
        }
    
        $data = array();
        $data['touser']      = $touser;
        $data['template_id'] = trim($template_id);
        $data['url']         = trim($url);
        $data['topcolor']    = trim($topcolor);
        $data['data']        = $postdata;
        
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $token;
        $response = j_http($url, json_encode($data), array('Content-Type'=>'application/json;charset=UTF-8'), 10);
        if(is_suc($response)){
            $response = json_decode($response['msg'], true);
            if($response['errcode'] == 0){
                return true;
            }else{
                return 'WX_ERR:'. $response['errmsg'];
            }

        }
        return 'WX_ERR:'.$response['msg'];
    }
    
    
    public function getMessage(){
        $msg = $this->errmsg;
        $this->errmsg = '';
        return $msg;
    }
    
    //数组转XML
    public function arrayToXml($arr, $hf=true){
        $xml = '';
        foreach ($arr as $key=>$val){
            if (is_numeric($val)){
                $xml.='<'.$key.'>'.$val.'</'.$key.'>';
            }elseif(is_array($val)){
                $xml .= '<'.$key.'>'.$this->arrayToXml($val, false).'</'.$key.'>';
            }else{
                $xml.='<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
            }
        }
        if($hf == true){
            return '<xml>'.$xml.'</xml>';
        }
        return $xml;
    }
    
    //将XML转为array
    public function xmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    
    /**
     * 被动回复用户消息
     * @param unknown $toUser
     * @param string $msg
     * @param string $fromUser
     * @return suc | err
     */
    public function reply_text($toUser, $msg='', $fromUser=''){
        return $this->_reply($toUser,['msg'=>$msg, 'type'=>'text'],$fromUser);
    }
    
    public function reply_image($toUser, $mediaId, $fromUser=''){
        return $this->_reply($toUser,['MediaId'=>$mediaId, 'type'=>'image'],$fromUser);
    }
    
    private function _reply($toUser, $param, $fromUser=''){
        if(empty($fromUser)) $fromUser = $this->account['account_id'];
        $data = [
            'ToUserName' => $toUser,
            'FromUserName' => $fromUser,
            'CreateTime'   => time(),
            'MsgType'      => $param['type'],
        ];
        if(empty($data['ToUserName'])){
            return err('ToUserName empty!');
        }elseif(empty($data['FromUserName'])){
            return err('FromUserName empty!');
        }
        if($param['type'] == 'text'){
            $data['Content'] = $param['msg'];
            if(empty($data['Content'])){
                return err('Content empty!');
            }
        }elseif('image' == $param['type']){
            $data['Image'] = ['MediaId'=>$param['MediaId']];
            if(empty($data['Image']['MediaId'])){
                return err('imageid empty');
            }
        }
        
        return suc($this->arrayToXml($data));
    }
    
   
    
    /**
     *  //自定义菜单查询接口
     *  @return errmsg | array
     */
    public function getMenu(){
        $token = $this->getAccess_token();
        if (false === ($token)) {
            return ('tokenerr:' . $this->getMessage());
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
        $response = j_http($url);
        if(is_suc($response)){
            $response = json_decode($response['msg'], true);
            if(!empty($response['menu'])){
                return $response;
            }else{
                return 'WX_ERR:'. $response['errmsg'];
            }
        }
        return 'WX_ERR:'.$response['msg'];
    }
    
    /**
     * 创建自定义菜单
     * @param string $json
     * @return string|true
     */
    public function createMenu($json="{}"){
        $token = $this->getAccess_token();
        if (false === ($token)) {
            return ('tokenerr:' . $this->getMessage());
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        
        $response = j_http($url, $json, array('Content-Type'=>'application/json;charset=UTF-8'), 10);
        if(is_suc($response)){
            $response = json_decode($response['msg'], true);
            if(empty($response['errcode'])){
                return true;
            }else{
                return 'WX_ERR: code:'. $response['errcode'].' msg:'.$response['errmsg'];
            }
        }
        return 'WX_ERR:'.$response['msg'];
        
        
    }
    
    //获取粉丝列表
    public function getUsers($next_openid=''){
        $token = $this->getAccess_token();
        if (false === ($token)) {
            return err('tokenerr:' . $this->getMessage());
        }
        
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token ;
        if(!empty($next_openid)){
            $url .= '&next_openid='.$next_openid;
        }
        $response = j_http($url);
        if(is_suc($response)){
            $response = json_decode($response['msg'], true);
            if(empty($response['errcode'])){
                return suc($response);
            }else{
                return err('WX_ERR: code:'. $response['errcode'].' msg:'.$response['errmsg']);
            }
        }
        return err('WX_ERR:'.$response['msg']);
    }
    //获取粉丝详细
    public function getUsersDetail($json){
        $token = $this->getAccess_token();
        if (false === ($token)) {
            return err('tokenerr:' . $this->getMessage());
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token='.$token;
        $response = j_http($url, $json, array('Content-Type'=>'application/json;charset=UTF-8'), 10);
        if(is_suc($response)){
            $response = json_decode($response['msg'], true);
            if(empty($response['errcode'])){
                return suc($response);
            }else{
                return err('WX_ERR: code:'. $response['errcode'].' msg:'.$response['errmsg']);
            }
        }
        return err('WX_ERR:'.$response['msg']);
    }
    
    
    
    
    
    
}