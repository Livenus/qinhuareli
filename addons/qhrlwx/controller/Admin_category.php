<?php
namespace addons\qhrlwx\controller;

/**
 * @title 文章分类
 * @type menu
 *
 */

class Admin_category extends Admin_qhrlwx{
	/**
     * @title 分类列表
     * @type menu
     * @login 1
     */
    public function listAc(){

          return j_help()->handleList([
            'topbuttons'=>'qhrlwx/admin_category/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'qhrlwx/admin_category/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id$qhrlwx/admin_category/del#删除#glyphicon glyphicon-edit#btn btn-warning#del#id',
            'search'    => 'j.cate_name',
            'query'     => [
                'db_table' => 'qhrlwx_category',
                'db_fields' => 'j.id$j.cate_id$j.keyword$j.cate_name$j.sort$j.create_time',
                'db_where'    =>'j.id$j.cate_name#like#%#pcate_name#like3#string',
            ]
        ]);
                
    }


     /**
     * @title 添加分类
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        return j_help()->handleAdd([
            'table' => 'qhrlwx_category',
            'name'  => '添加分类',
            'formfields'=>'cate_id,keyword,cate_name,sort'
        ]);

    }

    /**
     * @title 修改分类
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        return j_help()->handleEdit([
            'table' => 'qhrlwx_category',
            'name'  => '修改分类',
            'formfields'=>'cate_id,keyword,cate_name,sort'
        ]);

    }

      /**
     * @title 删除分类
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrlwx_category']);
    }

    

}