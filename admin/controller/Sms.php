<?php 
namespace app\admin\controller;
/**
 * @title 短信配置
 * @icon fa fa-gears
 */
class Sms extends Home{
    
    /**
     * @title 短信服务列表
     */
    public function listAc(){
        
        $Sms = new \ext\jhy\Sms;
        
        $smslist = $Sms->getsmslist();
        
        j_createcodeclass('jhy_createcode');
        $jhy_createcode = new \jhy\jhy_createcode();
        
        
        foreach($smslist as $k => &$v){
            
            $jhy_createcode->set('fieldsinfo',$v['configs']); //处理一下参数
            $fieldsinfo = $jhy_createcode->get('fieldsinfo');
            
            //取得已存入数据库的参数
            $da = j_model('admin_smsconfig')->where('name', $v['en_name'])->find();
            $v['dbconfig'] = $da?$da->getData():[];
            $v['dbconfig']['configs'] = $v['dbconfig']['configs']?unserialize($v['dbconfig']['configs']):'';
            
            $htmlArr = $jhy_createcode->createFormitems([],'',$v['dbconfig']['configs']);
            $v['confightml'] = '';
            foreach($htmlArr['item'] as $kk => $vv){
                $v['confightml'] .= $vv['html'];
            }
        }
        $this->assign('smslist', $smslist);
        return $this->fetch();
    }
    
    
    /**
     * @title 设置短信参数
     * @type menu
     * @menudisplay false
     */
    public function setconfigAc(){
        $en_name = $this->request->param('en_name');
        
        $Sms = new \ext\jhy\Sms;
        $smslist = $Sms->getsmslist();
        $info = $smslist[$en_name];
        
        if(!$info){
            return $this->error('短信服务不存在');
        }
        
        $data = [];
        foreach($info['configs'] as $k => $v){
            $data[$k] = $this->request->param($k);
        }
        
        
        $mod = j_model('admin_smsconfig');
        $data = [
            'configs' => serialize($data)
        ];
        
        if(j_model('admin_smsconfig')->where('name', $en_name)->count() > 0){
            $mod->isUpdate(true)->save($data, ['name' => $en_name]);
        }else{
            $data['name'] = $en_name;
            $mod->isUpdate(false)->save($data);
        }
        return $this->success('OK');
        
    }
    /**
     * @title 选定短信服务类型
     * @type menu
     * @menudisplay false
     */
    public function selectAc(){
        $smstype = $this->request->param('smstype');
        j_model('admin_smsconfig')->isUpdate(true)->save(['is_open'=>1], ['name'=>$smstype]);
        return $this->success('ok');
    }
    
    /**
     * @title 调用频率限制
     * @type menu
     * 
     */
    public function setfAc(){
        $setting = controller('setting');
        return $setting->set('syssms', '短信');
    }
    
    
}