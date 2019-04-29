<?php
namespace app\admin\controller;
/**
 * @title 管理员
 * @type menu
 * @icon fa fa-user
 */
class User extends \app\common\controller\Admin_home{
    
    /**
     * @title 首页
     * @desc 一般用于登录成功后第一页(home页的页面)
     * @type menu
     * @menudisplay false
     * @icon fa fa-home
     * @method get
     */
    public function homeAc(){
        return 'welcome admin';
    }
    
    /**
     * @title 管理员列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){

        
        return j_help()->handleList([
            'topbuttons'=>'admin/user/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'admin/user/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id$admin/user/setpwd#设置密码#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'search'    => '',
            'query'     => [
                'db_table' => 'admin_admin',
                'db_join'  => '__ADMIN_ADMINGROUP__ a#a.id=j.group_id#left',
                'db_fields' => 'j.id$j.username$a.name#gname$j.nickname$j.email$j.reg_ip$j.stat',
                'db_where' => ''
            ]
        ]);
        
    
    }
    
    /**
     * @title 添加会员
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        if($this->request->isPost()){
            if(db('admin_admin')->where('username', $this->request->param('username'))->count()>0){
                return $this->error('用户名存在');
            }
            $this->request->post(['salt'=>j_radomstr(6)]);
        }
    
        return j_help()->handleAdd([
            'table' => 'admin_admin',
            'name'  => '添加会员',
            'formfields'=>'group_id,username,nickname,stat'
        ], $this->request);
    }
    
    /**
     * @title 修改会员
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        return j_help()->handleEdit([
            'table' => 'admin_admin',
            'name'  => '修改会员',
            'formfields'=>'group_id,username,nickname,stat'
        ]);
    
    }
    
    /**
     * @title 设置密码
     * @type menu
     * @menudisplay false
     */
    public function setpwdAc(){
        $id = $this->request->param('id');
        $uinfo = db('admin_admin')->find($id);
        if($this->request->isPost()){
            if(($password = $this->request->param('password/s', '')) == ''){
                return $this->error('密码不能为空');
            }
            $data = [
                'password' => j_encodepassword($password, $uinfo['salt'])
            ];
            $this->request->post($data);
    
        }
        return j_help()->handleEdit([
            'table' => 'admin_admin',
            'name'  => '设置管理员【'.$uinfo['username']. ($uinfo['nickname']?'('.$uinfo['nickname'].')':'') . '】密码',
            'formfields'=>'password',
            'values' => [
                'password' => ''
            ]
        ]);
    }
    /**
     * @title 管理员组列表
     * @icon fa fa-users
     * @type menu
     * 
     */
    public function membergrouplistAc(){
        
        return j_help()->handleList([
            'topbuttons'=>'admin/user/membergroupadd#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'admin/user/membergroupedit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'search'    => '',
            'query'     => [
                'db_table' => 'admin_admingroup',
                'db_join'  => '',
                'db_fields' => 'j.id$j.name',
                'db_where' => ''
            ]
        ]);
        return 'uncode';
    }
    
    /**
     * @title 添加管理员组
     * @type menu
     * @menudisplay false
     */
    public function membergroupaddAc(){
        if($this->request->isPost()){
            if(db('admin_admingroup')->where('name', $this->request->param('name'))->count()>0){
                return $this->error('组名存在');
            }
            
        }
    
        return j_help()->handleAdd([
            'table' => 'admin_admingroup',
            'name'  => '添加管理员组',
            'formfields'=>'id,name'
        ], $this->request);
    }
    /**
     * @title 修改管理员组
     * @type menu
     * @menudisplay false
     */
    public function membergroupeditAc(){
        return j_help()->handleEdit([
            'table' => 'admin_admingroup',
            'name'  => '修改会员组',
            'formfields'=>'id,name'
        ]);
    
    }
}