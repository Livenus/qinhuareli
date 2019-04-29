<?php 

namespace app\admin\Controller;

/**
 * @title 附件管理
 * @icon fa fa-file
 *
 */
class Attachment extends Home {
    
    /**
     * @title 图片管理
     * @icon  fa fa-file-image-o
     */
    public function imgAc(){
        
        return j_help()->handleList([
            'query' => [
                'db_where' => 'j.is_img#=#yes',
                'db_table' => 'jhy_upload'
            ],
            'fieldsconfig' => [
                'path' => [
                    'T'=>'image'
                ]
            ]
        ]);
        
        return 'img';
    }
    
    /**
     * @title 文件管理
     * @icon fa fa-file
     */
    public function fileAc(){
        
        return j_help()->handleList([
            'query' => [
                'db_where' => 'j.is_img#=#no',
                'db_table' => 'jhy_upload'
                
            ]
        ]);
        
        return 'img';
    }
    
}