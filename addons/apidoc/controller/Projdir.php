<?php
namespace addons\apidoc\controller;
use think\Config;
/**
 * @title 项目
 *
 */
class Projdir extends \app\common\controller\Module_home{
    protected $proj_id = 0;
    protected $logincode='index';
    
    public function _initialize(){
        parent::_initialize();
        if(($msg = $this->isLogin()) !== true){
            return $this->error(is_string($msg)?$msg:'未登录！');
        }
        $this->proj_id = $this->request->param('projid/d');
        if(!$this->proj_id){
            return $this->error('出错了！');
        }
    }
    
    /**
     * @title 项目目录列表及其API，并分级
     * @method post 
     * @uri  /index.php?s=apidoc/projdir/list
     * @param projid 项目ID
     *   不能为空
     * @param id sss
     *   ssssss
     * @type interface
     */
    public function listAc(){

        $mod = j_model('apidoc_proj_dirs');
        $dirs = $mod->where('project_id', $this->proj_id)->field('id')->select()->toArray();
        $dirids = array_column($dirs, 'id');

        //API
        $apilist = j_model('apidoc_api')->where('dir_id', ['in', $dirids])->select()->toArray();
        
        //目录
        $info = $mod->where('project_id', $this->proj_id)->select()->toArray();
        $_info = [];
        foreach($info as $k => $v){
            $_info[$v['id']] = $v;
        }
        $info = $_info;
        
        foreach($apilist as $k => $v){
            if(isset($info[$v['dir_id']])){
                $info[$v['dir_id']]['son'][] = $v;
                unset($apilist[$k]);
            }
        }
        $this->success('','',array_values($info));
    }
    
    /**
     * @title 查看一个项目
     * @type menu
     * @menudisplay false
     */
    public function viewAc(){
        
        $projinfo = j_model('apidoc_project')->find($this->proj_id);
        $this->assign('projinfo', $projinfo);
        
        return $this->fetch();
    }
    /**
     * @title 添加一个目录
     * @type menu|interface
     * @menudisplay false
     */
    public function adddirAc(){
        if($this->request->isPost()){
            $title = $this->request->param('title');
            if(isset($title) && !empty($title)){
                $mod = j_model('apidoc_proj_dirs');
                if($mod->where('project_id', $this->proj_id)->where('title', $title)->count() > 0){
                    return $this->error('目录名已存在!');
                }
                
                $uinfo = $this->getLoginUserinfo();
                $data = [
                    'title' => $title,
                    'user_id' => $uinfo['id'],
                    'sort'    => $this->request->param('sort/d', 0),
                    'project_id' => $this->proj_id
                ];
                j_model('apidoc_proj_dirs')->insert($data);
                return $this->success();
            }
            
            return $this->error('目录名称不能为空');            
        }
             
        return $this->fetch(); 
    }
    /**
     * @title 修改一个目录
     * @type menu
     * @menudisplay false
     */
    public function editdirAc(){
        $id = $this->request->param('id/d', 0);
        $mod = j_model('apidoc_proj_dirs');
        $dirinfo = $mod->find($id);
        if($this->request->isPost()){
            $title = $this->request->param('title');
            if(isset($title) && !empty($title)){
                if($mod->where('project_id', $this->proj_id)->where('id',['<>',$id])->where('title', $title)->count() > 0){
                    return $this->error('目录名已存在!');
                }
        
                $uinfo = $this->getLoginUserinfo();
                $data = [
                    'title' => $title,
                    'user_id' => $uinfo['id'],
                    'sort'    => $this->request->param('sort/d', 0),
                    'project_id' => $this->proj_id
                ];
                j_model('apidoc_proj_dirs')->where('id', $id)->update($data);
                return $this->success();
            }
        
            return $this->error('目录名称不能为空');
        }
        $this->assign('dirinfo', $dirinfo);
        return $this->fetch('user/Projdir_adddir');
    }
    
    /**
     * @title 删除一个目录
     * @type interface
     * @menudisplay false
     */
    public function delAc(){
        $id = $this->request->param('id/d');
        if($id > 0){
            $count = j_model('apidoc_api')->where('dir_id', $id)->count();
            if($count > 0){
                return $this->error('目录下的'.$count.'个API删除后才能删除此目录');
            }
            j_model('apidoc_proj_dirs')->where('id', $id)->delete();
            return $this->success();
        }
        return $this->error('参数错误');
    }
    
    /**
     * @title 数据字典
     * @type interface
     */
    public function dbddAc(){
        $projectid = $this->request->param('projid/d', 0);
        if(!!($ddinfo = j_model('apidoc_projectdb')->find($projectid))){
            $prefix = '';
            $ddinfo['content'] = $prefix. $ddinfo['content'] ;
        }
        
        return $this->success('','',$ddinfo);
    }


    /**
     * @title 删除接口
     * @type menu
     * @menudisplay false
     */
    public function delinterAc(){
        //项目编号
        $projid = $this->request->param('projid/d', 0);   
        //接口编号
        $id     = $this->request->param('id/d', 0);

        $api_mod = db('apidoc_api');
        $field_mod = db('apidoc_apifield');
        $api    = $api_mod->alias('j')
                    ->field('a.project_id')
                    ->join('__APIDOC_PROJ_DIRS__ a', 'a.id=j.dir_id')
                    ->where(['a.project_id' => $projid,'j.id' => $id])
                    ->find();
        if(empty($api)){
            return $this->error('接口不存在！');
        }else{
            $api_mod->startTrans();
            try{
                $res1 = $api_mod->where('id','eq',$id)->delete();
                if($field_mod->where('api_id','eq',$id)->count() > 0){
                    $res2 = $field_mod->where('api_id','eq',$id)->delete();
                }else{
                    $res2 = true;
                }

                if($res1 !== false && $res2 !== false){
                    $api_mod->commit();
                    return $this->success('删除成功！');
                }else{
                    $api_mod->rollback();
                    return $this->error('删除错误！');
                }
                
            }catch(\think\Exception $e){
                $api_mod->rollback();
                return $this->error('删除报错！');
            }
        }
        
    }
    
}