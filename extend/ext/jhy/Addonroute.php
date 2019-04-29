<?php
namespace jhy;
use think\Route;
use think\Loader;
use think\Config;
use think\Hook;
class Addonroute{
    private $addonPath;
    public function __construct(){
        $this->addonPath = ROOT_PATH . DS . 'addons' . DS;
    }
    public function run($addonname, $c='index', $a='index'){
        if(empty($c)) $c='index';
        if(empty($a)) $a='index';
        $_param = [&$addonname, &$c, &$a];
        foreach($_param as $k => &$v){
            $v = (string)$v;
            $v = strtolower($v);
            $v = trim($v);
            if(!preg_match('/^[a-z_]+$/', $v)){
                abort(404, '插件页面找不到了(1)');
            }
        }
        $c = ucfirst($c);
        $request = request()->instance();
        $request->module($addonname)->controller($c)->action($a);
        
        Hook::listen('addon_begin', $request);
        if(!empty($addonname) && !empty($c) && !empty($a)){
            $addonClassfile = $this->addonPath . DS . $addonname . DS . 'controller' . DS . $c . EXT;
            if(is_file($addonClassfile)){
                $addonClassname = 'addons\\' . $addonname . '\\controller\\' . $c;
                $instance = new $addonClassname($request);
                
                $vars = [];
                Hook::listen('addon_module_init', $request);
                if (is_callable([$instance, $a . Config::get('action_suffix')])){
                    $call = [$instance, $a . Config::get('action_suffix')];
                }elseif (is_callable([$instance, '_empty'])){
                    $call = [$instance, '_empty'];
                    $vars = [$a];
                }else{
                    abort(404, '插件页面找不到了(2)');
                }
                Hook::listen('addon_action_begin', $call);
                return call_user_func_array($call, $vars);
            }
        }
        abort(404, '插件页面找不到了(3)');
               
    }
}

Loader::addNamespace('addons', ROOT_PATH . DS . 'addons');
Loader::addNamespace('runtime', RUNTIME_PATH);



//路由

$rule = \think\Cache::get('jhy_router_rule');
if(!$rule){
    $___jhy_router_ruleArr = \think\Db::table('__ADMIN_MODULES__')->select();
    if(isset($___jhy_router_ruleArr) && !empty($___jhy_router_ruleArr)){
        $___jhy_router_ruleArr_tem = [];
        foreach($___jhy_router_ruleArr as $k => $v){
            if(!in_array($v['en_name'], ['index','admin','wx','jhy'])){
                $___jhy_router_ruleArr_tem[] = [
                    $v['en_name'] . '/[:controller]/[:action]',
                    'jhy\\Addonroute@run?addonname='.$v['en_name'].'&c=:controller&a=:action'
                ];
                
            }
        }
        $rule = $___jhy_router_ruleArr_tem;
        \think\Cache::set('jhy_router_rule', $rule, 500);
    }
}

if(!empty($rule))
    Route::any($rule);

Hook::add('addon_begin',function($request){
    
});

Hook::add('addon_module_init',function(){
    
});

Hook::add('addon_action_begin',function(){
    
});


