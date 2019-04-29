<?php 

namespace addons\qhrlwx\controller;

/**
 * @title 登录登出
 * @type menu
 * @menudisplay false
 * @author jhy
 *
 */
class Login extends \app\common\controller\Home{
    
    protected $logincode = 'qhrlwx';

    /**
     * @title成功返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function suc($data){
        $rdata['stat'] = 1;
        $rdata['data']  = $data;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @title失败返回结果
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function err($msg){
        $rdata['stat'] = 0;
        $rdata['msg']  = $msg;
        die(json_encode($rdata, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @title 登录
     * @type menu
     * @login 0
     * @menudisplay false
     * @return string
     */
    public function loginAc(){

        if(($errmsg = $this->isLogin()) === true){
            $this->redirect('qhrlwx/index/index');
        }

        return   $this->view->fetch(j_view_login($this->logincode),[],[],[],true);

    }

    /**
     * @title 注册
     * @type interface
     * @login 0
     * @param username  用户名  1  1  验证用户名为6到20位数字或字母和唯一验证
     * @param mobile   手机号   1  1  手机号有格式和唯一验证
     * @param code   短信验证码 1   1  4位
     * @param password  密码   1   1   验证密码为6到20位字符串
     */
    public function registerAc(){

        $request = request();
        //注册用户名
        $username = $request->post('username/s','');
        //用户手机号
        $mobile = $request->post('mobile/s','');
        //验证码
        $code = $request->post('code/s','');
        //密码
        $password = $request->post('password/s','');

        //验证用户名为6到20位字符串
        if(preg_match('/^[A-Za-z0-9]{6,20}$/',$username) != 1){
            $this->err('请输入正确的用户名！');
        }
        //验证用户手机号
        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$mobile) != 1){
            $this->err('请输入正确的手机号！');
        }
        //验证用户验证码
        if(preg_match('/^[0-9]{4}$/',$code) != 1){
            $this->err('请输入正确的验证码！');
        }

        $result = $this->verifycode($mobile,$code);
        if($result === false){
            $this->err('参数错误！');
        }elseif($result !== true){
            $this->err($result);
        }
        //验证密码为6到20位字符串
        if(preg_match('/^.{6,20}$/',$password) != 1){
            $this->err('请输入正确的密码！');
        }
        
        $db_user = db('qhrlwx_user');
        //验证用户名唯一
        $count = $db_user->where('username',$username)->count();
        if($count > 0){
            $this->err('当前用户名已经注册！');
        }

        //验证手机号唯一
        $count2 = $db_user->where('mobile',$mobile)->count();
        if($count2 > 0){
            $this->err('当前手机号已经注册！');
        }

        $salt = j_radomstr(6);
        $data = [];
        $data['username']    = $username;
        $data['password']    = j_encodepassword($password,$salt);
        $data['salt']        = $salt;
        $data['mobile']      = $mobile;
        $data['stat']        = 1;    //允许登录
        $data['group_id']    = 1;    //用户组
        $data['reg_ip']      = $this->request->ip(1);       
        $data['create_time'] = time();

        $res = $db_user->insert($data);
        if($res > 0){
            $this->suc('注册成功！');
        }else{
            $this->err('注册失败！');
        }

    }

    /**
     * @title 发送验证码
     * @type interface
     * @login 0
     * @param mobile   手机号   1  1  手机号有格式验证
     */
    public function sendSmsAc(){
        $request = request();
        $mobile = $request->post('mobile/s','');

        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$mobile) != 1){
            $this->err('请输入正确的手机号！');
        }

        $sms = new \ext\jhy\Sms();
        $res = $sms->sendMsg($mobile);
        if($res['stat'] == 1){
            $this->suc('发送成功！');
        }else{
            $this->err($res['msg']);
        }
    }

    /**
     * @title 验证验证码
     * @type menu
     * @login 0
     * @menudisplay false
     * @param mobile  手机号  
     * @param code  验证码 
     */
    private function verifycode($mobile,$code){

        if(empty($mobile) || empty($code)){
            return false;
        }

        $sms = new \ext\jhy\Sms();
        $res = $sms->verifycode($mobile,$code);
        if($res['stat'] == 1){
            return true;
        }else{
            return $res['msg'];
        }

    }


    /**
     * @title 忘记密码校验
     * @type interface
     * @login 0
     * @param mobile  手机号  1  1  
     * @param code   验证码  1   1
     */
    public function forgetCheckAc(){
        $request = request();
        $mobile = $request->post('mobile/s','');
        $code = $request->post('code/s','');
        //验证用户手机号
        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$mobile) != 1){
            $this->err('请输入正确的手机号！');
        }
        $code = $request->post('code/s','');
        //验证用户验证码
        if(preg_match('/^[0-9]{4}$/',$code) != 1){
            $this->err('请输入正确的验证码！');
        }

        //验证手机号
        $count = db('qhrlwx_user')->where('mobile',$mobile)->count();
        if($count != 1){
            $this->err('用户不存在！');
        }

        $result = $this->verifycode($mobile,$code);
        if($result === false){
            $this->err('参数错误！');
        }elseif($result !== true){
            $this->err($result);
        }
        $this->suc('验证通过！');
    }


    /**
     * @title 忘记密码
     * @type interface
     * @login 0
     * @param mobile  手机号  1  1  
     * @param password  密码  1   1  验证完成后
     * @param code  验证码  1  1  四位数字
     */
    public function editPassAc(){
        $request = request();
        //用户手机号
        $mobile = $request->post('mobile/s','');
        //新密码
        $password = $request->post('password/s','');
        //验证用户验证码
        $code = $request->post('code/s','');
        
        if(preg_match('/^[0-9]{4}$/',$code) != 1){
            $this->err('请输入正确的验证码！');
        }

        if(preg_match('/^1(3\d{1}|47|5[^4]|78|8[^1])\d{8}$/',$mobile) != 1){
            $this->err('请输入正确的手机号！');
        }

        //验证密码为6到20位字符串
        if(preg_match('/^.{6,20}$/',$password) != 1){
            $this->err('请输入正确的密码！');
        }

        $result = $this->verifycode($mobile,$code);
        if($result === false){
            $this->err('参数错误！');
        }elseif($result !== true){
            $this->err($result);
        }

        $db_user = db('qhrlwx_user');
        //验证用户名
        $count = $db_user->where('mobile',$mobile)->count();
        if($count != 1){
            $this->err('用户不存在！');
        }

        $data = [];
        $salt = j_radomstr(6);
        $data['salt']     = $salt;
        $data['password'] = j_encodepassword($password,$salt);

        $res = $db_user->where('mobile',$mobile)->update($data);

        if($res !== false){
            $this->suc('修改成功！');
        }else{
            $this->err('修改失败！');
        }
    }


    /**
     * @title 地区查询
     * @type interface
     * @login 0
     * @param id  地区编号  1  0  不传查询省级
     * @return total   条数  1  1   
     * @return data  返回数据  array  0 
     */
    public function getAreaAc(){
        $request = request();

        $id = $request->post('id/s','0');
        $db_area = db('admin_area');

        $list = $db_area->where('pid',$id)->order('sort asc')->select();

        $return = [
            'total'      => count($list),
            'data'       => $list
        ];

        $this->suc($return);
    }
}