<?php 

namespace ext\jhy\sms;

/**
 *  手机短信发送基类
 * @author Administrator
 *
 */

abstract  class  Smsbase {
    
    //发送短信
    abstract function sendMsg($mobiles,$content='',$params=array(),$templateid=0);
    
    //基本信息
    abstract function getinfo();
    
}