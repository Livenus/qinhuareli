<?php 
namespace addons\apidoc\controller;
use think\Config;
use think\Db;
use phpDocumentor\Reflection\Project;

/**
 * @title 项目管理
 * @type menu
 * @icon fa fa-th-list
 */
class Admin_proj  extends \app\common\controller\Admin_home{
    
    public function _initialize(){
        parent::_initialize();
  
    }
    
    /**
     * @title 项目列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        
        return j_help()->handleList([
            'name' => '项目列表',
            'contrllername' => 'Admin_proj',
//             'actionname'    => 'proj',
//             'type'          => 'list',
//             'table'         => 'apidoc_project',
            'topbuttons'    => 'apidoc/admin_proj/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons' =>'apidoc/admin_proj/genapi#生成文档#glyphicon glyphicon-trash#btn btn-primary#add#id
                        $apidoc/admin_projdb/update#生成数据字典#glyphicon glyphicon-trash#btn btn-primary#add#id
                        $apidoc/admin_proj/del#删除#glyphicon glyphicon-trash#btn btn-primary#del#id
                        $apidoc/admin_proj/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id
                        $apidoc/admin_proj/editAuth#修改权限#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'query' => [
                'name' => '项目列表查询',
//                 'source' => 'db',
                'db_table'    => 'apidoc_project',
                'db_fields'   => 'j.id$j.title$j.url_develop$j.allow_search'
            ]
        ]);
        
//         Config::set('jhy_opid', 0);
// 		return action('jhy/Error/_empty', [$this->request]);
    }
    
    /**
     * @title 添加项目
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        
        return j_help()->handleAdd([
            'name' => '新增项目',
//             'contrllername' => 'Admin_proj',
//             'actionname'    => 'add',
//             'type'          => 'add',
            'table'         => 'apidoc_project',
        ]);
//         Config::set('jhy_opid', 1);
//         return action('jhy/Error/_empty', [$this->request]);
        
    }
    
    /**
     * @title 修改项目
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        return j_help()->handleEdit([
            'name' => '修改项目',
            //             'contrllername' => 'Admin_proj',
            //             'actionname'    => 'add',
            //             'type'          => 'add',
            'table'         => 'apidoc_project',
        ]);
//         Config::set('jhy_opid', 2);
//         return action('jhy/Error/_empty', [$this->request]);
    }


    /**
     * @title 项目权限
     * @type menu
     * @icon fa fa-list
     */
    public function authListAc(){
        $list = db('apidoc_project')->select();
        $this->assign('auth_list',$list);
        return $this->fetch();
    }


    /**
     * @title 修改权限
     * @type menu
     * @menudisplay false
     */
    public function authEditAc(){
        $projid = $this->request->param('id/d', 0);
        $index_db = db('index_user');
        $info = $index_db->alias('a')
                    ->field('a.nickname,a.id as index_id,j.*')
                    ->join('__APIDOC_MEMBERPROJ__ j', 'a.id=j.member_id')
                    ->where('j.proj_id',$projid)
                    ->select();

        foreach ($info as &$v) {
            if(!empty($v)){
                $v['dir_rules']= explode(',', $v['dir_rules']);
            }
            if(count($v['dir_rules']) > 3){
                $v['auth'] = true;
            }else{
                $v['auth'] = false;
            }
        }

        //查询当前项目无记录用户
        $user_k = array_column($info,'member_id');
        $v_info = $index_db->field('id as index_id,nickname')->where('id','not in',$user_k)->where('stat',1)->select();
        $auth_info = array_merge($v_info,$info);

        dump($auth_info);
        $this->assign('auth_info',$auth_info);
        return $this->fetch();
    }
    
    /**
     * @title 删除项目
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return $this->error('uncode');
    }
    
    /**
     * @title 自动扫描生成API
     * @type menu
     * @menudisplay false
     */
    public function genapiAc(){
        $projid = $this->request->param('pid/d', 0);
        
        $projinfo = j_model('apidoc_project')->find($projid);
        if(!$projinfo){
            return $this->error('项目未找到','','','sss');
        }
        
        $apipath = ROOT_PATH . '/' . $projinfo['api_path'];
        if(!is_dir($apipath)){
            return $this->error('目录不存在','','','ss');
        }
        $list = j_scanDocapi($apipath);
        
        $pordir_mod = j_model('apidoc_proj_dirs');
        $flag = 0;
        foreach($list as $k => $v){
            //项目目录是否存在
            $projderinfo = $pordir_mod->where('auto_md5', $k)->find();
            if(isset($projderinfo) && isset($projderinfo['id'])){
                
            }else{
                //新增目录
                $pordir_mod->insert([
                    'project_id' => $projinfo['id'],
                    'title'      => '[自]'.($v['commentarr']['title']?$v['commentarr']['title']:$v['commentarr']['__name']),
                    'auto_md5'   => $k,
                ]);
                $projderinfo = $pordir_mod->where('auto_md5', $k)->find();
            }
            if(!$projderinfo){
                return $this->error('项目目录错误');
            }
            
            //API
            $api_mod = j_model('apidoc_api');
            foreach($v['son'] as $kk => $vv){
                $apiinfo = $api_mod->where('auto_md5', $kk)->find();
                if(isset($apiinfo) && isset($apiinfo['id'])){
                    
                }else{
                    $url = $this->getInterfaceUrl($projinfo['api_path'],$v['commentarr']['__name'],$vv['__name']);
                    $api_mod->insert([
                        'dir_id' => $projderinfo['id'],
                        'title'      => '[自]'.($vv['title']?$vv['title']:$vv['__name']),
                        'method'     => $vv['method']?$vv['method']:'POST',
                        'uri'        => $vv['uri']?$vv['uri']:$url,
                        'intro'      => $vv['desc'],
                        'type'       => 'api',
                        'auto_md5'   => $kk
                        
                    ]);
                }
                $apiinfo = $api_mod->where('auto_md5', $kk)->find();

                //请求字段  
                if(isset($vv['param'])){
                    $res1 = $this->setfield($apiinfo['id'],$vv['param'],1);
                }
                //响应字段
                if(isset($vv['return'])){
                    $res2 = $this->setfield($apiinfo['id'],$vv['return'],2);
                }

                if($res1 === false || $res2 === false){
                    $flag++;
                    continue;
                }

            }
        }
        
        
        return $flag == 0 ? '扫描成功' : '扫描失败'.$falg;
    }

    /**
     * @title 自动扫描接口字段添加
     * @type menu
     * @menudisplay false
     */
    private function setfield($apiid,$param,$type){
        $apifield_mod = j_model('apidoc_apifield');
        $flag = true;
        foreach ($param as $v) {
            $str = preg_replace("/[\s]+/is"," ",$v);
            $field_info = explode(' ', $str);
            if(count($field_info) > 0){
                $name = $field_info[0];
                $apifieldinfo = $apifield_mod->where(['api_id' => $apiid,'name' => $name,'class' => $type])->find();
                if(isset($apifieldinfo) && isset($apifieldinfo['id'])){

                }else{
                    $r = $apifield_mod->insert([
                        'api_id'     => $apiid,
                        'user_id'      => 0,
                        'name'     => $name,
                        'title'        => '[自]'.(isset($field_info[2]) ? $field_info[2] : ''),
                        'class'      => $type,
                        'is_required'       => isset($field_info[3]) ? $field_info[3] : '',
                        'default_value'   => isset($field_info[1]) ? $field_info[1] : '',
                        'intro' => isset($field_info[4]) ? $field_info[4] : '',
                    ]);

                    if($r === false){
                        $flag = false;
                    }
                }
            }
        }

        return $flag;
    }

    /**
     * @title 生成接口路径
     * @type menu
     * @menudisplay false
     */
    private function getInterfaceUrl($m,$c,$a){

        if(empty($m) || empty($c) || empty($a)){
            return 'untitle';
        } 
        $url = '/';       
        $m_s = explode('/', $m);
        if(count($m_s) > 2 && $m_s[2] == 'controller'){
            $url .= $m_s[1] . '/';
        }else{
            return 'untitle';
        }

        $c_s = explode('_', $c);
        if(count($c_s) > 1){
            $url .= $c_s[0] . '/';
        }else{
            return 'untitle';
        }

        $Ac = substr($a,strlen($a)-2); 
        if($Ac == 'Ac'){
            $a = substr($a,0,strlen($a)-2); 
            $url .= $a . '.html';
        }else{
            return 'untitle';
        }
        return $url;
    }
    
}
