<?php 
namespace addons\apidoc\controller;
use think\Db;
use think\Curl;
/**
 * @title 接口调试
 * @type menu
 * @menudisplay false
 *
 */
class Debug extends \app\common\controller\Module_home{
    protected $logincode='index';
    protected $apiid = 0;
    
    public function _initialize(){
        parent::_initialize();
        $this->apiid = $this->request->param("apiid/d");
        if(!$this->apiid){
            return $this->error('出错了！');
        }
    }
    /**
     * @title 接口调试页面
     * @desc 接口调试页面
     * @type menu
     * @login 0
     * @menudisplay false
     * @icon fa fa-home
     * @method get
     */
    public function indexAc(){

        $api = db('apidoc_api')->alias('j')->field('j.*,a.title as dirname')->join('__APIDOC_PROJ_DIRS__ a', 'a.id=j.dir_id')->find($this->apiid);
        if(empty($api)){
            return $this->error("接口不存在！");
        }
        $field_info = Db::table('__APIDOC_APIFIELD__')->where('api_id',$this->apiid)->select();

        $request = $response = $header = [];
        if(!empty($field_info)){

            foreach ($field_info as &$v) {
                if($v['is_required'] == 1){
                    $v['required'] = '是';
                }else{
                    $v['required'] = '否';
                }

                //1=请求字段,2=响应字段,3=header字段
                if($v['class'] == 1){
                    $request[] = $v; 
                }else if($v['class'] == 2){
                    $response[] = $v; 
                }else if($v['class'] == 3){
                    $header[] = $v; 
                }
            }

        }

        if(stripos($api['uri'], 'http') === false){
            if(stripos($api['uri'], '/') != 0){
                $api['uri'] = 'http://'.$_SERVER["HTTP_HOST"] . '/' . $api['uri'];
            }else{
                $api['uri'] = 'http://'.$_SERVER["HTTP_HOST"] . $api['uri'];
            }
        }

        if(empty($request)){
            $request['_empty'] = true;
        }

        if(empty($response)){
            $response['_empty'] = true;
        }

        if(empty($header)){
            $header['_empty'] = true;
        }

        $this->assign('api', $api);
        $this->assign('request', $request);
        $this->assign('response', $response);
        $this->assign('header', $header);

        return $this->fetch();
    }
    /**
     * @title 调试页面
     * @desc 调试页面
     * @type interface
     * @login 0
     * @menudisplay false
     * @icon fa fa-home
     * @method get
     * @param get1
     * @return get2
     */
    public function debugAc(){

        $this->apiid = $this->request->param('apiid/d');
        if(!$this->apiid){
            return $this->error('出错了！');
        }

        $api = Db::table('__APIDOC_API__')->find($this->apiid);
        if(empty($api)){
            return $this->error('接口不存在！');
        }
        $data = $this->request->param();
        if(!isset($data['url']) || !$url = $data['url']){
            return $this->error('请填写接口路径！');
        }

        if(!isset($data['method']) || !$method = strtoupper($data['method'])){
            return $this->error('请填写正确请求方式！');
        }
        $method_arr = ['POST','GET','PUT'];
        if(!in_array($method, $method_arr)){
            return $this->error('请填写正确请求方式！');
        }

        $request = isset($data['request']) ? $data['request'] : '';
        $header  = isset($data['header']) ? $data['header'] : '';
        $headers = [];
        foreach ($header as $k => $v) {
            $headers[] = $k.':'.$v;
        }

        $curl = new \ext\jhy\Curl($url, $method, $request,$headers);

        $info = $curl->getInfo();
        $getbody = $curl->getBody();
        $body = json_decode($getbody,true);
        if(empty($body)){
            $setbody['data'] = $getbody;
            $setbody['status'] = 1;
        }else{
            $setbody['data'] = $body;
            $setbody['status'] = 0;
        }
        $header = $curl->getHeader();
        return $this->success('','',['info' => $info,'body' => $setbody,'header' => $header]);
    }
}