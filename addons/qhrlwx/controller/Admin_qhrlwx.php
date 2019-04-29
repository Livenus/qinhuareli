<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 后台
 * @type menu
 * @menudisplay false
 */
class Admin_qhrlwx  extends \app\common\controller\Admin_home{

    protected $interweb     = 'http://localhost:8087/index.php/apis/api/index';
    protected $interkey     = '123456';
    protected $intersecret  = 'abcd';

    /**
     * @title 后台成功返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function a_suc($msg,$data,$url,$wait){
        $rdata['code'] = 1;
        $rdata['msg']  = $msg;
        $rdata['data']  = $data;
        $rdata['url']  = $url;
        $rdata['wait']  = $wait;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @title 后台失败返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function a_err($msg,$data,$url,$wait){
        $rdata['code'] = 0;
        $rdata['msg']  = $msg;
        $rdata['data']  = $data;
        $rdata['url']  = $url;
        $rdata['wait']  = $wait;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }


    /**
     * @title请求远程数据
     * @type menu
     * @login 1
     * @menudisplay false
     */
    protected function get_far_data($postData,$url){

        if(empty($url) || strpos('s'.$url, '/n/') != 1 || strpos($url, '/f/') < 4){
            return false;
        }
        $url = $this->interweb . $url;

        $key = [
            'time' => $this->request->time(),
            'key' => $this->interkey
        ];

        $postData = array_merge($postData,$key);       
        $postData['sign'] = $this->get_sign($postData,$this->intersecret);

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

        $sign = $r['sign'];
        unset($r['sign']);
        $data = $r;

        $data['data'] = base64_encode(is_string($data['data'])?$data['data']:json_encode($data['data'], JSON_UNESCAPED_UNICODE));
        $check_sign = $this->get_sign($data,$key['time']);

        if($check_sign === $sign){
            return $r['data'];
        }else{
            return false;
        }
      
    }

    private function get_sign($postData,$secret){
        ksort($postData);
        $signContent = "";
        
        foreach ($postData as $k => $v) {
            if (isset($v) && !empty($v)) {
                $signContent .= (string)($k) . '=' . (string)($v) . '&';
            }
        }
        $signContent  .= 'sign=' . $secret;   
        return md5($signContent);
    }

    /**
     * @title请求特定小区列表
     * @type menu
     * @login 1
     * @menudisplay false
     * @param ids 小区id数组
     * @return list 小区列表
     */
    protected function get_community_list($ids,$area_id){
        if((empty($ids) || (!empty($ids) && !is_array($ids))) && empty($area_id)){
            return false;
        }
        if(!empty($ids)){
            foreach ($ids as &$value) {
                if(!empty($value)){
                    unset($value);
                }
            }
        }
        //请求远程数据
        if(is_array($ids)){
            $data['communitys'] = implode(',', $ids);
        }
        if(!empty($area_id)){
            $data['area_id'] = $area_id;
        }

        $url  =  '/n/qhrl/f/get_community';
        $r = $this->get_far_data($data,$url);

        if($r['stat'] == -1){
            return false;
        }else{
            return array_column($r['data'],null,'id');
        }
        
    }

    /**
     * @title请求房屋信息
     * @type menu
     * @login 1
     * @menudisplay false
     * @param $ids arr
     * @param $stat  true返回信息，false返回是否有这个信息
     */
    protected function _get_house_info($ids,$stat=true){
        if(empty($ids) || !is_array($ids)){
            return false;
        }

        $data['ids']  = implode(',', $ids);
        $data['stat'] = $stat;
        //请求远程数据
        $url  =  '/n/qhrl/f/get_house_info';
        $r = $this->get_far_data($data,$url);

        if($r['stat'] == -1){
            return false;
        }else{
            return $r['data'];
        }
    }

}