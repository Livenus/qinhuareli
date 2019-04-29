<?php
namespace app\admin\controller;

/**
 * @title 首页
 * @type menu
 * @menudisplay false
 *
 */
class Index extends \app\common\controller\Home{
    protected $logincode = 'admin';
    /**
     * @title首页
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function indexAc(){
        if($this->isLogin() !== true){
            $this->redirect('admin/login/login');
        }
                        //获取登录信息
        $loginsysinfo = j_model('jhy_login')->field(['name', 'code'])->where('code', $this->logincode)->find();
        $this->assign('loginsysinfo', $loginsysinfo);
        return   $this->view->fetch(j_view_home($this->logincode),[],[],[],true);
    }
}