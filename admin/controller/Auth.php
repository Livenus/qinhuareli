<?php 

namespace app\admin\Controller;

/**
 * @title 权限管理
 * @icon fa fa-cogs
 */
class Auth extends \app\common\controller\Admin_home{
    
    /**
     * @title 所有权限组列表
     * @type menu
     * @icon fa fa-list
     * @desc 列出本系统所有权限模块及权限组
     */
    public function grouplistAc(){
        
        $list = scandir(ROOT_PATH . DS . 'addons'  );
        $list = array_merge($list, ['admin', 'jhy', 'index', 'wx']);
        $modulelist = [];
        $i=0;
        foreach($list as $k => $v){
            if(in_array($v, ['.','..' ])){
                continue;
            }
            $modulelist[] = [
                'id' => $i,
                'name'=> $v
            ];
            $i++;
        }
        
        //取得每模块下的权限组列表
        $moduleUsergrouplist = [];
        foreach($modulelist as $k => $v){
            //模块是否有登录
            $moduleLogininfo = j_model('jhy_login')->where('code', $v['name'])->find();
            if(isset($moduleLogininfo) && !empty($moduleLogininfo)){
                
                $moduleUsergrouplist[$k]['group'] = $moduleLogininfo;
                
                $_gtable = $moduleLogininfo['usergrouptable'];
                if(!empty($_gtable)){
                    $_glist = j_model($_gtable)->select();
                    foreach($_glist as $kk => $vv){
                        $vv = $vv -> toArray();
                        $vv['modulename'] = $v['name'];
                        $moduleUsergrouplist[$k]['son'][] = $vv;
                        
                    }
                }
            }
        }
//         dump($moduleUsergrouplist);
        $this->assign('glist', $moduleUsergrouplist);
        return $this->fetch('user/auth_grouplist');
    }
    
    /**
     * @title 权限编辑
     * @desc 编辑一个用户组的权限
     * @icon fa f-edit
     * @type menu
     * menudisplay false
     * @return string
     */
    public function editAc(){
        $modulename = $this->request->param('modulename/s');
        $id         = $this->request->param('id/d');
        
        $gauth_mod = j_model('jhy_groupauth');
        
        if($this->request->isPost()){
            $selectAuthlist = $this->request->param('selectauthlist/a');
            
            $where = ['modulename'=>$modulename, 'g_id'=>$id];
            $_o_gauthlist = $gauth_mod->where($where)->select();
            
            $o_gauthlist = [];
            foreach($_o_gauthlist as $k => $v){
                $o_gauthlist[$v['auth_id']] = $v->getData();
            }
            $mca = [];
            foreach($selectAuthlist as $k => $v){
                if(empty($v)) continue;
                $where = array_merge($where, ['auth_id'=>$v]);
                
                if(!isset($mca[$v])){
                    $mca[$v] = j_model('jhy_menu_op')->where('id', $v)->find();
                    $mcacount = count(explode('/', $mca[$v]));
                    if($mcacount == 2){
                        $mca[$v] = $mca[$v]['modulename'] . '/' . $mca[$v]['mca'];
                    }else{
                        $mca[$v] = $mca[$v]['mca'];
                    }
                }
                if(isset($o_gauthlist[$v])){
                    $gauth_mod->where($where)->update(['stat'=>1, 'auth_mca'=>$mca[$v]]);
                    unset($o_gauthlist[ $v]);
                }else{
                    $gauth_mod->insert(array_merge($where, [ 'stat'=>1, 'auth_mca'=>$mca[$v]]));
                }
            }

            $gauth_mod->where('auth_id', 'in', array_keys($o_gauthlist))->update(['stat'=>0]);
            
            return $this->success();
            
        }
        
        if($modulename == 'index'){
            $where = [
                'modulename' => ['not in', ['admin','jhy']]
            ];
        }else{
            $where = ['modulename'=>$modulename];
        }
        $where['g_id'] = $id;
        
        //组权限列表
        $_gauthlist = j_model('jhy_groupauth')->where($where)->select();
        $gauthlist = [];
        foreach($_gauthlist as $k => $v){
            $v = $v->getData();
            $gauthlist[$v['g_id']][$v['auth_id']] = $v['stat'];
        }
 
        //权限列表

//         $authlist = j_model('jhy_menu_op')->where($where)->select();
        $authlist['menu'] = [
            'name' => '菜单',
            'items'=>j_getMenulist($modulename, ['type'=>'menu'])
        ];
        $authlist['inerface'] = [
            'name' => '接口',
            'items'=>[[
                'title' => '全部接口',
                'son' => j_getMenulist($modulename,['type'=>'interface'])
                
            ]]
        ];
//         dump($authlist);
        
        $this->assign('gid', $id);
        $this->assign('authlist', $authlist);
        $this->assign('gauthlist',$gauthlist);
        return $this->fetch('user/auth_edit');
    
    }
}

