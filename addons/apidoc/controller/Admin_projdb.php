<?php 
namespace addons\apidoc\controller;
use think\Config;
use think\db\Query;

/**
 * @title 项目数据库
 * @type menu
 * @icon fa fa-database
 */
class Admin_projdb  extends \app\common\controller\Admin_home{

    /**
     * @title 更新数据字典
     * @type menu
     * @menudisplay false
     */
    public function updateAc(){
        $projectid = $this->request->param('pid/d', 0);
        
        if(!!($projectinfo = j_model('apidoc_project')->find($projectid))){
            if(!($db_addr = $projectinfo['db_addr'])){
                $this->error('数据库地址为空');
            }elseif(!($db_port = $projectinfo['db_port'])){
                $this->error('数据库端口为空');
            }elseif(!($db_uname = $projectinfo['db_uname'])){
                $this->error('数据库用户名为空');
            }elseif(!($db_name = $projectinfo['db_name'])){
                $this->error('数据库库名为空');
            }
            $dbconfig = [
                // 数据库类型
                'type'            => 'mysql',
                // 服务器地址
                'hostname'        => $db_addr,
                // 数据库名
                'database'        => $db_name,
                // 用户名
                'username'        => $db_uname,
                // 密码
                'password'        => $projectinfo['db_upass'],
                // 端口
                'hostport'        => $db_port,
                // 连接dsn
                'dsn'             => '',
                // 数据库连接参数
                'params'          => [],
                // 数据库编码默认采用utf8
                'charset'         => 'utf8',
            ];
            
            $db = db('',$dbconfig);
            $_tables = $db->query('SHOW TABLE STATUS ');
            $tables = [] ;

            if($_tables){
                foreach($_tables as $k => $v){

                    $tablename = $v['Name'];
                    $columns = [];
                    if(!!($_columns = $db->query('SHOW FULL COLUMNS FROM '. $tablename))){
                        foreach($_columns as $kk => $vv){
                            $columns[$vv['Field']] = [
                                'fieldname' => $vv['Field'],
                                'type'      => $vv['Type'],
                                'is_null'   => $vv['Null'],
                                'key'       => $vv['Key'],
                                'default'   => $vv['Default'],
                                'extra'     => $vv['Extra'],
                                'comment'   => $vv['Comment']
                            ];
                        }
                    }
                    $tables[$tablename] = ['comment'=>$v['Comment'],'fields'=>$columns];
                }
            }
            
            //生成MARKDOWN
            $rn = "\r\n";
            $markdownstr = '######共' .count($tables). '张表  ' ;
            $markdownstr .= '最后更新时间：'.date('Y-m-d H:i:s') . $rn . '---' . $rn;
            foreach($tables as $k => $v){
                $_tablestr = '### '.$k .$rn.'###### '.(($v['comment'])?$v['comment']:''). $rn;
                $_fieldstr = '';
                if($v['fields']){
                    $_fieldstr =  $rn. '|字段名|类型|是否NULL|默认值|备注|'.$rn;
                    $_fieldstr      .= '|:|:|:|:|:|'. $rn;
                    foreach($v['fields'] as $kk => $vv){
                        $_fieldstr .= '|' . $vv['fieldname'] .
                                      '|' . $vv['type'] .
                                      '|' . $vv['is_null'] . 
                                      '|' . ($vv['default']?$vv['default']:'空') . 
                                      '|' . $vv['comment'] . 
                                      '|'. $rn;
                    }
                }
                $markdownstr .= $_tablestr . $_fieldstr;
            }
            
            
            $mod = j_model('apidoc_projectdb');
            if($mod->where('id', $projectid)->count() > 0){
                $mod -> isUpdate(true)->save(['content'=>$markdownstr],['id'=>$projectid]);
            }else{
                $mod -> isUpdate(false)-> save(['id'=>$projectid, 'content'=>$markdownstr]);
            }
            
        }else{
            return $this->error('项目不存在');
        }
        
        
        
        
        
        
        return 'updataAc';
    }
}