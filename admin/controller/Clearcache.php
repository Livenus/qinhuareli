<?php 

namespace app\admin\Controller;

/**
 * @title 清除缓存
 * @icon fa fa-cogs
 */
class Clearcache extends \app\common\controller\Admin_home{
    /**
     * @title 清除缓存
     * @icon fa fa-remove
     */
    public function clearchcheAc(){
        $temDirs = [
            'runtime/temp',
            'runtime/cache',
            'runtime/jhy'
        ];
        foreach($temDirs as $k => $v){
            j_rmdirs($v, false);
        }
        $this->success('操作成功！', null, null, -1);
    }
}