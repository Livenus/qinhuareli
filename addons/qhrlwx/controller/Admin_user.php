<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 用户管理
 * @type menu
 * @icon fa fa-th-list
 */
class Admin_user  extends Admin_qhrlwx{
    
    protected $stats;
    protected $sexs;
    protected $groups;
    protected $area_1;
    protected $area_2;
    protected $area_3;
    public function _initialize(){
        $this->stats = [
                '1' => '允许',
                '0' => '禁止',
                ];
        $this->sexs = [
                'male'   => '男',
                'female'  => '女',
                'unknow' => '未知',
                ];
        $this->groups = [
                '1'  => '用户',
                '2'  => '员工',
                ];
        $this->area_1 = '27';//陕西省
        $this->area_2 = '438';//西安市
        $this->area_3 = '4677';//雁塔区
        parent::_initialize();
        $this->assign('groups',$this->groups);
        $this->assign('area_1list',$this->getAreaList(0));//省
    }


    
    /**
     * @title 人员列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){
        $request = request();
        $stats  = $this->stats;
        $sexs   = $this->sexs;
        $groups = $this->groups;

        $this->assign('area_2list',$this->getAreaList($this->area_1)); //市
        $this->assign('area_3list',$this->getAreaList($this->area_2)); //区
        $this->assign('area_4list',$this->getAreaList($this->area_3,3));//小区
        $this->assign('area_1',$this->area_1);
        $this->assign('area_2',$this->area_2);
        $this->assign('area_3',$this->area_3);
        

        if ($request::instance()->isAjax()){
            $p      = $request->param('p/d','1');
            $limit  = $request->param('limit/d','20');
            $offset = $request->param('offset/d','0');
            $order  = $request->param('order/s','asc');

            $where = [];
            $db_user = db('qhrlwx_user');
            $group_id     = $request->param('group_id/s','');
            $username     = $request->param('username/s','');
            $nickname     = $request->param('nickname/s','');
            $community_id = $request->param('community_id/d','');
            $mobile       = $request->param('mobile/s','');

            if($group_id){
                $where['group_id'] = $group_id;
            }

            if($username){
                $where['username'] = ['like',"%".$username."%"];
            }

            if($nickname){
                $where['nickname'] = ['like',"%".$nickname."%"];
            }

            if($mobile){
                $where['mobile'] = ['like',"%".$mobile."%"];
            }

            if($community_id){
                $where['repair_list'] = ['like',"%/".$community_id."/%"];
            }

            $total = $db_user->where($where)->count();
            $list = $db_user->where($where)
                    ->limit($offset,$limit)
                    ->order('id '.$order)->select();

            //查询小区
            $com_list = array_column($list,'repair_list','id');
            $com_arr = [];
            foreach ($com_list as $value) {
                $com_arr = array_keys(array_flip($com_arr)+array_flip(explode('/', trim($value,' /'))));
            }
            $communitys = $this->get_community_list($com_arr);
            
            foreach ($list as &$v) {
                $v['stat']   = $stats[$v['stat']] ? $stats[$v['stat']] : '--' ;
                $v['gender'] = $sexs[$v['gender']] ? $sexs[$v['gender']] : '--' ;
                $v['group']  = $groups[$v['group_id']] ? $groups[$v['group_id']] : '--' ;
                $v['create_time']  = date('Y-m-d H:i:s',$v['create_time']) ? date('Y-m-d H:i:s',$v['create_time']) : '--' ;
                if(!empty($v['repair_list']) && $communitys){
                    $v['repair_list']= $this->get_com_str($communitys,$v['repair_list']);
                }
                
            }
            return ['rows' => $list,'total' => $total];
        }
        
        return $this->fetch();
    }

    /**
     * @title 添加人员
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();
        $db_user = db('qhrlwx_user');
        $groups = $this->groups;
        if ($request::instance()->isAjax()){
            
            $data = $this->_get_param(-1);
            $group_id = $request->param('group_id/s','1');
            if(!isset($groups[$group_id])){
                $this->a_err('类型错误！');
            }
            $data['group_id']    = $group_id;
            $data['reg_ip']      = $request->ip(1);       
            $data['create_time'] = time();

            $res = $db_user->insert($data);
            if($res > 0){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加失败！');
            }
        }

        //获取默认
        $this->assign('area_2list',$this->getAreaList($this->area_1));//市
        $this->assign('area_3list',$this->getAreaList($this->area_2));//区
        $this->assign('area_4list',$this->getAreaList($this->area_3,3)); //小区
        $this->assign('area_1',$this->area_1);
        $this->assign('area_2',$this->area_2);
        $this->assign('area_3',$this->area_3);
        return $this->fetch();
    }

    /**
     * @title 编辑人员
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $db_user = db('qhrlwx_user');
        $id = $request->param('id/s','');
        $info = $db_user->find($id);

        if ($request::instance()->isAjax()){
            
            if($id <= 0){
                $this->a_err('参数错误');
            }
            $data = $this->_get_param($id);

            $data['update_time'] = time();
            $data['errtimes']    = $request->param('errtimes/d','');
            if($data['errtimes'] < 0){
                $this->a_err('登录错误次数最小为0');
            }

            $res = $db_user->where('id',$id)->update($data);
            if($res > 0){
                $this->a_suc('编辑成功！');
            }else{
                $this->a_err('编辑失败！');
            }
        }
        //查询员工工作区域数据
        if($info['group_id'] == 2){
            //员工工作小区
            $area_4 = explode('/', trim($info['repair_list'],' /'));
            if($area_4){
                $communitys = $this->get_community_list($area_4);
                foreach ($communitys as $value) {
                    $area_3 = $value['area_id'];
                }

                //获取上级省市区
                if($area_3){
                    $area = $this->getParent($area_3);
                    if(count($area) == 3){
                        $area_1 = $area[0];
                        $area_2 = $area[1];
                        $area_3 = $area[2];
                    }
                }
            }
            //获取默认
            if(count($area) != 3){
                $area_1 = $this->area_1;
                $area_2 = $this->area_2;
                $area_3 = $this->area_3;
            }

            $this->assign('area_2list',$this->getAreaList($area_1)); //市
            $this->assign('area_3list',$this->getAreaList($area_2));//区
            $this->assign('area_4list',$this->getAreaList($area_3,3)); //小区
            $this->assign('area_1',$area_1);
            $this->assign('area_2',$area_2);
            $this->assign('area_3',$area_3);
            $info['repair_list'] = array_flip($area_4);  //已经选中的维修区域

        }

        $this->assign('user',$info);
        return $this->fetch();
    }


    /**
     * @title 删除人员
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        $request = request();
        $id = $request->param('id/d','');

        if ($request::instance()->isAjax()){
            if($id <= 0){
                $this->a_err('参数错误！');
            }

            $res = db('qhrlwx_user')->where(['id' => $id,'group_id' => 2])->delete();
            if($res !== false){
                $this->a_suc('删除成功！');
            }else{
                $this->a_err('删除失败！');
            }
        }
    }

    /**
     * @title 小区字符串
     * @type menu
     * @menudisplay false
     */
    private function get_com_str($all,$com){
        if(empty($all) || empty($com)){
            return false;
        }

        $com = explode('/', trim($com,' /'));
        $com = array_flip($com);
        $all = array_column($all, 'name','id');
        $result = array_intersect_key($all, $com);
        return implode(',',$result) ? implode(',',$result) : '--';
    }

    /**
     * @title 获取添加和编辑参数
     * @type menu
     * @menudisplay false
     */
    private function _get_param($f){
        $request = request();
        $db_user = db('qhrlwx_user');
        $username    = $request->param('username/s','');
        $nickname    = $request->param('nickname/s','');
        $password    = $request->param('password/s','');
        $bank_password = $request->param('bank_password/s','');
        $avatar      = $request->param('avatar/s','');
        $gender      = $request->param('gender/s','');
        $email       = $request->param('email/s','');
        $email_isverified  = $request->param('email_isverified/s','');
        $mobile      = $request->param('mobile/s','');
        $mobile_isverified = $request->param('mobile_isverified/s','');
        $stat        = $request->param('stat/s','');
        $birthday    = $request->param('birthday/s','');
        $repair_list = $request->param('repair_list/a','');

        if(empty($username)){
            $this->a_err('用户名不能为空！');
        }

        if($f > 0){
            if($db_user->where('username',$username)->where('id','neq',$f)->count() > 0){
                $this->a_err('用户名已存在！');
            }

            if(!empty($mobile) && $db_user->where('mobile',$mobile)->where('id','neq',$f)->count() > 0){
                $this->a_err('手机号已存在！');
            }

            if(!empty($password) &&strlen($password) < 6){
                $this->a_err('密码最短6位！');
            }

            if(!empty($bank_password) &&strlen($bank_password) < 6){
                $this->a_err('资金密码最短6位！');
            }
        }else{
            if($db_user->where('username',$username)->count() > 0){
                $this->a_err('用户名已存在！');
            }

            if(!empty($mobile) && $db_user->where('mobile',$mobile)->count() > 0){
                $this->a_err('手机号已存在！');
            }

            if(strlen($password) < 6){
                $this->a_err('密码最短6位！');
            }

            if(strlen($bank_password) < 6){
                $this->a_err('资金密码最短6位！');
            }
        }
        

        $salt = j_radomstr(6);
        $bank_salt = j_radomstr(6);
        $data['username'] = $username;
        $data['nickname'] = $nickname;
        if($f > 0){
            if(!empty($password)){
                $data['salt'] = $salt;
                $data['password'] = j_encodepassword($password,$salt);
            }
            if(!empty($bank_password)){
                $data['bank_salt'] = $bank_salt;
                $data['bank_password'] = j_encodepassword($bank_password,$bank_salt);
            }
        }else{
            $data['salt'] = $salt;
            $data['password'] = j_encodepassword($password,$salt);
            $data['bank_salt'] = $bank_salt;
            $data['bank_password'] = j_encodepassword($bank_password,$bank_salt);
        }
        $data['avatar'] = $avatar;
        $data['gender'] = $gender;
        $data['email'] = $email;
        $data['email_isverified'] = $email_isverified;
        $data['mobile'] = $mobile;
        $data['mobile_isverified'] = $mobile_isverified;
        $data['stat'] = $stat;
        $data['birthday'] = strtotime($birthday);
        $data['repair_list'] = implode('/', $repair_list) ? '/'.implode('/', $repair_list).'/' : '';

        return $data;
    }


    /**
     * @title 获取地区列表
     * @type menu
     * @menudisplay false
     */
    public function getAreaListAc(){
        $request = request();
        $area_id = $request->param('area_id/s','0');
        $type    = $request->param('type/s','1');

        $this->a_suc('',$this->getAreaList($area_id,$type));
    }

    private function getAreaList($area_id,$type){
        if($type == 3){
            $list = $this->get_community_list('',$area_id);
        }else{
            $list = db('admin_area')->where('pid',$area_id)->select();
        }
        return $list;
    }

    /**
     * @title 查询省市区
     * @type menu
     * @login 1
     * @menudisplay false
     */
    private function getParent($id){
        if(empty($id)){
            return false;
        }
        $info = [];
        while (!empty($id)) {
            $area = db('admin_area')->find($id);
            $info[] = $area;
            $id = $area['pid'];
        }
        return array_column(array_reverse($info),'id');
    }
}   