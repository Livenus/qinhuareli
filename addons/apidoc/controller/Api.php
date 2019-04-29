<?php 
namespace addons\apidoc\controller;
use think\Config;
/**
 * @title API管理
 *
 */
class Api extends \app\common\controller\Module_home{
    protected $logincode='index';
    public function _initialize(){
        parent::_initialize();
        if(($msg = $this->isLogin()) !== true){
            return $this->error(is_string($msg)?$msg:'未登录！');
        }

    }
    
    /**
     * @title 新增API
     * @desc 
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $id = $this->request->param('id/d', 0); //目录ID
        $dirinfo = db('apidoc_proj_dirs')->where('id', $id)->find();
        if(!isset($dirinfo)){
            return $this->error('目录不存在');
        }
        $dirname = $dirinfo['title'];
        
        if($this->request->isPost()){
            $uinfo = $this->getLoginUserinfo();
            $data = [
                'title' => $this->request->param('title'),
                'method' => $this->request->param('method'),
                'uri' => $this->request->param('uri'),
                'intro' => $this->request->param('intro'),
                'sort' => $this->request->param('sort/d'),
                'user_id' =>$uinfo['id'],
                'dir_id' => $id,
                'type' =>$this->request->param('type')                
            ];
            j_model('apidoc_api')->insert($data);
            return $this->success();
        }
        
        
        return j_help()->handleAdd([
                'name' => '新增API',
                'table'         => 'apidoc_api',
                'formfields'     => 'dir_id,type,title,method,uri,sort,intro'
            
        ]);

       
        $this->assign('dirname', $dirname);
        return $this->fetch();
    }
    /**
     * @title 修改API
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $id = $this->request->param('id/d', 0);
        $apiinfo = j_model('apidoc_api')->find($id);
        if($this->request->isPost()){
            $dirid = $this->request->param('dir_id/d');
            $dirinfo = db('apidoc_proj_dirs')->where('id', $dirid)->find();
            if(!isset($dirinfo)){
                return $this->error('目录不存在');
            }

            $apiinfo = $apiinfo->getData();
            if(strtolower($apiinfo['type']) == 'api'){
                $data = [
                    'title' => $this->request->param('title'),
                    'method' => $this->request->param('method'),
                    'uri' => $this->request->param('uri'),
                    'intro' => $this->request->param('intro'),
                    'sort' => $this->request->param('sort/d'),
                    'dir_id' => $dirid
                ];
            }elseif(strtolower($apiinfo['type']) == 'doc'){
                $data = [
                    'title' => $this->request->param('title'),
                    'doc_content' => $this->request->param('doc_content'),
                    'sort' => $this->request->param('sort/d'),
                    'dir_id' => $dirid
                ];
            }
            j_model('apidoc_api')->where('id',$id)->update($data);
            return $this->success();
        }
        
        
        if(strtolower($apiinfo['type']) == 'api'){
            $fields = 'dir_id,title,method,uri,sort,intro';
        }elseif(strtolower($apiinfo['type']) == 'doc'){
            $fields = 'dir_id,sort,title,doc_content';
        }
        
        return j_help()->handleEdit([
            'name'    => '修改API',
            'table'         => 'apidoc_api',
            'formfields'     => $fields
            
        ]);
        
    }
    
    
    /**
     * @title API/doc查看
     * @desc 返回一个API内容
     * @type interface
     */
    public function viewAc(){
        //APIid
        $api_id = $this->request->param('id/d', 0);
        
        $apiinfo = db('apidoc_api')->alias('j')->join('__APIDOC_PROJ_DIRS__ a', 'a.id=j.dir_id')->field('j.*,a.title as dirname')->find($api_id);

        $apiinfo['method'] = strtoupper($apiinfo['method']);
        $apiinfo['intro']  = empty($apiinfo['intro']) ? '描述为空' : $apiinfo['intro'];
        $return['apiinfo'] = $apiinfo;
        if($return['apiinfo']['type'] == 'api'){
            $apifields = j_model('apidoc_apifield')->where('api_id', $api_id)->select()->toArray();
            foreach($apifields as $k => $v){
             
                $fields[$v['class']][] = $v;
                unset($apifields[$k]);
            }

            $return['field']['1'] = $fields['1'];
            $return['field']['2'] = $fields['2'];
            $return['field']['3'] = $fields['3'];
        }
        return $this->success('','',$return);
    }
    /**
     * @title 新增一个字段
     * @type menu
     * @menudisplay false
     */
    public function addfieldAc(){
        $apiid = $this->request->param('apiid/d', 0);
        $name = $this->request->param('name', 0);
        $class = $this->request->param('class/d');
        if($apiid <= 0) return $this->error('参数错(apiid)');
        if($this->request->isPost()){
            if(j_model('apidoc_apifield')->where('name', $name)->where('api_id', $apiid)->where('class',$class)->count()>0){
                return $this->error($name.' 参数已存在');
            }
            $uinfo = $this->getLoginUserinfo();
            $data = [
                'api_id' => $apiid,
                'user_id' => $uinfo['id'],
                'name'    => $name,
                'title'   => $this->request->param('title'),
                'class'  => $class,
                'is_required'  => $this->request->param('is_required/d'),
                'default_value'  => $this->request->param('default_value'),
                'intro'  => $this->request->param('intro')
            ];
            j_model('apidoc_apifield')->insert($data);
            return $this->success();
        }
        
        return j_help()->handleAdd([
                'name' => '新增字段',
                'table'         => 'apidoc_apifield',
                'formfields'     => 'name,title,class,is_required,default_value,intro'
        ]);

   
    }
    
    /**
     * @title 修改一个字段
     * @type menu 
     * @menudisplay false
     */
    public function editfieldAc(){

        $id = $this->request->param('id/d');
        
        $name = $this->request->param('name', 0);
        $class = $this->request->param('class/d');
 
        if($this->request->isPost()){
            if(j_model('apidoc_apifield')->where('name', $name)->where('api_id', $apiid)->where('class',$class)->where('id','<>', $id)->count()>0){
                return $this->error($name.' 参数已存在');
            }
            $uinfo = $this->getLoginUserinfo();
            $data = [
 
                'user_id' => $uinfo['id'],
                'name'    => $name,
                'title'   => $this->request->param('title'),
                'class'  => $class,
                'is_required'  => $this->request->param('is_required/d'),
                'default_value'  => $this->request->param('default_value'),
                'intro'  => $this->request->param('intro')
            ];
            j_model('apidoc_apifield')->where('id',$id)->update($data);
            return $this->success();
        }
        
        return j_help()->handleEdit([
            'name'          => '修改字段',
            'table'         => 'apidoc_apifield',
            'formfields'     => 'name,title,class,is_required,default_value,intro'
        ]);
        /*
        Config::set('jhy_opinfo', [
            '0'=>[
            ]
        ]);
        Config::set('jhy_opid', 0);
        return action('jhy/Error/_empty', [$this->request]);
        */
        
    }
    
}