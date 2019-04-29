<?php 

namespace addons\apidoc\controller;

/**
 * @title 登录登出
 * @type menu
 * @menudisplay false
 * @author jhy
 *
 */
class Login extends \think\Controller{
    
    /**
     * @title 登录
     * @type menu
     * @menudisplay false
     * @return string
     */
    function loginAc(){
        if(j_isLogin('index') === true){
            $this->redirect('apidoc/index/index');
        }
        return   $this->view->fetch(j_view_login('index'),[],[],[],true);
    }
    
}