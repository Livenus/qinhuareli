<?php 
namespace app\admin\controller;

/**
 * @title 会员管理
 * @icon fa fa-user
 *
 */
class Member extends \app\common\controller\Admin_home{

    /**
     * @title 会员列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        
        return j_help()->handleList([
            'topbuttons'=>'admin/member/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'admin/member/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id$admin/member/setpwd#设置密码#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'search'    => '',
            'query'     => [
                'db_table' => 'index_user',
                'db_join'  => '__INDEX_USERGROUP__ a#a.id=j.group_id#left',
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
            if(db('index_user')->where('username', $this->request->param('username'))->count()>0){
                return $this->error('用户名存在');
            }
            $this->request->post(['salt'=>j_radomstr(6)]);
        }
        
        return j_help()->handleAdd([
            'table' => 'index_user',
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
            'table' => 'index_user',
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
        $uinfo = db('index_user')->find($id);
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
            'table' => 'index_user',
            'name'  => '设置会员【'.$uinfo['username']. ($uinfo['nickname']?'('.$uinfo['nickname'].')':'') . '】密码',
            'formfields'=>'password',
            'values' => [
                'password' => ''
            ]
        ]);
    }
    
    /**
     * @title 会员组列表
     * @icon fa fa-users
     * @type menu
     * 
     */
    public function membergrouplistAc(){
        
        return j_help()->handleList([
            'topbuttons'=>'admin/member/membergroupadd#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'admin/member/membergroupedit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'search'    => '',
            'query'     => [
                'db_table' => 'index_usergroup',
                'db_join'  => '',
                'db_fields' => 'j.id$j.name',
                'db_where' => ''
            ]
        ]);
        return 'uncode';
    }
    
    /**
     * @title 会员组添加
     * @type menu
     * @menudisplay false
     */
    public function membergroupaddAc(){
        if($this->request->isPost()){
            if(db('index_usergroup')->where('name', $this->request->param('name'))->count()>0){
                return $this->error('组名存在');
            }
            
        }
    
        return j_help()->handleAdd([
            'table' => 'index_usergroup',
            'name'  => '添加管理员组',
            'formfields'=>'id,name'
        ], $this->request);
    }
    /**
     * @title 修改会员
     * @type menu
     * @menudisplay false
     */
    public function membergroupeditAc(){
        return j_help()->handleEdit([
            'table' => 'index_usergroup',
            'name'  => '修改会员组',
            'formfields'=>'id,name'
        ]);
    
    }

}