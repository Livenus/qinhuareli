<?php 

return [
    'name' => '秦华热力系统WX',
    'version' => '1.0.0',
    'author'  => 'yys_team',
    'logincode' => 'qhrlwx',
    'upload' => [
        'cat1' => [   //第一个分类
            'filename'=>'auto', //保存的文件名
            'type' => ['png','jpg'], //允许的文件后缀，同时受系统上传配置的限制
            'mime' => 'auto',
            'size' => 1024000 //文件大小（字节数）
        ]
    ]
];
