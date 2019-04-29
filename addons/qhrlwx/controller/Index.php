<?php
namespace addons\qhrlwx\controller;

/**
 * @title 首页
 * @type menu
 * @menudisplay false
 *
 */
class Index extends \app\common\controller\Home{

    protected $logincode = 'qhrlwx';

    /**
     * @title首页
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function indexAc(){

        if($this->isLogin() !== true){
            $this->redirect('qhrlwx/login/login');
        }

        return   $this->view->fetch(j_view_home($this->logincode),[],[],[],true);

    }
}