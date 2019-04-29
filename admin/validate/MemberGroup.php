<?php 
namespace app\admin\validate;

use think\Validate;

class MemberGroup extends Validate{
    protected $rule =   [
        'name'  => 'require|min:5|max:20|unique:member_group'
    ];
    
    protected $message  =   [
        'name.require' => '名称必须填写',
        'name.max'     => '名称最多不能超过20个字符',
        'name.min'     => '名称最少不能低于5个字符',
        'name.unique'   => '名称已存在',
        'name.between' => '名称必须在5~20个字符之间'
    ];
    
    protected $scene = [
        'gedit'  =>  ['name'],
    ];
}