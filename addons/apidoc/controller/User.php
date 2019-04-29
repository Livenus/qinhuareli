<?php 
namespace addons\apidoc\controller;
use think\Db;
/**
 * @title apidoc会员
 *
 */
class User extends \app\common\controller\Module_home{
    protected $logincode='index';
    
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * @title 欢迎页
     * @desc 一般用于登录成功后第一页(home页的页面)
     * @type menu
     * @menudisplay false
     * @icon fa fa-home
     * @method get
     */
    public function homeAc(){
        return 'welcome apidoc';
    }
    /**
     * @title 我参与的项目
     * @type menu
     * @icon fa fa-list
     */
    public function myprojectAc(){
        $memberinfo = $this->getLoginUserinfo();
        $member_id = $memberinfo['id'];

        $projlist = Db::table('__APIDOC_MEMBERPROJ__')->alias('j')->join('__APIDOC_PROJECT__ a','j.proj_id = a.id')
                    ->where('j.member_id',$member_id)
                    ->select();
        $this->assign('myprojlist', $projlist);
        return $this->fetch();
    }
}