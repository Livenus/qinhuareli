<?php
namespace addons\qhrlwx\controller;

/**
 * @title 留言
 * @type menu
 *
 */

class Admin_message extends Admin_qhrlwx{
	/**
     * @title 用户留言
     * @type menu
     * @login 1
     */
    public function listAc(){
    	 
         return j_help()->handleList([
            'name' => '留言列表',
            'contrllername' => 'Admin_message',
            'search'    => 'j.username',
            'query'     => [
                'db_table' => 'qhrlwx_message',
                'db_fields' => 'j.username$j.user_id$j.content$j.create_time',
                'db_where'    =>'j.username#like#%#pusername#like3#string',
                'db_order'    => 'create_time#desc',

            ]
        ]);
        
    }

}