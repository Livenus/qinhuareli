<?php 
namespace app\admin\validate;

use think\Validate;

class proj extends Validate{
    protected $rule = [
        'name'  =>  'require',
        'code'  =>  'require|unique:proj'

    ];
    protected $message = [
   
    ];
    
    protected $scene = [
        'add'   =>  [
            'name'=>'require', 
            'code'=>'require|unique:proj', 

        ],
        'edit'  =>  [
            'name'=>'require',
            'code'=>''
        ],

    ];




 
}