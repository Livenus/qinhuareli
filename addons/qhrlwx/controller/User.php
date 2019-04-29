<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 用户
 * @type menu
 * @menudisplay false
 *
 */
class User extends Home{
    /**
     * @title 用户信息查询
     * @type interface
     * @login 1
     * @return userinfo   登录用户信息  array   1  整体返回没userinfo键
     */
    public function getUserInfoAc(){
        $user = $this->getLoginUserinfo();
        if(empty($user)){
            $this->nologin('未登录！');
        }else{
            $this->suc($user);
        }
    }
}