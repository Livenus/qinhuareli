<?php 

namespace app\admin\Controller;

/**
 * @title 定时任务管理
 * @icon fa fa-hourglass
 */
class Cronmanage extends \app\common\controller\Admin_home{
    
    /**
     * @title 定时任务列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        
        return j_help()->handleList([
//            'topbuttons'=>'admin/member/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'admin/cronmanage/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id',
            'search'    => '',
            'query'     => [
                'db_table' => 'admin_cron',
                'db_join'  => '',
                'db_fields' => '',
                'db_where' => ''
            ]
        ]);
    }
    /**
     * @title  修改
     * @type menu
     * @menudisplay false
     * @icon fa fa-edit
     */
    public function editAc(){
        return j_help()->handleEdit([
            'table' => 'admin_cron',
            'name'  => '修改定时任务',
            'formfields'=>''
        ]);
    }
    
}