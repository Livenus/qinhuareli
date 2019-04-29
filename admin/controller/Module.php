<?php 

namespace app\admin\controller;

/**
 * @title 插件管理
 * @type menu
 * @icon fa fa-plug
 *
 */
class Module  extends \app\common\controller\Admin_home{
    
    /**
     * @title 插件列表
     * @icon fa fa-list
     * @type menu
     */
    public function listAc(){
        
        if($this->request->isPost()){
            $_modules = j_model('admin_modules')->select();
            $modules = [];
            foreach($_modules as $k=>$v){
                if(!in_array($v['en_name'], ['admin','jhy','wx','index'])){
                    $modules[$v['en_name']] = $v->getData();
                }
            }

            $addons = scandir(ROOT_PATH . '/addons/');
            if(isset($addons) && !empty($addons)){
                foreach($addons as $k => $v){
                    if($v != '.' && $v != '..' && !array_key_exists($v, $modules)){
                        $minfo = j_getaddoninfo($v);

                        $modules[$v] = [
                            'name' => $minfo['name'],
                            'en_name' => $v,
                            'remark' => '',
                            'stat' => 'uninstall'
                        ];
                        
                    }
                }
            }
            
            return $this->success('','',array_values($modules));
        }
        
        return $this->fetch();
    }
    
    /**
     * @title 安装一个插件
     * @type menu
     * @menudisplay false
     * 
     */
    public function installAc(){
        $name = $this->request->post('name/s');
        $mod = j_model('admin_modules');
        if($mod->where('en_name', $name)->count()>0){
            return $this->error('插件已安装');
        }
        if(isset($name) && !empty($name)){
            //安装SQL            
            if(($addoninfo = j_installaddon($name)) !== true){
                return $this->error($addoninfo);
            }
            
            //
            $addoninfo =j_getaddoninfo($name);
            $mod->insert(['en_name'=>$name, 'stat'=>'1', 'name'=>$addoninfo['name']]);

            return $this->success();
            
            
        }
        return $this->error('参数错');
    }
    /**
     * @title 卸载一个插件
     * @type menu
     * @menudisplay false
     *
     */
    public function uninstallAc(){
        $name = $this->request->post('name/s');
        if(isset($name) && !empty($name)){
        
            if(($addoninfo = j_uninstalladdon($name)) !== true){
                return $this->error($addoninfo);
            }
            $mod = j_model('admin_modules');
            $mod->where(['en_name'=>$name])->delete();
            return $this->success();
        
        
        }
        return $this->error('参数错');
    }
    /**
     * @title 禁用一个插件
     * @type menu
     * @menudisplay false
     *
     */
    public function stat0Ac(){
        $name = $this->request->post('name/s');
        if(isset($name) && !empty($name)){
            $mod = j_model('admin_modules');
            $mod->where(['en_name'=>$name])->update(['stat'=>0]);
            return $this->success();
        }
        return $this->error('参数错');
    }
    /**
     * @title 启用一个插件
     * @type menu
     * @menudisplay false
     *
     */
    public function stat1Ac(){
        $name = $this->request->post('name/s');
        if(isset($name) && !empty($name)){
            $mod = j_model('admin_modules');
            $mod->where(['en_name'=>$name])->update(['stat'=>1]);
            return $this->success();
        }
        return $this->error('参数错');
    }
    
    
}