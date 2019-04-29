<?php
namespace app\admin\controller;
/**
 * @title 登录登出
 * @type menu
 * @menudisplay false
 * @author jhy
 *
 */
class Login extends \app\common\controller\Home{
    protected $logincode = 'admin';
    /**
     * @title 登录
     * @type menu
     * @login 0
     * @menudisplay false
     * @return string
     */
    public function loginAc(){
        if(($errmsg = $this->isLogin()) === true){
            $this->redirect('admin/index/index');
        }
        //获取登录信息
        $loginsysinfo = j_model('jhy_login')->field(['name', 'code'])->where('code', $this->logincode)->find();
        
        $this->assign('errmsg', is_string($errmsg)?$errmsg:'');
        $this->assign('loginsysinfo', $loginsysinfo);
        
        return   $this->view->fetch(j_view_login($this->logincode),[],[],[],true);
    }
}