<?php 
namespace addons\apidoc\controller;

/**
 * @title 首页
 * @type menu
 * @menudisplay false
 *
 */
class Index extends \think\Controller{
    /**
     * @title首页
     * @type menu
     * @menudisplay false
     */
    public function indexAc(){
        if(j_isLogin('index') !== true){
            $this->redirect('apidoc/login/login');
        }
        return   $this->view->fetch(j_view_home('apidoc'),[],[],[],true);
    }
}
