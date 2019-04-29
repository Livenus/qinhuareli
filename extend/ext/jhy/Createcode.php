<?php
namespace jhy{
use \Exception;

error_reporting(E_ERROR | E_PARSE  |E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR);


/**
 * 基类
 */
class jhy_base implements \ArrayAccess {
//     public $_data=array();
//     //错误信息
//     protected $errmsg;
//     //成功信息
//     protected $msg;
//     //错误代码。0为无错误
//     protected $code;
//     //数据
//     protected $data;
    protected $_data;
    protected $protected = [
        'errmsg',
        'msg',
        'code',
        'data'
    ];

    public function __construct(){
        $this->set('errmsg','');
    }
    public function __get($name){
        return $this->get($name);
    }

    public function set($name, $val){
//         if(property_exists($this, $name)){
       if($name=='protected' || in_array($name, $this->protected)){
            $this->_data[$name] = $val;
            return $this;

        }else{
            throw new Exception('Class "' . get_class($this) .'" connot set property:'.$name);
        }
    }
    public function get($name){
//         if(property_exists($this, $name)){
        if($name=="protected" || in_array($name, $this->protected)){
            if(isset($this->_data[$name])){
                return $this->_data[$name];
            }
            return null;
        }elseif($name == '_data'){
            return $this->_data;
        }else{
            throw new Exception('Class "' . get_class($this) .'" connot get property: '.$name);
        }

    }
    public function getMessage(){
        return $this->get('errmsg');
    }

    public function suc($data, $msg=''){
        $t = new self();
        return $t->set('data', $data)->set('msg', $msg);
    }
    public function err($errmsg='', $errcode=255){
        $t = new self();
        return $t->set('errmsg', $errmsg)->set('code', $errcode);
    }
    public function is_suc(){
        if($this->get('errmsg') === ''){
            return true;
        }
        return false;
    }

    // ArrayAccess
    public function offsetSet($name, $value){
        $this->set($name, $value);
    }

    public function offsetExists($name){
        if(property_exists($this, $name)){
            return true;
        }
        return false;
    }
    public function offsetUnset($name){
        $this->set($name, null);
    }
    public function offsetGet($name){
        return $this->get($name);
    }

}


//生成模板
class jhy_createcode extends jhy_base{
    // 保护字段
//     protected $reservedField;
//     //忽略字段
//     protected $ignoreFields;
//     //字段数据
//     protected $fieldsinfo;
//     //模板类
//     protected $tplClass;

    public function __construct(){
        $this->protected = array_merge($this->protected, [
            'reservedField',
            'ignoreFields',
            'fieldsinfo',
            'tplClass'
        ]);
        $this->set('reservedField',['create_time', 'update_time'])
             ->set('ignoreFields',[])
             ->set('tplClass', new \jhy\jhy_formitem_pc());
    }
    
    public function set($name, $val){
        if($name == 'fieldsinfo'){
            $jhy_db_decompose = new jhy_db_decompose();
            $_val = [];
            foreach($val as $k => $v){
                if(($v['column_comment'])){
                    $_val[$k] = new jhy_fieldinfo();
                    $_val[$k]->set('column_name', $v['column_name'])
                    ->set('column_default', $v['column_default'])
                    ->set('is_nullable', $v['is_nullable'])
                    ->set('data_type', $v['data_type'])
                    ->set('character_maximum_length', $v['character_maximum_length'])
                    ->set('numeric_precision',$v['numeric_precision'])
                    ->set('column_comment',$v['column_comment'])
                    ->set('column_comment_de', $jhy_db_decompose->fieldCommentDecode($v['column_comment']))
                    ->set('column_key', $v['column_key'])
                    ->set('signed', $v['signed']);
                    
                }
          
            }
            $val = empty($_val)?$val:$_val;
        }
        return parent::set($name, $val);
    }
    
    public function createHome($params=[]){
        $tplClass = $this->get('tplClass');
        if(!$params['filename']){
            throw new Exception('将要保存的模型文件名不能为空。参数：$params[\'filename\']');
        }
        $filedir = dirname($params['filename']);
        if(!is_dir($filedir)){
            mkdir($filedir, 0755, true);
            file_put_contents($filedir. '/index.html', '');
        }
        $content = $tplClass->createHome($params);
        
        file_put_contents($params['filename'], $content);
        return true;
    }
    
    /**
     * 生成登录页
     * @param array $params
     * @param  string $params['filename'] 存储文件的位置
     * @param  string $params['modulename'] 模块名 
     * @throws Exception
     * @return boolean
     */
    public function createLogin($params = array()){
        $tplClass = $this->get('tplClass');
        if(!$params['filename']){
            throw new Exception('将要保存的模型文件名不能为空。参数：$params[\'filename\']');
        }
        $filedir = dirname($params['filename']);
        if(!is_dir($filedir)){
            mkdir($filedir, 0755, true);
            file_put_contents($filedir. '/index.html', '');
        }
        $content = $tplClass->createLogin($params);
        
        file_put_contents($params['filename'], $content);
        return true;
    }

        public function createModel($params=array() ){
            $tplClass = $this->get('tplClass');
            if(!$params['filename']){
                throw new Exception('将要保存的模型文件名不能为空。参数：$params[\'filename\']');
            }
            $filedir = dirname($params['filename']);
            if(!is_dir($filedir)){
                mkdir($filedir, 0755, true);
                file_put_contents($filedir. '/index.html', '');
            }
            $content = $tplClass->createModel($params);
            file_put_contents($params['filename'], "<?php\n//以下代码由系统自动生成。\n//生成时间:".date('Y-m-d H:i:s')."\n".$content);
            return true;
        }
        public function createlist($params=array()){
            $tplClass = $this->get('tplClass');
            if(!$params['filename']){
                throw new Exception('将要保存的列表模板文件名不能为空。参数：$params[\'filename\']');
            }
            $filedir = dirname($params['filename']);
            if(!is_dir($filedir)){
                mkdir($filedir, 0755, true);
//                file_put_contents($filedir. '/index.html', '');
            }
            $content = $tplClass->createlist($params);
            file_put_contents($params['filename'], "<{/*以下代码由系统自动生成。\n//生成时间:".date('Y-m-d H:i:s')."\n */}>".$content);
            return true;
        }


    //生成相应代码，
    /**
     * 生成相应代码
     * @param  array $fields_info 用户自定义字段内容
     * @param  array $fields_vals 各字段值，一般用于修改前的显示
     * @return [type]              [description]
     *            inputtype  输入类型
     */
    public function createFormitems($fields_info, $submit_url='', $fields_vals, $is_search=false){
        //保护字段
        $reservedField = $this->get('reservedField');
        //忽略字段
        $ignoreFields = $this->get('ignoreFields');
        //字段信息
        $fieldsArr = $this->get('fieldsinfo');
        //前端模板
        $Form = $this->get('tplClass');
        //数入 和 DB字段 对应规则
        $jhy_inputAndDbRole = new jhy_inputAndDbRole();
        $fields_role          = $jhy_inputAndDbRole->get('i2d');
        $fields2inputtype_role = $jhy_inputAndDbRole->get('d2i');
  
        if(empty($fieldsArr)){
            return $this->err('字段信息不能为空。设置方法：$jhy_createcode->set("fieldsinfo",$someinfo)');
        }


      //  $Form = new $tplClass();

        //循环字段，构建HTML,JS,CSS
        $formitemlist = [];
        $columnList = is_array($fieldsArr)?$fieldsArr:$fieldsArr->get('data');
        $jhy_db_decompose = new jhy_db_decompose();

        foreach ($columnList as $k => $v){
            if(!$v){
                continue;
            }
            $field = $v->get('column_name');

            if ($v->get('column_key') == 'PRI' || in_array($field, $reservedField) || in_array($field, $ignoreFields)){
                continue;
            }
            $fieldsComment = ($v->get('column_comment_de'));
            $fieldsComment_fieldlan = $fieldsComment->get('fieldlan');
            
            if($fieldsComment->get('T')){
                $fields_info[$field]['inputtype'] = $fieldsComment->get('T');
            }


            if(isset($fields_info[$field]['inputtype'])){
                //用户自定义，则按用户自定义，检查规则
                if(!in_array($v->get('data_type'), array_keys($fields_role['input'][$fields_info[$field]['inputtype']]['asso']))){
                    //关联表时，不检查
                    if(!$fieldsComment->get('B')){
                        throw new Exception($fieldsComment_fieldlan.' 输入类型(' . $fields_info[$field]['inputtype'] . ') 与数据库字段('.$v->get('data_type').')不宜匹配。参考j_field配置文件');
                    }

                }
            }else{
                //用户未自定义输入类型，系统自动分配类型,字段名匹配优先，类型匹配次之
                $fieldtype_match = '';
                foreach($fields2inputtype_role as $_k => $_v){
                    foreach($_v as $_kk => $_vv){

                        if($_kk == $v->get('data_type')){
                     
                            if(!empty($_vv) && preg_match($_vv, $field)){
                                
                                $fields_info[$field]['inputtype'] = $_k;
                                break;
                            }elseif(empty($fieldtype_match)){
                                $fieldtype_match = $_k;
                            }
                        }
                    }
                }
            }
            if(!isset($fields_info[$field]['inputtype']) && !empty($fieldtype_match)){
                $fields_info[$field]['inputtype'] = $fieldtype_match;
            }
            if(!$fields_info[$field]['inputtype'] ){
                if($v['DATA_TYPE'] == 'enum'){
                    $fields_info[$field]['inputtype'] = 'list_s';
                }elseif($v['DATA_TYPE'] == 'set'){
                    $fields_info[$field]['inputtype'] = 'list_m';
                }else{
                    throw new Exception($fieldsComment_fieldlan .' 输入类型与数据库字段('.$v['DATA_TYPE'].')未能自动匹配。参考j_field配置文件');
                }                 

            }

            //表关联，多对1
            $_b = $fieldsComment->get('B');
            if(!empty($_b)){
                
            }
// dump($v);
            //
            $_is_require = (strtolower($v->get('is_nullable'))=='no' && null ===($v->get('column_default')))?true:false;
            if(null != ($fieldsComment->get('A'))){
                $_is_require = $fieldsComment->get('A')?true:false;
            }
            $_type = ('r'==$fieldsComment->get('E'))?('radio'):('select');
            if(!$is_search){
                $_name = ($_is_require?'<b style="color:#ff0000">(*)</b>':'') .$fieldsComment_fieldlan . ':';
            }else{
                $_name = $fieldsComment_fieldlan;
            }
            $param = [
                'name'  => $_name,
                'field' => $field,
                'desc'  => '',
                'items' => $fieldsComment->get('C'), //$itemArr['field'],
                'default' => $v->get('column_default'), //$v['COLUMN_DEFAULT'],
                'itemtype' => $_type,
                'is_multi' => ($v->get('data_type')==='set')?true:(($v->get('data_type')==='enum')?false:''),
                'desc'  => $fieldsComment->get('H'),  //帮助文字
                'is_require'=>$_is_require,     //验证是否必填  
            ];
            $_e = ['G'];
            foreach($_e as $kk => $vv){
                if(!empty($_etmp = $fieldsComment->get($vv))){
                    $param['e_'.$vv] = $_etmp;
                }
            }
            
            

            if(is_array($fields_info[$field])){
                $param = array_merge($param, $fields_info[$field]);
            }
            if(isset($fields_vals[$param['field']])){
                $param['default'] = $fields_vals[$param['field']];
            }

            $method = $fields_info[$field]['inputtype'];
            if(!method_exists($Form, $method)){
                throw new Exception($fieldsComment_fieldlan .' 输入类型('. $fields_role['input'][$method]['name'].' '.$method.')没有相应的模板!');
            }
            
            $formitemlist['item'][] = ['html'=>$Form->$method($param, (($is_search && ($fieldsComment['I']) )? $fieldsComment['I'] :$param['default']), null, $is_search)];


            //var_dump($fieldsComment);
        }

        $formitemlist['validate'] = $Form->getValidateStr();

        return $formitemlist;

    }

    public function createSubmit($content=array(), $submit_url=''){
        //前端模板
        $tplClass = $this->get('tplClass');
        return $tplClass->createSubmit($content, $submit_url);
    }
}



//PC端 前端模板
class jhy_formitem_pc extends jhy_base{
        //各html的ID
    public $itemid=0;
    //验证JS
    public $validateStr='';
    private function _getItemid(){return (++$this->itemid);}

   
    
    
    /**
     * 生成登录页
     * @param array $params
     *  $params['modulename'] 模块名
     */
    public function createLogin($params=array()){
        $modulename = $params['modulename'];
        $str = <<< jhy
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><{\$loginsysinfo['name']}>-管理端</title>
    <link rel="shortcut icon" href="favicon.ico"> <link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/static/css/animate.css" rel="stylesheet">
    <link href="/static/css/style.css?v=4.1.0" rel="stylesheet">
	<!-- Sweet Alert -->
    <link href="/static/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <!-- <script>if(window.top !== window.self){ window.top.location = window.location;}</script> -->
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen  animated fadeInDown">
        <div>
			<div class="m-t">
			    <div><{\$loginsysinfo['name']}></div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="用户名" required="" name="username">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="密码" required="" name="password">
                </div> 
                <!--
				<div class="form-group">
                    <input type="text" class="form-control" placeholder="验证码" required="" name="code">
					<span><img
						src="<{:captcha_src()}>" style="width: 100%; height: 45px;"
						onclick="this.src='<{:captcha_src()}>?t'+new Date().getTime();" id="captcha"/>
					</span>
                </div>
                -->
                <button type="submit" class="btn btn-primary block full-width m-b" id="submit">登 录</button>

                <!--<p class="text-muted text-center"> <a href="login.html#"><small>忘记密码了？</small></a>-->
                </p>
			</div>
        </div>
    </div>

    <!-- 全局js -->
    <script src="/static/js/jquery.min.js"></script>
    <script src="/static/js/bootstrap.min.js"></script>
	<!-- Sweet alert -->
    <script src="/static/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/static/js/j_common.js"></script>
	<script>
    $(function(){
        var msg = "<{\$errmsg}>";
        if(msg){
            alert(msg);
        }
    });
	$("#submit").click(function(){
		var username = $('input[name=username]').val();
		var password = $('input[name=password]').val();
		var code = $('input[name=code]').val();
		$.ajax({
			url:"<{:url('jhy/jhylogin/login')}>",
			type:'post',
			dataType:'json',
			data:{username:username,password:password,code:code,logincode:'<{\$loginsysinfo['code']}>'},
			success:function(d){
				if(d.code == '1'){
					//存入登录状态
					var token = d.msg;
					j.setloginToken(token);
					swal({
						title: "",
						text: "登录成功",
						type: "success"
					},function(){
					    
						location.href=d.url?d.url:"<{:url('<{\$loginsysinfo['code']}>/index/index')}>";
					});
				
				}else{
					swal({
						title: "出错了",
						text: d.msg,
						type: "error"
					},function(){
						//location.href="{:url('user/login')}";
						$('#captcha').attr('src',"<{:captcha_src()}>?t="+new Date().getTime());
					});
				}
			},
			error:function(){
				swal("网络错误", "", "error");
				//alert('网络错误');
			}
		});
	});

onkeydown = function(event){
	if(event.keyCode == 13){
	    $("#submit").click();
	}
}
	</script>
</body>

</html>
        
jhy;
        return $str;
    }
    
    
    public function createlist($params=array()){
        $ajax_url =$params['url'];
        if(!$ajax_url){
            throw new Exception('数据请求地址不能为空. 参数: $params[\'url\']');
        }
        if(!$params['fields']){
            throw new Exception('显示的字段不能为空. 参数: $params[\'fields\']');
        }else{
            $fieldsStr = '';
            $jhy_db_decompose = new jhy_db_decompose();
            foreach($params['fields']->get('data') as $k => $v){
//                 $commentd = $jhy_db_decompose->fieldCommentDecode($v['column_comment']);
                $commentd = $v['column_comment_de'];
                $fieldlan = is_string($commentd)?'UNNAMED':$commentd['fieldlan'];//dump($commentd);
                if($commentd['T'] == 'image'){
                    $fieldsStr .= '{title: "'.$fieldlan .'",field:"'. $v['column_name'] . '",align: "center",valign:"middle", formatter:function(value){return "<img src=\"/"+ value+"\" />";}},';
                }else{
                    $fieldsStr .= '{title: "'.$fieldlan .'",field:"'. $v['column_name'] . '",align: "center",valign:"middle"},';
                }
                
// dump($commentd->get('_data'));
            }
        }

        //每条记录后面的操作按钮
        $buttonsStr = '';
        if($params['buttons']){
            foreach($params['buttons'] as $k => $v){
                $_opvars = explode(',', $v['opvars']);
                $_opvarStr = '';
                foreach($_opvars as $kk => $vv){
                    if($_opvarStr != ''){$_opvarStr .= ',';}
                    if(substr($vv,0,1) != '!'){
//                         $vv = '\\\''row[\'' . $vv. '\']+\\\'';
                        $vv = '\\\'\'+row.'.$vv.'+\'\\\'';
                    }else{
                        $vv = '\\\''.substr($vv,1).'\\\'';
                    }
                    $_opvarStr .= $vv;
                }
                $buttonsStr .= '<a href="javascript:'.$v['opname'].'('.$_opvarStr.');" class="'.$v['classname'].'" title="'.$v['name'].'"><span class="'.$v['icon'].'"></span>'.$v['name'].'</a> ';
            }
            $buttonsStr = '{title:"操作",field: "n_do",align: "center",valign: "middle",formatter:function(value,row,index){return \''.$buttonsStr.'\';}}';
        }

        //列表顶部的功能按钮
        $topbuttonsStr = '';
        if($params['topbuttons']){

            foreach($params['topbuttons'] as $k => $v){
                $_opvars = explode(',', $v['opvars']);
                $_opvarStr = '';
                foreach($_opvars as $kk => $vv){
                    if($_opvarStr != ''){$_opvarStr .= ',';}
                    if(substr($vv,0,1) != '!'){
                        $vv = '\'' . $vv.'\'';
                    }else{
                        $vv = '\''.substr($vv,1).'\'';
                    }
                    $_opvarStr .= $vv;
                }
                $topbuttonsStr .='<button type="button" class="'.$v['classname'].'" onclick="'.$v['opname'].'('.$_opvarStr.')">
                                        <i class="'.$v['icon'].'" aria-hidden="true"></i>'.$v['name'].'
                                    </button> ';
            }

        }
        //列表的搜索条件
        $searchStr = '';
        if($params['search']){
            $jhy_createcode = new \jhy\jhy_createcode();
            $jhy_createcode->set('fieldsinfo',$this->suc($params['search']));

            $searchArr = $jhy_createcode->createFormitems([],'',[],true);
            foreach($searchArr['item'] as $k => $v){
                $searchStr .= $v['html'].' ';
            }
            if('' != $searchStr){
                $searchStr .= '<a href="javascript:list_search();" class="btn btn-primary btn-sm">搜索</a>';
            }
        }


        $str = <<<jhy
<{include file="jhy@public/header"}>
<!--
<script type="text/javascript" charset="utf-8" src="/static/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<link rel="stylesheet" href="/static/css/plugins/bootstraptable/bootstrap-table.min.css">
<script type="text/javascript" charset="utf-8" src="/static/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
-->
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox float-e-margins">
        <!--
            <div class="ibox-title" style=" padding-bottom: 0;">
      
            </div>
        -->
            <div class="ibox-content" id="content">
                <div class="row">
                    <div class="col-sm-12">
                        <!-- Example Events -->
                        <div class="example-wrap">
                            <div class="example">
                                <div class="hidden-xs" id="exampleTableEventsToolbar" role="group">
                                    {$topbuttonsStr}
                                </div>
                                <!---search start-->
                                <div class="right">
                                    <form class="form-inline" id="search_from1" onsubmit='return false'>
                                    {$searchStr}
                                    
                                    </form>
                                    
                                </div>
                                <!---search end---->
                                <table id="dg" data-height="100%" data-mobile-responsive="true">
                                </table>
                            </div>
                        </div>
                        <!-- End Example Events -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var del,edit,add,getQueryParams;
        var iframdeindex, iframefinish;
        require(['bootstrap','bootstrap-table'],function(){
            require(['/static/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js', 'layer'], function(zh, layer){
            
                //删除====================================
                del = function(id, url){
                    console.log(url)
                    layer.confirm('确认删除？', {
                      btn: ['确认','取消'] //按钮
                    }, function(){
                      layer.msg('删除中#'+id+'..', {icon: 1});
                      $.ajax({
                            url:url + '/id/' + id,
                            type:'get',
                            dataType:'json',
                            success:function(d){
                                console.log(d)
                                if(d.code == 1){
                                    setTimeout(function(){list_search();},(d.wait?d.wait:2)*1000);    
                                }else{
                                    layer.msg( (d.msg?d.msg:'服务器暂时无法连接...'), {icon: 2});
                                }
                            },
                            error:function(d){
                                layer.msg('服务器暂时无法连接...', {icon: 2});
                            }
                        });
    
                    });
                 
                },//编辑==================================
                edit=function(id,url){
                    if(!id){
                        layer.msg('页面出错了...',{icon:2});
                    }
    
                    //iframe窗
                    iframdeindex = layer.open({
                      type: 2,
                      title: '修改',
                      // closeBtn: 0, //不显示关闭按钮
                      shade: [0.5],
                      area: ['90%', '90%'],
                      // offset: 'rb', //右下角弹出
    //                  time: 2000, //2秒后自动关闭
                       anim: 2,
                       shift:2,
                       maxmin: true, //开启最大化最小化按钮
                      content: [url + '/id/' + id], //iframe的url，no代表不显示滚动条
                      success:function(layero, index){
                        
                      },
                      end: function(){ //此处用于演示
                        
                      }
                    });
                },
                //添加=======================================
                add = function(un, url){
                    if(un) url = url + '/pid/' + un;                  
                    layer.open({
                      type: 2,
                      title: '新增',
                      shade: [0.5],
                      area: ['90%', '90%'],
                      content: [url], //iframe的url，no代表不显示滚动条
                      end: function(){ //此处用于演示
                        
                      }
                    });
                },
                list_search = function(){
    
                    q = getQueryParams();
                    $("#dg").bootstrapTable('refresh', {url: "{$ajax_url}",query:q}); 
                },
                getQueryParams = function(){
                    var _params = $('#search_from1').serializeArray();
                    var params={};
                    $.each(_params, function(k, v){
                        params[v.name]=v.value;
                    });
                    return params;
                },
                //关闭IFRAME刷新本页面数据
                iframefinish = function(){
                    layer.closeAll('iframe');
                    list_search();
                }
                
                //表格=========================================
                $('#dg').bootstrapTable({
                    url:'{$ajax_url}',
                    method:'post',
    //                 height: (function(){
    //                     console.info($(window).height());
    //                 return 300;})(),
    	            queryParamsType: "limit",
    	            queryParams:function(params){
                        params.p = parseInt(params.offset/params.limit)+1;
                        params = $.extend(params, getQueryParams());
                        return params;
                    },
    	            contentType: 'application/json',
    	            dataType: 'json',
    	            sidePagination: "server", //表示服务端请求
    	            pagination: true, //启动分页
    	            pageNumber: 1, //当前第几页
    	            pageSize: 10, //每页显示的记录数
    	            pageList: [5, 10, 15, 20], //记录数可选列表
    	            dataField: 'rows',
    	            idField: "id",
                    columns:[
                        {checkbox: true,align: 'center', valign: 'middle'},
                        {$fieldsStr}
                        {$buttonsStr}
                        ],
                    responseHandler:function(res){
    	              //远程数据加载之前,处理程序响应数据格式,对象包含的参数: 我们可以对返回的数据格式进行处理
    	              //在ajax后我们可以在这里进行一些事件的处理
    	              return res;
    	            },
    	            onLoadSuccess:function(){
                    }
    
                });
                
                setTimeout(function(){setTableHeight()}, 1000);
                var setTableHeight = function(){
                    var height = 300;
                    var wh = $(window).outerHeight(true);
                    console.log(wh);
                    var contenth = $("body>.wrapper-content").outerHeight(true);
                    console.log(contenth);
                    
                    var oh = $("#dg").closest('.bootstrap-table').outerHeight(true);
                    console.log(oh);
                    $("#dg").bootstrapTable('resetView',{height:oh - (contenth-wh)+10 });
                }
                $(window).resize(function(){
                    setTableHeight();
                });
                
                
            });

        });
        
       
    </script>
    <script type='text'>
    function getQueryParams(){
        var q = {};
        q.ap_id = $("#ap_id").val();
        q.a_name = $("#a_name").val();
        return q;
    }
    $("#btnsearch").click(function(){
        q = getQueryParams();
        $("#dg").bootstrapTable('refresh', {url: "<{:url('ad/listajax')}>",query:q}); 
    })
    $("#btnadd").click(function(){
        location.href="<{:url('ad/add')}>";
    })
    
    $("#btndel").click(function(){
        var items = $("#dg").bootstrapTable('getSelections');
        items = objcolum(items,'id');
        if(items.length <= 0){
            swal("出错了", "请选择要删除的值", "error");
        }else{
            del(items);
        }
    });
    function del(ids){
        swal({
            title: "您确定要删除吗",
            text: "删除后将无法恢复，请谨慎操作！",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            closeOnConfirm: false,
            closeOnCancel: false
        },
        function (isConfirm) {
            if (isConfirm) {
                if(!$.isArray(ids)){
                    ids = [ids];
                }
                $.ajax({
                    url:"<{:url('ad/del')}>",
                    data:{id:ids},
                    type:'post',
                    dataType:'json',
                    success:function(d){
                        if(d.stat == '1'){
                            swal({
                                title: "太帅了",
                                text: d.msg,
                                type: "success"
                            },function(){
                                location.href="<{:url('ad/index')}>";
                            });
                        }else{
                            swal({
                                title: "出错了",
                                text: d.msg,
                                type: "error"
                            });
                        }
                    }
                });
                //swal("删除成功！", "您已经永久删除了这条信息。", "success");
            } else {
                swal("已取消", "您取消了删除操作！", "error");
            }
        });
    }
    $(function(){
        $('#dg').bootstrapTable({
            url:"{$ajax_url}",
            //method:'post',
            queryParams:function(params){
                params.p = parseInt(params.offset/params.limit)+1;
                params = $.extend(params,getQueryParams());
                return params;
            },
            queryParamsType:"limit",
            contentType:'application/json',
            dataType:'json',
            sidePagination: "server", //表示服务端请求
            pagination:true,//启动分页
            pageNumber:1,//当前第几页
            pageSize:20,//每页显示的记录数
            pageList:[10,15,20], //记录数可选列表
            dataField:'rows',
            idField:"id",
            columns:[{checkbox: true,align: 'center', valign: 'middle'},
                    {title: '广告名称',field: 'name',align: 'center',valign: 'middle'},
                    {title: '所在广告位置',field: 'position',align: 'center',valign: 'middle'},
                    /*{title: '开始时间',field: 'a_start_date',align: 'center',valign: 'middle',
                        formatter:function(value,row,index){
                            return int2date('yyyy-MM-d h:m:s',row.a_start_date);
                    }},
                    {title: '结束时间',field: 'a_end_date',align: 'center',valign: 'middle',
                        formatter:function(value,row,index){
                            return int2date('yyyy-MM-d h:m:s',row.a_end_date);
                    }},*/
                    {title: '操作',field: 'n_do',align: 'center',valign: 'middle',
                        formatter:function(value,row,index){
                            var html = '';
                            html = '<a href="<{:url(\'ad/edit\')}>?id='+row.id+'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="编辑" style=" margin-right:4px;"><span class="glyphicon glyphicon-edit"></span> 编辑</a><a href="javascript:;" onclick="del('+row.id+')" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="删除" style=" margin-right:4px;"><span class="glyphicon glyphicon-remove"></span> 删除</a>';
                            return html;
                        }
                    }],
            responseHandler:function(res){
              //远程数据加载之前,处理程序响应数据格式,对象包含的参数: 我们可以对返回的数据格式进行处理
              //在ajax后我们可以在这里进行一些事件的处理
              return res;
            },
            search:false,
            iconSize: 'outline',
            toolbar: '#exampleTableEventsToolbar',
            icons: {
                refresh: 'glyphicon-repeat',
                toggle: 'glyphicon-list-alt',
                columns: 'glyphicon-list'
            },
            toolbarAlign:'left',
            showToggle:false,
            showColumns:false,//显示下拉框勾选要显示的列
           // minimumCountColumns:3,
           // showRefresh:true,//显示刷新按钮
            striped:true,//表格显示条纹
            singleSelect:false,//复选框只能选择一条记录  
            clickToSelect:false,//点击行即可选中单选/复选框  
            checkboxHeader:true
        });
    });
    </script>
<{include file="jhy@public/footer"}>    
jhy;
    return str_replace('<{#', '<{$', $str);
    }

    
    public function createHome($params=array()){
        
        $str = <<< jhy
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title><{\$loginsysinfo['name']}>-管理端</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <link rel="shortcut icon" href="favicon.ico"> <link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/static/css/font-awesome.min.css" rel="stylesheet">
    <link href="/static/css/animate.css" rel="stylesheet">
    <link href="/static/css/style.css?v=4.1.0" rel="stylesheet">
</head>

<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
    <div id="wrapper">
        <!--左侧导航开始-->
        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="nav-close"><i class="fa fa-times-circle"></i>
            </div>
            <div class="sidebar-collapse">
                <ul class="nav" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element">
                            <!--
                            <span><img alt="image" class="img-circle" src="img/profile_small.jpg" /></span>
                            -->
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear">
                               <span class="block m-t-xs"><strong class="font-bold"><{\$userinfo['username']}><{if \$userinfo['nickname']}>(<{\$userinfo['nickname']}>)<{/if}></strong></span>
                                <span class="text-muted text-xs block"><{if \$userinfo['groupname']}>(<{\$userinfo['groupname']}>)<{/if}><b class="caret"></b></span>
                                </span>
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a class="J_menuItem" href="<{:url('jhy/Jhyuser/setpwd',['modulename'=>request()->module()])}>">修改登录密码</a></li>
                                <!--
                                <li><a class="J_menuItem" href="profile.html">个人资料</a></li>
                                <li><a class="J_menuItem" href="contacts.html">联系我们</a></li>
                                -->
                                <li class="divider"></li>
                                <li><a href="javascript:logout();">安全退出</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <!--左侧导航结束-->
        <!--右侧部分开始-->
        <div id="page-wrapper" class="gray-bg dashbard-1">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                    <div><{\$loginsysinfo['name']}> 管理系统</div>
                    <!--
                        <form role="search" class="navbar-form-custom" method="post" action="search_results.html">
                            <div class="form-group">
                                <input type="text" placeholder="请输入您需要查找的内容 …" class="form-control" name="top-search" id="top-search">
                            </div>
                        </form>
                    -->
                    </div>
                    <ul class="nav navbar-top-links navbar-right">

                        <li class="dropdown hidden-xs">
                            <a class="right-sidebar-toggle" aria-expanded="false">
                                <i class="fa fa-tasks"></i> 主题
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="row content-tabs">
                <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
                </button>
                <nav class="page-tabs J_menuTabs">
                    <div class="page-tabs-content">
                        <a href="javascript:;" class="active J_menuTab" data-id="index_v1.html">首页</a>
                    </div>
                </nav>
                <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
                </button>
                <div class="btn-group roll-nav roll-right">
                    <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span>

                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li class="J_tabShowActive"><a>定位当前选项卡</a>
                        </li>
                        <li class="divider"></li>
                        <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                        </li>
                        <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                        </li>
                    </ul>
                </div>
                <a href="javascript:logout();" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a>
            </div>
            <div class="row J_mainContent" id="content-main">
                <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<{:url({$params['modulename']}.'/user/home')}>" frameborder="0" data-id="index_v1.html" seamless></iframe>
            </div>
            <div class="footer">
                <div class="pull-right">&copy; 2014-2015 <a href="" target="_blank">jhy</a>
                </div>
            </div>
        </div>
        <!--右侧部分结束-->
        <!--右侧边栏开始-->
        <div id="right-sidebar">
            <div class="sidebar-container">

                <ul class="nav nav-tabs navs-3">

                    <li class="active">
                        <a data-toggle="tab" href="#tab-1">
                            <i class="fa fa-gear"></i> 主题
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="sidebar-title">
                            <h3> <i class="fa fa-comments-o"></i> 主题设置</h3>
                            <small><i class="fa fa-tim"></i> 你可以从这里选择和预览主题的布局和样式，这些设置会被保存在本地，下次打开的时候会直接应用这些设置。</small>
                        </div>
                        <div class="skin-setttings">
                            <div class="title">主题设置</div>
                            <div class="setings-item">
                                <span>收起左侧菜单</span>
                                <div class="switch">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                                        <label class="onoffswitch-label" for="collapsemenu">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="setings-item">
                                <span>固定顶部</span>

                                <div class="switch">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="fixednavbar" class="onoffswitch-checkbox" id="fixednavbar">
                                        <label class="onoffswitch-label" for="fixednavbar">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="setings-item">
                                <span>
                        固定宽度
                    </span>

                                <div class="switch">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                                        <label class="onoffswitch-label" for="boxedlayout">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="title">皮肤选择</div>
                            <div class="setings-item default-skin nb">
                                <span class="skin-name ">
                         <a href="#" class="s-skin-0">
                             默认皮肤
                         </a>
                    </span>
                            </div>
                            <div class="setings-item blue-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-1">
                            蓝色主题
                        </a>
                    </span>
                            </div>
                            <div class="setings-item yellow-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-3">
                            黄色/紫色主题
                        </a>
                    </span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <!--右侧边栏结束-->
        <!--mini聊天窗口开始-->
        <!--
        <div class="small-chat-box fadeInRight animated">

            <div class="heading" draggable="true">
                <small class="chat-date pull-right">
                    2015.9.1
                </small> 与 Beau-zihan 聊天中
            </div>

            <div class="content">

                <div class="left">
                    <div class="author-name">
                        Beau-zihan <small class="chat-date">
                        10:02
                    </small>
                    </div>
                    <div class="chat-message active">
                        你好
                    </div>

                </div>
                <div class="right">
                    <div class="author-name">
                        游客
                        <small class="chat-date">
                            11:24
                        </small>
                    </div>
                    <div class="chat-message">
                        你好，请问H+有帮助文档吗？
                    </div>
                </div>
                <div class="left">
                    <div class="author-name">
                        Beau-zihan
                        <small class="chat-date">
                            08:45
                        </small>
                    </div>
                    <div class="chat-message active">
                        有，购买的H+源码包中有帮助文档，位于docs文件夹下
                    </div>
                </div>
                <div class="right">
                    <div class="author-name">
                        游客
                        <small class="chat-date">
                            11:24
                        </small>
                    </div>
                    <div class="chat-message">
                        那除了帮助文档还提供什么样的服务？
                    </div>
                </div>
                <div class="left">
                    <div class="author-name">
                        Beau-zihan
                        <small class="chat-date">
                            08:45
                        </small>
                    </div>
                    <div class="chat-message active">
                        1.所有源码(未压缩、带注释版本)；
                        <br> 2.说明文档；
                        <br> 3.终身免费升级服务；
                        <br> 4.必要的技术支持；
                        <br> 5.付费二次开发服务；
                        <br> 6.授权许可；
                        <br> ……
                        <br>
                    </div>
                </div>


            </div>
            <div class="form-chat">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control"> <span class="input-group-btn"> <button
                        class="btn btn-primary" type="button">发送
                </button> </span>
                </div>
            </div>

        </div>
        <div id="small-chat">
            <span class="badge badge-warning pull-right">5</span>
            <a class="open-small-chat">
                <i class="fa fa-comments"></i>

            </a>
        </div>
        -->
        <!--mini聊天窗口结束-->
    </div>

    <!-- 全局js -->
    <script src="/static/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/static/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="/static/js/plugins/layer/layer.min.js"></script>
    <script src="/static/js/j_common.js"></script>

    <!-- 自定义js -->
    <script>
//自定义js

//公共配置
if(top != window){
    top.window.location.href=location.href;    
}
$(function(){

    //退出系统
    logout = function(){
       $.ajax({
        url : "<{:url('/jhy/jhylogin/logout')}>",
        type:'post',
        dataType:'json',
        data:{logincode:"<{\$loginsysinfo['code']}>"},
        success:function(d){
            if(d.code == '1'){
                location.reload();
            }
        }
        });
    }

//菜单---------------------------------------------------
	$.ajax({
		url:"<{:url('/jhy/jhymenu/menu')}>",
		type:'post',
		dataType:'json',
		data:{modulename:"{$params['modulename']}"},
		success:function(d){
			if(d.code == '1'){
		      var handle = function(menu, depth){
		            if(!depth){
                        depth = 1;
                    }
		            var depthclass="third";
		          
		            if(depth == 1){depthclass = 'second';}
		            if(depth == 2){depthclass = 'third';}
		            var html = '';
                    if(menu.son){
		                html = '<a href="#"><i class="'+menu.icon+'"></i><span class="nav-label">'+menu.title+'</span><span class="fa arrow"></span></a>';
		                var html_s = '';
		                $.each(menu.son, function(k, v){
                            //html_s += '<li><a class="J_menuItem" href="'+v.url+'"><i class="'+v.icon+'"></i>'+v.title+'</a></li>';
                            html_s += handle(v, depth+1);
                        });
                        html += '<ul class="nav nav-'+depthclass+'-level">' +html_s+ '</ul>';
                    }else{
                        html = '<a class="J_menuItem" href="'+menu.url+'"><i class="'+menu.icon+'"></i> <span class="nav-label">'+menu.title+'</span></a>'
                    }
		            return '<li>' + html + '</li>';
               }
		    
		    
		    
				var html = '';
				
				$.each(d.data, function(k,v){
		            html += handle(v);
				});
				$('#side-menu').append(html);
				
				// MetsiMenu
				$('#side-menu').metisMenu();
				//通过遍历给菜单项加上data-index属性
				$(".J_menuItem").each(function (index) {
					if (!$(this).attr('data-index')) {
						$(this).attr('data-index', index);
					}
				});
				
				function menuItem() {
					// 获取标识数据
					var dataUrl = $(this).attr('href'),
					dataIndex = $(this).data('index'),
					menuName = $.trim($(this).text()),
					flag = true;
					if (dataUrl == undefined || $.trim(dataUrl).length == 0)return false;
					
					// 选项卡菜单已存在
					$('.J_menuTab').each(function () {
						if ($(this).data('id') == dataUrl) {
							if (!$(this).hasClass('active')) {
								$(this).addClass('active').siblings('.J_menuTab').removeClass('active');
								scrollToTab(this);
								// 显示tab对应的内容区
								$('.J_mainContent .J_iframe').each(function () {
									if ($(this).data('id') == dataUrl) {
										$(this).show().siblings('.J_iframe').hide();
										return false;
									}
								});
							}
							flag = false;
							return false;
						}
					});
					
					// 选项卡菜单不存在
					if (flag) {
						var str = '<a href="javascript:;" class="active J_menuTab" data-id="' + dataUrl + '">' + menuName + ' <i class="fa fa-times-circle"></i></a>';
						$('.J_menuTab').removeClass('active');
						
						// 添加选项卡对应的iframe
						var str1 = '<iframe class="J_iframe" name="iframe' + dataIndex + '" width="100%" height="100%" src="' + dataUrl + '" frameborder="0" data-id="' + dataUrl + '" seamless></iframe>';
						$('.J_mainContent').find('iframe.J_iframe').hide().parents('.J_mainContent').append(str1);
						
						//显示loading提示
						//            var loading = layer.load();
						//
						//            $('.J_mainContent iframe:visible').load(function () {
						//                //iframe加载完成后隐藏loading提示
						//                layer.close(loading);
						//            });
						// 添加选项卡
						$('.J_menuTabs .page-tabs-content').append(str);
						scrollToTab($('.J_menuTab.active'));
					}
					return false;
				}
				
				$('.J_menuItem').on('click', menuItem);
			}else{
				layer.msg(d.msg?d.msg:'出错了');
			}
		}
	});
	//-------------------------------------------------------------------
    // 打开右侧边栏
    $('.right-sidebar-toggle').click(function () {
        $('#right-sidebar').toggleClass('sidebar-open');
    });

    // 右侧边栏使用slimscroll
    $('.sidebar-container').slimScroll({
        height: '100%',
        railOpacity: 0.4,
        wheelStep: 10
    });

    // 打开聊天窗口
    $('.open-small-chat').click(function () {
        $(this).children().toggleClass('fa-comments').toggleClass('fa-remove');
        $('.small-chat-box').toggleClass('active');
    });

    // 聊天窗口使用slimscroll
    $('.small-chat-box .content').slimScroll({
        height: '234px',
        railOpacity: 0.4
    });

    // Small todo handler
    $('.check-link').click(function () {
        var button = $(this).find('i');
        var label = $(this).next('span');
        button.toggleClass('fa-check-square').toggleClass('fa-square-o');
        label.toggleClass('todo-completed');
        return false;
    });
    //固定菜单栏
    $('.sidebar-collapse').slimScroll({
        height: '100%',
        railOpacity: 0.9,
        alwaysVisible: false
    });
    // 菜单切换
    $('.navbar-minimalize').click(function () {
        $("body").toggleClass("mini-navbar");
        SmoothlyMenu();
    });
    // 侧边栏高度
    function fix_height() {
        var heightWithoutNavbar = $("body > #wrapper").height() - 61;
        $(".sidebard-panel").css("min-height", heightWithoutNavbar + "px");
    }
    fix_height();

    $(window).bind("load resize click scroll", function () {
        if (!$("body").hasClass('body-small')) {
            fix_height();
        }
    });

    //侧边栏滚动
    $(window).scroll(function () {
        if ($(window).scrollTop() > 0 && !$('body').hasClass('fixed-nav')) {
            $('#right-sidebar').addClass('sidebar-top');
        } else {
            $('#right-sidebar').removeClass('sidebar-top');
        }
    });

    $('.full-height-scroll').slimScroll({
        height: '100%'
    });

    $('#side-menu>li').click(function () {
        if ($('body').hasClass('mini-navbar')) {
            NavToggle();
        }
    });
    $('#side-menu>li li a').click(function () {
        if ($(window).width() < 769) {
            NavToggle();
        }
    });

    $('.nav-close').click(NavToggle);

    //ios浏览器兼容性处理
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
        $('#content-main').css('overflow-y', 'auto');
    }



	$(window).bind("load resize", function () {
	    if ($(this).width() < 769) {
	        $('body').addClass('mini-navbar');
	        $('.navbar-static-side').fadeIn();
	    }
	});

	function NavToggle() {
	    $('.navbar-minimalize').trigger('click');
	}
	
	function SmoothlyMenu() {
	    if (!$('body').hasClass('mini-navbar')) {
	        $('#side-menu').hide();
	        setTimeout(
	            function () {
	                $('#side-menu').fadeIn(500);
	            }, 100);
	    } else if ($('body').hasClass('fixed-sidebar')) {
	        $('#side-menu').hide();
	        setTimeout(
	            function () {
	                $('#side-menu').fadeIn(500);
	            }, 300);
	    } else {
	        $('#side-menu').removeAttr('style');
	    }
	}
	//主题设置

    // 顶部菜单固定
    $('#fixednavbar').click(function () {
        if ($('#fixednavbar').is(':checked')) {
            $(".navbar-static-top").removeClass('navbar-static-top').addClass('navbar-fixed-top');
            $("body").removeClass('boxed-layout');
            $("body").addClass('fixed-nav');
            $('#boxedlayout').prop('checked', false);

            if (localStorageSupport) {
                localStorage.setItem("boxedlayout", 'off');
            }

            if (localStorageSupport) {
                localStorage.setItem("fixednavbar", 'on');
            }
        } else {
            $(".navbar-fixed-top").removeClass('navbar-fixed-top').addClass('navbar-static-top');
            $("body").removeClass('fixed-nav');

            if (localStorageSupport) {
                localStorage.setItem("fixednavbar", 'off');
            }
        }
    });


    // 收起左侧菜单
    $('#collapsemenu').click(function () {
        if ($('#collapsemenu').is(':checked')) {
            $("body").addClass('mini-navbar');
            SmoothlyMenu();

            if (localStorageSupport) {
                localStorage.setItem("collapse_menu", 'on');
            }

        } else {
            $("body").removeClass('mini-navbar');
            SmoothlyMenu();

            if (localStorageSupport) {
                localStorage.setItem("collapse_menu", 'off');
            }
        }
    });

    // 固定宽度
    $('#boxedlayout').click(function () {
        if ($('#boxedlayout').is(':checked')) {
            $("body").addClass('boxed-layout');
            $('#fixednavbar').prop('checked', false);
            $(".navbar-fixed-top").removeClass('navbar-fixed-top').addClass('navbar-static-top');
            $("body").removeClass('fixed-nav');
            if (localStorageSupport) {
                localStorage.setItem("fixednavbar", 'off');
            }


            if (localStorageSupport) {
                localStorage.setItem("boxedlayout", 'on');
            }
        } else {
            $("body").removeClass('boxed-layout');

            if (localStorageSupport) {
                localStorage.setItem("boxedlayout", 'off');
            }
        }
    });

    // 默认主题
    $('.s-skin-0').click(function () {
        $("body").removeClass("skin-1");
        $("body").removeClass("skin-2");
        $("body").removeClass("skin-3");
        return false;
    });

    // 蓝色主题
    $('.s-skin-1').click(function () {
        $("body").removeClass("skin-2");
        $("body").removeClass("skin-3");
        $("body").addClass("skin-1");
        return false;
    });

    // 黄色主题
    $('.s-skin-3').click(function () {
        $("body").removeClass("skin-1");
        $("body").removeClass("skin-2");
        $("body").addClass("skin-3");
        return false;
    });

    if (localStorageSupport) {
        var collapse = localStorage.getItem("collapse_menu");
        var fixednavbar = localStorage.getItem("fixednavbar");
        var boxedlayout = localStorage.getItem("boxedlayout");

        if (collapse == 'on') {
            $('#collapsemenu').prop('checked', 'checked')
        }
        if (fixednavbar == 'on') {
            $('#fixednavbar').prop('checked', 'checked')
        }
        if (boxedlayout == 'on') {
            $('#boxedlayout').prop('checked', 'checked')
        }
    }

    if (localStorageSupport) {

        var collapse = localStorage.getItem("collapse_menu");
        var fixednavbar = localStorage.getItem("fixednavbar");
        var boxedlayout = localStorage.getItem("boxedlayout");

        var body = $('body');

        if (collapse == 'on') {
            if (!body.hasClass('body-small')) {
                body.addClass('mini-navbar');
            }
        }

        if (fixednavbar == 'on') {
            $(".navbar-static-top").removeClass('navbar-static-top').addClass('navbar-fixed-top');
            body.addClass('fixed-nav');
        }

        if (boxedlayout == 'on') {
            body.addClass('boxed-layout');
        }
    }
 //计算元素集合的总宽度
    function calSumWidth(elements) {
        var width = 0;
        $(elements).each(function () {
            width += $(this).outerWidth(true);
        });
        return width;
    }
    //滚动到指定选项卡
    function scrollToTab(element) {
        var marginLeftVal = calSumWidth($(element).prevAll()), marginRightVal = calSumWidth($(element).nextAll());
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").outerWidth() < visibleWidth) {
            scrollVal = 0;
        } else if (marginRightVal <= (visibleWidth - $(element).outerWidth(true) - $(element).next().outerWidth(true))) {
            if ((visibleWidth - $(element).next().outerWidth(true)) > marginRightVal) {
                scrollVal = marginLeftVal;
                var tabElement = element;
                while ((scrollVal - $(tabElement).outerWidth()) > ($(".page-tabs-content").outerWidth() - visibleWidth)) {
                    scrollVal -= $(tabElement).prev().outerWidth();
                    tabElement = $(tabElement).prev();
                }
            }
        } else if (marginLeftVal > (visibleWidth - $(element).outerWidth(true) - $(element).prev().outerWidth(true))) {
            scrollVal = marginLeftVal - $(element).prev().outerWidth(true);
        }
        $('.page-tabs-content').animate({
            marginLeft: 0 - scrollVal + 'px'
        }, "fast");
    }
    //查看左侧隐藏的选项卡
    function scrollTabLeft() {
        var marginLeftVal = Math.abs(parseInt($('.page-tabs-content').css('margin-left')));
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").width() < visibleWidth) {
            return false;
        } else {
            var tabElement = $(".J_menuTab:first");
            var offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {//找到离当前tab最近的元素
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            offsetVal = 0;
            if (calSumWidth($(tabElement).prevAll()) > visibleWidth) {
                while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                    offsetVal += $(tabElement).outerWidth(true);
                    tabElement = $(tabElement).prev();
                }
                scrollVal = calSumWidth($(tabElement).prevAll());
            }
        }
        $('.page-tabs-content').animate({
            marginLeft: 0 - scrollVal + 'px'
        }, "fast");
    }
    //查看右侧隐藏的选项卡
    function scrollTabRight() {
        var marginLeftVal = Math.abs(parseInt($('.page-tabs-content').css('margin-left')));
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").width() < visibleWidth) {
            return false;
        } else {
            var tabElement = $(".J_menuTab:first");
            var offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {//找到离当前tab最近的元素
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            scrollVal = calSumWidth($(tabElement).prevAll());
            if (scrollVal > 0) {
                $('.page-tabs-content').animate({
                    marginLeft: 0 - scrollVal + 'px'
                }, "fast");
            }
        }
    }





    // 关闭选项卡菜单
    function closeTab() {
        var closeTabId = $(this).parents('.J_menuTab').data('id');
        var currentWidth = $(this).parents('.J_menuTab').width();

        // 当前元素处于活动状态
        if ($(this).parents('.J_menuTab').hasClass('active')) {

            // 当前元素后面有同辈元素，使后面的一个元素处于活动状态
            if ($(this).parents('.J_menuTab').next('.J_menuTab').size()) {

                var activeId = $(this).parents('.J_menuTab').next('.J_menuTab:eq(0)').data('id');
                $(this).parents('.J_menuTab').next('.J_menuTab:eq(0)').addClass('active');

                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == activeId) {
                        $(this).show().siblings('.J_iframe').hide();
                        return false;
                    }
                });

                var marginLeftVal = parseInt($('.page-tabs-content').css('margin-left'));
                if (marginLeftVal < 0) {
                    $('.page-tabs-content').animate({
                        marginLeft: (marginLeftVal + currentWidth) + 'px'
                    }, "fast");
                }

                //  移除当前选项卡
                $(this).parents('.J_menuTab').remove();

                // 移除tab对应的内容区
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == closeTabId) {
                        $(this).remove();
                        return false;
                    }
                });
            }

            // 当前元素后面没有同辈元素，使当前元素的上一个元素处于活动状态
            if ($(this).parents('.J_menuTab').prev('.J_menuTab').size()) {
                var activeId = $(this).parents('.J_menuTab').prev('.J_menuTab:last').data('id');
                $(this).parents('.J_menuTab').prev('.J_menuTab:last').addClass('active');
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == activeId) {
                        $(this).show().siblings('.J_iframe').hide();
                        return false;
                    }
                });

                //  移除当前选项卡
                $(this).parents('.J_menuTab').remove();

                // 移除tab对应的内容区
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == closeTabId) {
                        $(this).remove();
                        return false;
                    }
                });
            }
        }
        // 当前元素不处于活动状态
        else {
            //  移除当前选项卡
            $(this).parents('.J_menuTab').remove();

            // 移除相应tab对应的内容区
            $('.J_mainContent .J_iframe').each(function () {
                if ($(this).data('id') == closeTabId) {
                    $(this).remove();
                    return false;
                }
            });
            scrollToTab($('.J_menuTab.active'));
        }
        return false;
    }

    $('.J_menuTabs').on('click', '.J_menuTab i', closeTab);

    //关闭其他选项卡
    function closeOtherTabs(){
        $('.page-tabs-content').children("[data-id]").not(":first").not(".active").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
            $(this).remove();
        });
        $('.page-tabs-content').css("margin-left", "0");
    }
    $('.J_tabCloseOther').on('click', closeOtherTabs);

    //滚动到已激活的选项卡
    function showActiveTab(){
        scrollToTab($('.J_menuTab.active'));
    }
    $('.J_tabShowActive').on('click', showActiveTab);


    // 点击选项卡菜单
    function activeTab() {
        if (!$(this).hasClass('active')) {
            var currentId = $(this).data('id');
            // 显示tab对应的内容区
            $('.J_mainContent .J_iframe').each(function () {
                if ($(this).data('id') == currentId) {
                    $(this).show().siblings('.J_iframe').hide();
                    return false;
                }
            });
            $(this).addClass('active').siblings('.J_menuTab').removeClass('active');
            scrollToTab(this);
        }
    }

    $('.J_menuTabs').on('click', '.J_menuTab', activeTab);

    //刷新iframe
    function refreshTab() {
        var target = $('.J_iframe[data-id="' + $(this).data('id') + '"]');
        var url = target.attr('src');
//        //显示loading提示
//        var loading = layer.load();
//        target.attr('src', url).load(function () {
//            //关闭loading提示
//            layer.close(loading);
//        });
    }

    $('.J_menuTabs').on('dblclick', '.J_menuTab', refreshTab);

    // 左移按扭
    $('.J_tabLeft').on('click', scrollTabLeft);

    // 右移按扭
    $('.J_tabRight').on('click', scrollTabRight);

    // 关闭全部
    $('.J_tabCloseAll').on('click', function () {
        $('.page-tabs-content').children("[data-id]").not(":first").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
            $(this).remove();
        });
        $('.page-tabs-content').children("[data-id]:first").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').show();
            $(this).addClass("active");
        });
        $('.page-tabs-content').css("margin-left", "0");
    });


            //判断浏览器是否支持html5本地存储
    function localStorageSupport() {
        return (('localStorage' in window) && window['localStorage'] !== null)
    }
    
});

            
    </script>


    <!-- 第三方插件 -->
    <script src="/static/js/plugins/pace/pace.min.js"></script>

</body>

</html>
        
jhy;
    
        return str_replace('<{#', '<{$', $str);
    }
    
    
    public function createModel($params=array() ){
        if(!$params['modelname']){
            throw new Exception('模型名不能为空！参数名: $params[\'modelname\']' );
        }elseif(!$params['namespace']){
            throw new Exception('模块名不能为空！参数名: $params[\'namespace\']' );
        }

        //自动时间
        $params['updatetime'] = $params['updatetime']===true?'\'update_time\'':($params['updatetime']?('\''.$params['updatetime'].'\''):'false');
        $params['createtime'] = $params['createtime']===true?'\'create_time\'':($params['createtime']?('\''.$params['createtime'].'\''):'false');
        $updatetime = 'protected $updateTime =' . $params['updatetime'] .';';
        $createtime = 'protected $createTime =' . $params['createtime'] .';';
        

        //类型转换
        $type_ = '';
        if(is_array($params['db_type_fields'])){
            $enter = "\r\n";
            $type_ = 'protected $type = [' . $enter;
            foreach($params['db_type_fields'] as $k => $v){
                $type_ .= '\''.$k.'\' => \''.$v.'\',' . $enter;
            }
            $type_ .= '];'.$enter;
        }
        
        
        //数据库SET类型处理//enum/set等类型语言放入列表数据中
//         dump($params);die;
        
        $get_c = '';
        foreach($params['db_c_fields'] as $k => $v){
//            $funcname = $k;// Loader::parseName($name, 1)
//             $funcname = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
//                 return strtoupper($match[1]);
//             }, $k);
            $funcname = \think\Loader::parseName($k, 1);
            
            
            $carrStr = '';
            $carr = [];
            foreach($v as $kk => $vv){
                $carr[] = "'$kk' => '$vv'";
            }
            $carrStr = '[' . implode(',' ,$carr) . ']';
            $get_c .= <<<jhy

    public function get{$funcname}Attr(\$value){
        \$carr = $carrStr;
        return \$carr[\$value];
    }

jhy;
        }
        //时间类型
        if(is_array($params['db_datetime_fields'])){
            foreach($params['db_datetime_fields'] as $k => $v){
                $k = \think\Loader::parseName($k, 1);
                $get_c .= <<<jhy
                
    public function get{$k}Attr(\$value){
        if(!preg_match('/^[\d]{1,11}$/', \$value)){
            return \$value;
        }
        return date('Y-m-d H:i:s',\$value);
    }
    public function set{$k}Attr(\$value){
        if(!preg_match('/^[\d]{1,11}$/', \$value)){
            \$value = strtotime(\$value);
        }
        return \$value;
    }
                
jhy;
            }
        }    
        

        $str = <<<jhy
namespace {$params['namespace']};

use think\Model;

class {$params['modelname']} Extends Model
{

    // 开启自动写入时间戳字段
    protected \$autoWriteTimestamp = 'int';
    protected \$auto_timestamp = 'int';
    {$type_}

    {$updatetime}
    {$createtime}
   
    {$get_c}
}
jhy;

    return $str;
    }

    /**
     * 提交表单模板
     * @param  [type] $content    表单元素HTML
     * @param  [type] $submit_url [description]
     * @return [type]             [description]
     */
    public function createSubmit($submit_url){
        $submit_url = $submit_url?$submit_url:'';
        $formid = 'formid1';
        $submitid = 'submitid1';
        $str = <<<jhy
<!-- 头部开始 -->
<{include file="jhy@public/header" }>
<!-- 头部结束 -->
    
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs-container ibox-title">
                    <ul class="nav nav-tabs ">
                       
                    </ul>
                    <div class="ibox-content">
                        <div class="">
                            <form class="form-horizontal" id="{$formid}" action="$submit_url" method='post'>
                               <div class="panel-heading">
                                    <{#title}>
                                </div> 
                                <div class="">
                                    <{#html}>
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <a href="javascript:;" id="{$submitid}" class="btn btn-warning btnbottom" data-loading-text="加载中...">立即保存</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    require(['bootstrap' ,'validate', 'layer'],function($, $1, layer){
        var loading;
        $ = $.extend($, $1);
        $1('#{$formid}').validate({
            submitHandler:function(form){
                $.ajax({
                    url:"{$submit_url}",
                    type:'post',
                    cache:false,
                    data:$(form).serialize(),
                    dataType:'json',
                    beforeSend:function(XMLHttpRequest){
                        $("#{$submitid}").addClass('disabled');
                        $("#{$submitid}").button('loading');
                        loading = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2

                    },
                    complete:function(XMLHttpRequest, textStatus){
                        $("#{$submitid}").removeClass('disabled');
                        $("#{$submitid}").button('reset');
                        layer.close(loading);
                    },
                    success:function(data,textStatus){
                        if(data.code == 1){
                            var time = data.wait?(data.wait*1000):2000;
                            layer.msg(data.msg?data.msg:'success!!', {icon: 1,time:time});
                            setTimeout(function(){
                                
                                if(top.window != window){
                                    if(typeof parent.window.iframefinish == 'function'){
                                        parent.window.iframefinish()
                                    }else if(typeof top.window.iframefinish == 'function'){
                                        parent.window.iframefinish()
                                    }else if(typeof top.window.reload == 'function'){
                                        top.window.reload();
                                    }else{
                                        top.window.location.reload();
                                    }
                                    
                                }
                            },time);
                        }else{
                            layer.msg(data.msg?data.msg:'出错了',{icon:2});
                        }
                    },
                    error:function(XMLHttpRequest, textStatus, errorThrown){
                        if(XMLHttpRequest.status == 404){
                            alert('404');
                        }
                    }
                });
            }
        });
        <{#validate}>
        $("#{$submitid}").click(function(){
            $("#{$formid}").submit();
        });
    });
</script>

    <{include file="jhy@public/footer" }>
jhy;
        return str_replace('<{#', '<{$', $str);
    }

    /**
     * 单行文本框
     * @param  arr $params 参数
     *              name : 表单中文名称
     *              field: 表单提交名称
     *              min   : 最小长度
     *              max   : 最大长度
     *              readonly: 是否只读
     *              cls   : 表单样式名
     *              desc  : 表单说明、帮助
     *              type  : 文本框类型，text, password,number ...
     *              num_min: 数字的最小值
     *              num_max: 数字的最大值
     *              is_require : 是否必填
     * @param   text $val  表单值   
     * @param   string $itemid  表单组件的ID         
     * @return  string      html/js代码
     */
    public function text($params=array(), $val, $itemid, $is_search=false){
        $itemid = $itemid?$itemid:'text_'.$this->_getItemid();
        $_s = array_merge(['cls'=>'','readonly'=>''], $params);
        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';
        if(empty($_s['type'])){
            $_s['type'] = 'text';
        }
        if($is_search){
            $str = <<< jhy
<div class="form-group {$_s['cls']}" id="{$itemid}">
<label class="sr-only" for="{$itemid}_input1">{$_s['name']}</label>
<input type="{$_s['type']}" class="form-control" id="{$itemid}_input1" value="{$val}" name="{$_s['field']}" {$_s['readonly']} placeholder="{$_s['name']}">
</div>
jhy;
        }else{
            $str = <<< jhy
<div class="form-group {$_s['cls']}" id="{$itemid}">
    <div class="col-sm-2 control-label">{$_s['name']}</div>
    <div class="col-sm-4">
        <input id="{$itemid}_input1"  type="{$_s['type']}" class="form-control" value="{$val}" name="{$_s['field']}" {$_s['readonly']}>
        <span class="help-block m-b-none">{$_s['desc']}</span>
    </div>
</div>
jhy;
        }
        $valid = $this->_validatestr($params, $itemid);


        return $str;
    }

	public function password($params=array(), $val, $itemid, $is_search=false){
		$params['type'] = 'password';
		$str = $this->text($params, $val, $itemid, $is_search);
		return $str;
	}
    /**
     * 编辑器——百度
     * @param array $params
     *        以e_开头的参数名视为ueditor的参数
     *        up_modulename为上传所属模块
     *        up_cat为上传所属类别，
     * @param unknown $val
     * @param unknown $itemid
     * @param string $is_search
     */
    public function editor_baidu($params=array(), $val, $itemid, $is_search=false){
    
        $itemid = $itemid?$itemid:'text_'.$this->_getItemid();
        $url = '/static/ueditor/';
        $server = url('index/upload/ueditor', ['modulename'=>$params['up_modulename'], 'cat'=>$params['up_cat'],'_ajax'=>1]);
        
        $_s = array_merge(['cls'=>'','readonly'=>''], $params);
         
        
        $param_json = array();
        
        foreach($params as $k=>$v){
            $f = strpos($k,'e_');
            if($f !== false && $f==0){
                $param_json[substr($k,2)] = $v;
            }
        }
        
        $param_json = json_encode($param_json);
        
        
        $str = '';
//         if(!defined('EDITOR_BAIDU_INIT')){
//             //            $str .= '<script src="/static/components/hp/js/plugins/layer/laydate/laydate.js"></script>';
//             define('EDITOR_BAIDU_INIT',1);
//         }
//         $cls = $fieldinfo['setting']['cls'];
        $str .= <<<jhy
                    <div class="form-group {$_s['cls']}">
                        <label class="col-sm-2 control-label">{$_s['name']}</label>
                        <div class="col-sm-10">
                            <textarea id="{$itemid}" name="{$_s['field']}">{$val}</textarea>
                            <span class="help-block m-b-none">{$_s['desc']}</span>
                        </div>
                    </div>
jhy;
        static $flag = false;
        
        
        if($flag == false){
            $flag = true;
            $str .= <<< jhy
<script>  
(function () {

    var URL = window.UEDITOR_HOME_URL || getUEBasePath();

    window.UEDITOR_CONFIG = {
        UEDITOR_HOME_URL: '{$url}'
        , serverUrl: URL + '{$server}'
        , toolbars: [[
            'fullscreen', 'source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'print', 'preview', 'searchreplace', 'drafts', 'help'
        ]]
		// xss过滤白名单 名单来源: https://raw.githubusercontent.com/leizongmin/js-xss/master/lib/default.js
		,whitList: {
			a:      ['target', 'href', 'title', 'class', 'style'],
			abbr:   ['title', 'class', 'style'],
			address: ['class', 'style'],
			area:   ['shape', 'coords', 'href', 'alt'],
			article: [],
			aside:  [],
			audio:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'class', 'style'],
			b:      ['class', 'style'],
			bdi:    ['dir'],
			bdo:    ['dir'],
			big:    [],
			blockquote: ['cite', 'class', 'style'],
			br:     [],
			caption: ['class', 'style'],
			center: [],
			cite:   [],
			code:   ['class', 'style'],
			col:    ['align', 'valign', 'span', 'width', 'class', 'style'],
			colgroup: ['align', 'valign', 'span', 'width', 'class', 'style'],
			dd:     ['class', 'style'],
			del:    ['datetime'],
			details: ['open'],
			div:    ['class', 'style'],
			dl:     ['class', 'style'],
			dt:     ['class', 'style'],
			em:     ['class', 'style'],
			font:   ['color', 'size', 'face'],
			footer: [],
			h1:     ['class', 'style'],
			h2:     ['class', 'style'],
			h3:     ['class', 'style'],
			h4:     ['class', 'style'],
			h5:     ['class', 'style'],
			h6:     ['class', 'style'],
			header: [],
			hr:     [],
			i:      ['class', 'style'],
			img:    ['src', 'alt', 'title', 'width', 'height', 'id', '_src', 'loadingclass', 'class', 'data-latex'],
			ins:    ['datetime'],
			li:     ['class', 'style'],
			mark:   [],
			nav:    [],
			ol:     ['class', 'style'],
			p:      ['class', 'style'],
			pre:    ['class', 'style'],
			s:      [],
			section:[],
			small:  [],
			span:   ['class', 'style'],
			sub:    ['class', 'style'],
			sup:    ['class', 'style'],
			strong: ['class', 'style'],
			table:  ['width', 'border', 'align', 'valign', 'class', 'style'],
			tbody:  ['align', 'valign', 'class', 'style'],
			td:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			tfoot:  ['align', 'valign', 'class', 'style'],
			th:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			thead:  ['align', 'valign', 'class', 'style'],
			tr:     ['rowspan', 'align', 'valign', 'class', 'style'],
			tt:     [],
			u:      [],
			ul:     ['class', 'style'],
			video:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width', 'class', 'style']
		}
    };

    function getUEBasePath(docUrl, confUrl) {

        return getBasePath(docUrl || self.document.URL || self.location.href, confUrl || getConfigFilePath());

    }

    function getConfigFilePath() {

        var configPath = document.getElementsByTagName('script');

        return configPath[ configPath.length - 1 ].src;

    }

    function getBasePath(docUrl, confUrl) {

        var basePath = confUrl;


        if (/^(\/|\\\\)/.test(confUrl)) {

            basePath = /^.+?\w(\/|\\\\)/.exec(docUrl)[0] + confUrl.replace(/^(\/|\\\\)/, '');

        } else if (!/^[a-z]+:/i.test(confUrl)) {

            docUrl = docUrl.split("#")[0].split("?")[0].replace(/[^\\\/]+$/, '');

            basePath = docUrl + "" + confUrl;

        }

        return optimizationPath(basePath);

    }

    function optimizationPath(path) {

        var protocol = /^[a-z]+:\\/\\//.exec(path)[ 0 ],
            tmp = null,
            res = [];

        path = path.replace(protocol, "").split("?")[0].split("#")[0];

        path = path.replace(/\\\\/g, '/').split(/\\//);

        path[ path.length - 1 ] = "";

        while (path.length) {

            if (( tmp = path.shift() ) === "..") {
                res.pop();
            } else if (tmp !== ".") {
                res.push(tmp);
            }

        }

        return protocol + res.join("/");

    }

    window.UE = {
        getUEBasePath: getUEBasePath
    };

})();
</script>       
jhy;
        }
        $s = <<<jhy
<script>
//    require(['zeroclipboard','ueditor','editorlang'],function(zeroclipboard){
    require(['editorlang'],function(){
//		window.ZeroClipboard = zeroclipboard;
		if(window.ue_{$itemid}){window.ue_{$itemid}.destroy();}
		
		window.ue_{$itemid} = UE.getEditor('{$itemid}',{$param_json}); 
	});	
</script>
jhy;
        
        return $str . $s;

    }
    /**
     * 编辑器——Markdown 
     * @param array $params
     * @param unknown $val
     * @param unknown $itemid
     * @param string $is_search
     */
    public function editor_markdown($params=array(), $val, $itemid, $is_search=false){
        static $is_load=false;
        $o= '';
        if(!$is_load){
            $ossssssssss = <<< jhy
            <link rel="stylesheet" href="static/js/plugins/markdown/css/editormd.css" />
<script src="static/js/plugins/markdown/xss.min.js"></script>

<script src="static/js/plugins/markdown/editormd.js"></script>
<script src="static/js/plugins/markdown/plugins/image-dialog/image-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/link-dialog/link-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/preformatted-text-dialog/preformatted-text-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/code-block-dialog/code-block-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/html-entities-dialog/html-entities-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/goto-line-dialog/goto-line-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/table-dialog/table-dialog.js"></script>
<script src="static/js/plugins/markdown/plugins/reference-link-dialog/reference-link-dialog.js"></script>
<script src="static/js/plugins/markdown/lib/marked.min.js"></script>
<script src="static/js/plugins/markdown/lib/prettify.min.js"></script>
<script src="static/js/plugins/markdown/lib/flowchart.min.js"></script>
<script src="static/js/plugins/markdown/lib/raphael.min.js"></script>
<script src="static/js/plugins/markdown/lib/underscore.min.js"></script>
<script src="static/js/plugins/markdown/lib/sequence-diagram.min.js"></script>
<script src="static/js/plugins/markdown/lib/jquery.flowchart.min.js"></script>
<script src="static/js/plugins/markdown/lib/jquery.flowchart.min.js"></script>
jhy;
        }   
            

        $itemid = $itemid?$itemid:'editor_md_'.$this->_getItemid();
        $_s = $_s = array_merge(['cls'=>'','readonly'=>''], $params);
        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';
        if(!isset($_s['rows']) || $_s['rows'] <= 0){
            $_s['rows'] = 10;
        }
        
        $str = <<< jhy
            <div class="form-group {$_s['cls']}">

            <div id="{$itemid}">
               <textarea id="{$itemid}_testarea" style="display:none;" rows="{$_s['rows']}" name="{$_s['field']}">{$val}</textarea>

               
            </div>
        </div>  
        <script>
        var deps = [
        "editormd", 
                        "css!/static/js/plugins/markdown/css/editormd.css",
                        "css!/static/js/plugins/markdown/lib/codemirror/codemirror.min.css",
                "/static/js/plugins/markdown/languages/en.js", 
                "/static/js/plugins/markdown/plugins/link-dialog/link-dialog.js",
                "/static/js/plugins/markdown/plugins/reference-link-dialog/reference-link-dialog.js",
                "/static/js/plugins/markdown/plugins/image-dialog/image-dialog.js",
                "/static/js/plugins/markdown/plugins/code-block-dialog/code-block-dialog.js",
                "/static/js/plugins/markdown/plugins/table-dialog/table-dialog.js",
                "/static/js/plugins/markdown/plugins/emoji-dialog/emoji-dialog.js",
                "/static/js/plugins/markdown/plugins/goto-line-dialog/goto-line-dialog.js",
                "/static/js/plugins/markdown/plugins/help-dialog/help-dialog.js",
                "/static/js/plugins/markdown/plugins/html-entities-dialog/html-entities-dialog.js", 
                "/static/js/plugins/markdown/plugins/preformatted-text-dialog/preformatted-text-dialog.js"
        ];
            require(deps,function(editormd){
                var editorMarkdown_{$itemid} ;
                $(function(){
                    editorMarkdown_{$itemid} = editormd("{$itemid}", {
   
//                         path    : "http://phpcodemaker.com//static/js/plugins/markdown/lib/",
						width  : "98%",
						height : 720,
// 						autoHeight : true,
// 						markdown : '',
                        codeFold : true,
                        searchReplace : true,
//                         saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
                        //watch : false,
                        htmlDecode : "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启
                        emoji : true,
                        taskList : true,
                        tocm            : true,         // Using [TOCM]
                        tex : true,                     // 开启科学公式 TeX 语言支持，默认关闭
//                         previewCodeHighlight : false,  // 关闭预览窗口的代码高亮，默认开启
                        flowChart : true,  
                        sequenceDiagram : true,         // 同上
						onload : function() {
							this.previewing();
						}
                    });
                });
            });
        </script>
        {$o}
jhy;
        return $str;
    }
    

    /**
     * 单选列表
     * @param  arr $params 参数
     *              name : 表单中文名称
     *              field: 表单提交名称
     *              readonly: 是否只读
     *              cls   : 表单样式名
     *              desc  : 表单说明、帮助
     *              type  : radio=选择框，select=下接框，默认radio
     *              items : 选项[]
     *                      name|0 : 建
     *                      val |1 : 值
     *              itemskeys: 选项键值,['key','val'],默认为['name','val']
     *              default : 默认值
     *              is_muti : 是否允许多选，默认0 
     *              
     * @param   text $val  表单值   
     * @param   string $itemid  表单组件的ID         
     * @return  string      html/js代码
     */
    public function list_s($params=array(), $val, $itemid, $is_search){
        $val = (string)$val;
        $itemid = $itemid?$itemid:'list_s_'.$this->_getItemid();
        $_s = array_merge(['cls'=>'','readonly'=>''], $params);
        if($params['is_multi']){
            $_s['field'] = $_s['field'].'[]';
        }
        
        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';

        $params['itemtype'] = $params['itemtype'] ? $params['itemtype'] : 'radio';
        // $option_name = isset($items['itemskeys'])?$items['itemskeys'][0]:'name';
  //       $option_val  = isset($items['itemskeys'])?$items['itemskeys'][1]:'val';
        if($is_search){
            $params['itemtype'] = 'select';
            $params['items'] = array_merge( ['jhynull'=>'=='.$_s['name'].'=='], $params['items']);
//             if(1){
//                 $val = $params['I'];
//                 dump($params);
//             }
        }

        $itemshtml = '';
        if($params['itemtype'] == 'select'){
            $multipleStr = ($params['is_multi'])?' multiple ':'';
            //下拉选择
            foreach($params['items'] as $k => $v){
                $k = (string)$k;
                $checked = (($k === $val) || ($val === '' && $k===$params['default'])) ? 'selected':'';
                $itemshtml .= '<option '. $checked .' value="' . $k .'">'. $v .'</option>';
            }
            $itemshtml = '<select '.$multipleStr.'class="form-control" name="'. $_s['field'] .'">' . $itemshtml . '</select>';
        }elseif($params['itemtype']  == 'radio'){
            $radioTypeStr = (!$params['is_multi'])? 'radio' : 'checkbox';
            //选框
            foreach($params['items'] as $k => $v){
                $k = (string)$k;
                $checked = (($k === $val) || ($val === '' && $k===$params['default'])) ? 'checked':'';
                $itemshtml .= '<div class="'.$radioTypeStr.' ' . $itemid . '_ichecks' . ' radio-inline"><label class="radio"><input class="i-checks"' . $_s['readonly'] . ' ' . $checked .' type="'.$radioTypeStr.'" value="' . $k . '" name="' . $_s['field'] .'">'.$v.'</label></div>';
            }
        }
        $itemshtml = $itemshtml ?$itemshtml :'暂无选项！！';
        if($is_search){
            $str = <<< jhy
                <div class="form-group {$_s['cls']}">
                    
                    {$itemshtml}
                        
                  
                </div>

jhy;
        }else{
            $str = <<< jhy
                    <div class="form-group {$_s['cls']}">
                        <div class="col-sm-2 control-label">{$_s['name']}</div>
                        <div class="col-sm-4">
                            {$itemshtml}
                            <span class="help-block m-b-none">{$_s['desc']}</span>
                        </div>
                    </div>
                    <script>
                                
     require(['icheck', 'bootstrap'],function($){
        $(document).ready(function () {
            $('.{$itemid}_ichecks .i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
     });
                                
                    </script>

jhy;
        }

 
        return $str;
    }

    /**
     * [textarea description]
     * @param  array  $params [description]
     *                        rows : 行数
     * @param  [type] $val    [description]
     * @param  [type] $itemid [description]
     * @return [type]         [description]
     */
    public function textarea($params=array(), $val, $itemid){
        $itemid = $itemid?$itemid:'list_s_'.$this->_getItemid();
        $_s = $params;
        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';
        if($_s['rows'] <= 0){
            $_s['rows'] = 4;
        }
        $str = <<< jhy
        <div class="form-group {$_s['cls']}">
            <div class="col-sm-2 control-label">{$_s['name']}</div>
            <div class="col-sm-4">
               <textarea id="{$_s['itemid']}" class="form-control" rows="{$_s['rows']}" name="{$_s['field']}">{$val}</textarea>

                <span class="help-block m-b-none">{$_s['desc']}</span>
            </div>
        </div>                              
jhy;
        return $str;
    }




    /**
     * 多选列表
     * @param  arr $params 参数
     *              name : 表单中文名称
     *              field: 表单提交名称
     *              readonly: 是否只读
     *              cls   : 表单样式名
     *              desc  : 表单说明、帮助
     *              
     * @param   text $val  表单值   
     * @param   string $itemid  表单组件的ID         
     * @return  string      html/js代码
     */
    public function list_m($params=array(), $val, $itemid){
        return $this->list_s($params, $val, $itemid);
    }


    public function num($params=array(), $val, $itemid, $is_search){
        $params['type'] = 'number';
        $str = $this->text($params, $val, $itemid, $is_search);

        return $str;
    }

    public function dotnum($params=array(), $val, $itemid, $is_search){
        $params['type'] = 'number';
        $str = $this->text($params, $val, $itemid, $is_search);
        return $str;
    }
    
    public function images($params=array(), $val, $itemid){ return $this->image($params, $val, $itemid, true);}
    public function image($params=array(), $val, $itemid, $muti=false){
        
        $itemid = $itemid?$itemid:'list_s_'.$this->_getItemid();
        $_s = $params;

        $maxcount = 1;
        if($muti){
            $maxcount = intval($params['e_G']);
            if(!$maxcount) $maxcount = 5;
            $val = !empty($val)?(string)$val:'{}';
        }else{
            $val = json_encode([$val]);
        }

        $muti = $muti ? '1':'0';
        
        $server = url('jhy/jhyupload/file', ['action'=>'uploadimage','modulename'=>$params['up_modulename'], 'cat'=>$params['up_cat'],'_ajax'=>1]);
        
        //load js文件或者公共的js代码
        static $is_load = false;

        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';
        $o = '';
        if($is_load == false){
            $o = '<script></script>';
        }

        $str = <<< jhy
            <div class="form-group up_div {$_s['cls']}" id="{$itemid}">
                <div class="col-sm-2 control-label">{$_s['name']}</div>
                <div class="col-sm-8" id="up_dnd_{$itemid}">
                    <div class="upload_{$itemid}">
                        <div id="pick_{$itemid}" class="up_pick">选择图片</div>
                        <div id="filelist_{$itemid}"></div>
                    </div>
                    <span class="help-block m-b-none">{$_s['desc']}</span>
                </div>
            </div>      
    
            <script>
 require(['webuploader'], function(WebUploader){
    var count = 0, limitcount = parseInt('{$maxcount}');
    var val = JSON.parse('{$val}') || [];
    var muti = false;
    if('{$muti}' == '1'){
        muti = true;
    }
    
    $(function(){
        setTimeout(function(){
             var uploader_{$itemid} = WebUploader.create({
                 auto:true,
                 swf:'/static/js/plugins/webuploader/Uploader.swf',
                 server:'{$server}',
                 pick : '#pick_{$itemid}',
                 disableGlobalDnd:'#up_dnd_{$itemid}',
                 paste : '#up_dnd_{$itemid}',
                 
                 accept: {
                    title: 'Images',
                    extensions: 'gif,jpg,jpeg,bmp,png',
                    mimeTypes: 'image/*'
                }
            }); 
            //添加一个图片
            var addfile = function(name, src, id){
                    count ++;
                    if(!id) id='';
                    var \$li = $(
                        '<div id="'+id+'" class="file-item thumbnail" data-index="'+count+'">' +
                            '<img src="'+src+'">' +
                            '<div class="info">' + name + '</div>' +
                            '<div class="butn" style="display:none"><span class="left">1</span><span class="mid">x</span><span class="rgt">3</span></div>'+
                        '</div>'
                        ),
                    \$img = \$li.find('img');
            
            
                // \$list为容器jQuery实例
                \$list.append( \$li );
                addipt(src);
                \$li.hover(function(t){
                    \$(t.currentTarget).find('.butn').css('display','block')
                }, function(t){
                    \$(t.currentTarget).find('.butn').css('display','none')
        
                });
                
                //删除
                \$li.find('span.mid').click(function(t){
    //                 var fileid = $(t.currentTarget).closest('div.file-item').attr('id');
                    var li = $(t.currentTarget).closest('div.file-item');
                    var fileid = li.data('index');
                    $('#ipt_{$itemid}_'+count).val('');
                    li.remove();
                    count--;
                });
                
            }
            
            var addipt = function(val){
                var ipt = $('#ipt_{$itemid}_' + count);
                var fieldname = '{$_s['field']}';
                if(muti){
                    fieldname += '['+count+']';
                }
                if(ipt.length <= 0){
                    ipt = $('<input id="ipt_{$itemid}_'+count+'"  type="hidden" value="'+val+'" name="'+fieldname+'">').appendTo($('#up_dnd_{$itemid}'));
                }
            }
            
            var \$list = $('#filelist_{$itemid}');
            $.each(val, function(k,v){
                if(v){
                    addfile(v, v);
                }
            });
            
            // 当有文件添加进来的时候
            uploader_{$itemid}.on( 'fileQueued', function( file ) {
                if(count >= limitcount){
                    alert('超过最大数量');
                    uploader_{$itemid}.cancelFile(file);
                    return false;
                }
                addfile(file.name,'',file.id);
        //         var \$li = $(
        //                 '<div id="' + file.id + '" class="file-item thumbnail">' +
        //                     '<img>' +
        //                     '<div class="info">' + file.name + '</div>' +
        //                 '</div>'
        //                 ),
            
                    \$img = \$('#'+file.id).find('img');
            
        //         // \$list为容器jQuery实例
        //         \$list.append( \$li );
            
                // 创建缩略图
                // 如果为非图片文件，可以不用调用此方法。
                // thumbnailWidth x thumbnailHeight 为 100 x 100
                uploader_{$itemid}.makeThumb( file, function( error, src ) {
                    if ( error ) {
                        \$img.replaceWith('<span>不能预览</span>');
                        return;
                    }
            
                    \$img.attr( 'src', src );
                } );
            });
                   
         
             // 文件上传过程中创建进度条实时显示。
            uploader_{$itemid}.on( 'uploadProgress', function( file, percentage ) {
                var \$li = $( '#'+file.id ),
                    \$percent = \$li.find('.progress .progress-bar');
            
                // 避免重复创建
                if ( !\$percent.length ) {
                    \$percent = $('<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%;"> </div></div>')
                            .appendTo( \$li )
                            .find('span');
                }
            
                \$percent.css( 'width', percentage * 100 + '%' );
            });
            
            // 文件上传成功，给item添加成功class, 用样式标记上传成功。
            uploader_{$itemid}.on( 'uploadSuccess', function( file ,response) {
                if(response['code'] == 1){
                    $( '#'+file.id ).addClass('upload-state-done');
                    $('#'+file.id).find('img').attr('src', response.data.val);
                    var ipt = $('#ipt_{$itemid}_' + count);
    //                 if(ipt.length <= 0 ){
    //                     ipt = $('<input value="{$val}" name="{$_s['field']}" type="hidden" />').appendTo($('#{$itemid}'));
    //                 }
                    ipt.val(response.data.val);
                    
                }else{
                    var \$li = $( '#'+file.id ),
                    \$error = \$li.find('div.error');
            
                    // 避免重复创建
                    if ( !\$error.length ) {
                        \$error = $('<div class="error"></div>').appendTo( \$li );
                    }
                
                    \$error.text(response['msg']?response['msg']:'上传失败');
                 }    
            
                
            });
            
            // 文件上传失败，显示上传出错。
            uploader_{$itemid}.on( 'uploadError', function( file ) {
                var \$li = $( '#'+file.id ),
                    \$error = \$li.find('div.error');
            
                // 避免重复创建
                if ( !\$error.length ) {
                    \$error = $('<div class="error"></div>').appendTo( \$li );
                }
            
                \$error.text('上传失败');
            });
            
            // 完成上传完了，成功或者失败，先删除进度条。
            uploader_{$itemid}.on( 'uploadComplete', function( file ) {
                $( '#'+file.id ).find('.progress').remove();
            });
    
        },500);

    
    
    });

 }); 
            </script>  
            {$o}
        
jhy;


        $is_load = true;
        return $str;



    }

    /**
     * [datetime description]
     * @param  array  $params [description]
     *                       datatype : 控件类型，'year','month','date','time','datetime'
     * @param  [type] $val    [description] 空字串表示无值, 0代表 1970/1/1 8:0:0
     * @param  [type] $itemid [description]
     * @return [type]         [description]
     */
    public function datetime($params=array(), $val='', $itemid, $is_search){
        $_s = $params;
        $_s['desc'] = $_s['desc'] ? '<i class="fa fa-info-circle"></i>'. $_s['desc'] : '';
        $types = array('year','month','date','time','datetime');
        if(empty($params['datatype'])){
            $params['datatype'] = 'datetime';
        }elseif(!in_array($params['datatype'], $types)){
            throw new Exception($_s['name'] . '('.$_s['field'].')类型不正确。请选择'.implode(',',$types) . '其中一种！');
        }

        $itemid = $itemid?$itemid:'datetime_'.$this->_getItemid();
        if($is_search){
            $str = <<<jhy
<div class="form-group {$_s['cls']}" id="{$itemid}">
<input type="text" id="{$itemid}_input1" class="form-control" placeholder="{$_s['name']}" value="" name="{$_s['field']}" readonly>
</div>
jhy;
        }else{
            $str = <<<jhy
<div class="form-group {$_s['cls']}" id="{$itemid}">
    <div class="col-sm-2 control-label">{$_s['name']}</div>
    <div class="col-sm-4">
        <input type="text" id="{$itemid}_input1" class="form-control" placeholder="{$_s['name']}" value="" name="{$_s['field']}" readonly>
        <span class="help-block m-b-none">{$_s['desc']}</span>
    </div>
</div>
jhy;
        }

        $str = <<<jhy
{$str}
 <script>
    require(['laydate','jquery','j_common'], function(laydate, $, j){
        var option = {
            elem : '#{$itemid}_input1',
            trigger: 'click',
            type : '{$params['datatype']}',
            value:(''=='{$val}')?'':j.int2date('{$val}')
        };

        var layd = laydate.render(option);
        if('{$_s['readonly']}'){
            layd.config.elem=null;
        }

    });
 </script>
jhy;

        return $str;
    }

    public function date($params=array(), $val, $itemid, $is_search){
        $params['datatype'] = 'date';
        return $this->datetime($params, $val, $itemid, $is_search);
    }



    public function getValidateStr(){
        return (empty($this->validateStr))?'':<<<jhy


        require(['validate'],
        function($){
            
            {$this->validateStr}
        })

jhy;
    }

    protected function _validatestr($fieldinfo, $itemid=''){
        if(empty($itemid)) return '';
        $validJquery = '';
        $valid = array(
            'required' => $fieldinfo['is_require']?true:false
        );
        
        $fieldinfo['errortext'] = isset($fieldinfo['errortext']) ? $fieldinfo['errortext'] : '请输入正确格式';
        $msg = array();
        if(isset($fieldinfo['minl']) && $fieldinfo['minl'] > 0){
            $valid['minlength'] = (int)$fieldinfo['minl'];
            $msg['minlength']   = $fieldinfo['errortext']; //'jQuery.validator.format("At least {0} characters required!")';
        }
        if(isset($fieldinfo['maxl']) && $fieldinfo['maxl'] > 0){
            $valid['maxlength'] = (int)$fieldinfo['maxl'];
            $msg['maxlength']   = $fieldinfo['errortext'];
        }
        if(isset($fieldinfo['rule']) && $fieldinfo['rule']){
            $validJquery = <<<jhy
                        $("#{$itemid}_input1").attr('data-validaterule',"{$fieldinfo['rule']}");           
jhy;
            $valid['rule'] = true;
            $msg['rule'] = $fieldinfo['errortext'];
        
        }
        if($msg){
            $valid['messages'] = $msg;
        }
        
        
        
        $str = '$("#' . $itemid .'_input1").rules("add", '.json_encode($valid).');';
        $this->validateStr = $this->validateStr . $validJquery . $str; 
        return $validJquery . $str;
    }
}




//处理数据库的表及字段信息
class jhy_db_decompose extends jhy_base{
    // //数据库连接信息
//     protected $db_host = '';
//     protected $db_uname = '';
//     protected $db_upass = '';
//     protected $db_name= '';

    //连接资源列表
    private static $dbcon = array();

    public function __construct(){
//         parent::__construct();
        $this->protected = array_merge($this->protected, [
            'db_host',
            'db_uname',
            'db_upass',
            'db_name'
        ]);
    }

    
    /**
     * 根据表名或字段信息数组，取相应表字段信息
     * @param  [type] $tablename [description]
     * @return [type]            [description]
     */
    public function getFieldinfoByTablename($tablename){
        if(is_string($tablename)){
            $db_name = $this->get('db_name');
            $tableinfo = $this->db_query("SHOW TABLE STATUS LIKE '{$tablename}'");
            $tableinfo = $tableinfo->get('data');
            if(!$tableinfo){
                throw new Exception("table[{$tablename}] not found");
            }
            $tableinfo = $tableinfo[0];


            //从数据库中获取表字段信息
            $sql = "SELECT * FROM `information_schema`.`columns`  WHERE TABLE_SCHEMA = '{$db_name}' AND table_name = '{$tablename}' ". "ORDER BY ORDINAL_POSITION";
            $columnList = $this->db_query($sql);

            if($columnList->is_suc()){
                $columnList = $columnList->get('data');
            }else{
                return $columnList;
            }
            $fieldArr = array(); //字段列表
            $priKey = '' ;       //主键
            foreach ($columnList as $k => $v){
                $fieldArr[] = $v['COLUMN_NAME'];
                if (!$priKey && $v['COLUMN_KEY'] == 'PRI'){
                    $priKey = $v['COLUMN_NAME'];
                }
            }
            if(!$priKey){
                throw new Exception($tablename . '\'s  Primary key not found!');
            }            
        }elseif(is_array($tablename)){
            $columnList = $tablename;
        }


        $return  = array();
        foreach($columnList as $k => $v){
            $v = array_merge(['COLUMN_NAME'=>'','COLUMN_DEFAULT'=>'','IS_NULLABLE'=>'', 'DATA_TYPE'=>'','CHARACTER_MAXINUM_LENGTH'=>'','NUMERIC_PRECISION'=>'','COLUMN_COMMENT'=>'','COLUMN_COMMENT'=>'','COLUMN_KEY'=>''], $v);
            $return[$v['COLUMN_NAME']] = new jhy_fieldinfo();
            $return[$v['COLUMN_NAME']]->set('column_name', $v['COLUMN_NAME'])
                 ->set('column_default', $v['COLUMN_DEFAULT'])
                 ->set('is_nullable', $v['IS_NULLABLE'])
                 ->set('data_type', $v['DATA_TYPE'])
                 ->set('character_maximum_length', $v['CHARACTER_MAXIMUM_LENGTH'])
                 ->set('numeric_precision',$v['NUMERIC_PRECISION'])
                 ->set('column_comment',$v['COLUMN_COMMENT'])
                 ->set('column_comment_de', $this->fieldCommentDecode($v['COLUMN_COMMENT']))
                 ->set('column_key', $v['COLUMN_KEY'])
                 ->set('signed', ((strpos($v['COLUMN_TYPE'],'unsigned') === false)?'1':''));
           
        }

        return $this->suc($return);
    }

    //根据数据库的字段comment信息，返回解析后的内容
    public function fieldCommentDecode($comment=''){
        $return = array();
        if(empty($comment)) return '参数空';

        $sepStr = ':';
        $dataArr = explode($sepStr, $comment);

        $return = new jhy_fieldcommentinfo();
        $return->set('fieldlan', array_shift($dataArr));
        foreach($dataArr as $k => $v){
            if($v == '') continue;
            $i = 0;
            $keyStr = '';
            $keyfirstcharStr = '';
            while(($keyfirstcharStr = substr($v,0,1)) == 'n' && ($i++)<10){
                $v = substr($v, 1);
                $keyStr .= $keyfirstcharStr;
            }
            if($i>10) return '参数中扩展(n)数量太多';
            $keyStr = strtoupper($keyStr.substr($v,0,1));
            $valStr = substr($v, 1);
            

            if($keyStr == 'C'){
                $_tem = array();
                $kvsArr = explode(',', $valStr);
                foreach($kvsArr as $kk => $vv){
                    $kvArr = explode('=', $vv, 2);
                    $_tem[$kvArr[0]] = $kvArr[1];
                }
                $valStr = $_tem;
            }elseif($keyStr == 'S'){
                $_tem = array();
                $kvArr = explode('.', $valStr, 2);
                $_tem['tablename'] = $kvArr[0];
                $_tem['fieldname'] = $kvArr[1];
                $valStr = $_tem;
            }elseif($keyStr == 'B'){
                $_tem1 = explode('.', substr($valStr, 1));
                if(count($_tem1) != 3){
                    throw new Exception('字段'.$v['name'].'的关联信息出错。格式：b1tablename.id.name');
                }
                $comment_b['rtype'] = substr($valStr, 0, 1); //关联类型
                $comment_b['rtable'] = $_tem1[0];           //关联表名
                $comment_b['rfield'] = $_tem1[1];           //关联表字 
                $comment_b['rname']  = $_tem1[2];           //关联表主体名字段
                $valStr = $comment_b;
            }elseif($keyStr == 'H'){
                $valStr = $return->get('H') . '  ' . $valStr;
            }
            if(!preg_match('/^[a-z]*$/i', $keyStr)){
                throw new Exception('数据库字段备注中，配置名错误('.$comment.')。pls contact jhy Q695000985!');
            }
            if(isset($valStr))
                $return->set($keyStr, $valStr);
        }
        return $return;
    }
    
    //根据正则表达示，返回符合要求的表名
    public function getTablenamesByReg($reg=''){
        return $this->getTablenames($reg);
    }
    /**
     * 返回所有数据表
     * @return array 
     */
    public function getTablenames($reg=''){
        $tablesObj = $this->db_query('SHOW TABLES');

        if($tablesObj->is_suc()){
            $tablesArr = $tablesObj->get('data');
            $returnArr = array();
            foreach($tablesArr as $k => $v){
                $v = current($v);
                if($reg == '' || preg_match($reg, $v)){
                    $returnArr[] = ($v);
                }
            }
            return $this->suc($returnArr);
        }

        return $tablesObj;
    }

    //查询数据库
    private function db_query($sql){
        $db_host = $this->get('db_host');
        $db_name = $this->get('db_uname');
        $db_pass = $this->get('db_upass');
        $db_uname= $this->get('db_name');

        $key = md5($db_host . $db_name . $db_pass. $db_uname);
        $con = isset(self::$dbcon[$key])?self::$dbcon[$key]:null;
        if(empty($con)){
            $con = mysqli_connect($db_host, $db_name, $db_pass);

            if(!$con){
                return $this->err('Could not connet:' . mysqli_connect_error());
            }

            mysqli_select_db($con ,$db_uname) or die("select db error!");
            self::$dbcon[$key] = $con;
            mysqli_query($con, "SET CHARACTER_SET_CLIENT = utf8,CHARACTER_SET_CONNECTION = utf8,CHARACTER_SET_DATABASE = utf8,
                         CHARACTER_SET_RESULTS = utf8,CHARACTER_SET_SERVER = utf8,COLLATION_CONNECTION = utf8_general_ci,
                         COLLATION_DATABASE = utf8_general_ci,COLLATION_SERVER = utf8_general_ci,sql_mode=''");



        }

        try{
            $r = mysqli_query($con, $sql);//var_dump(function_exists('mysqli_fetch_all'));die;

            $result = array();
            if(function_exists('mysqli_fetch_all')){
                $result = mysqli_fetch_all($r, MYSQLI_ASSOC);
            }else{
                $rows = mysqli_num_rows($r);
                if($rows >0) {
                    while($row = mysqli_fetch_assoc($r))$result[] = $row;
                    mysqli_data_seek($r,0);
                }   
            }           
        }catch(Exception $e){
            $error = mysqli_error();
            mysqli_free_result($r);
            return $this->err('error:' . $error . $e->getMessage());
        }
        mysqli_free_result($r);

        return $this->suc($result);
    }

}
//用户输入 和 数据库字段 对应规则
class jhy_inputAndDbRole extends jhy_base{
//     //用户输入 和 数据库字段 对应规则
//     protected $jhy_inputAndDbRole;
//     //输入 对应 库  数据
//     protected $i2d;
//     //库 对应 输入 数据
//     protected $d2i;
//     //库类型数组
//     protected $d;

    public function __construct(){
        
        $this->protected = array_merge($this->protected, [
             'jhy_inputAndDbRole',
            //输入 对应 库  数据
             'i2d',
            //库 对应 输入 数据
             'd2i',
            //库类型数组
             'd'
        ]);
        
        $config = [
            //mysql 数据库字段类型
            'db'=>[
                //整数数字类
                'num' => [
                    'tinyint' => [
                        'name' => '最小整数',
                        'min'  => 0,
                        'max'  => 255,
                        'min_s'=>-128,
                        'max_s'=> 127,
                        'size' => 1
                    ],
                    'smallint' => [
                        'name' => '小整数',
                        'min'  => 0,
                        'max'  => 65535,
                        'min_s'=>-32768,
                        'max_s'=> 32767,
                        'size' => 2
                    ],
                    'mediumint'=> [
                        'name' => '中整数',
                        'min'  => 0,
                        'max'  => 16777215,
                        'min_s'=>-8388608,
                        'max_s'=> 8388607,
                        'size' =>3
                    ],
                    'int'     => [
                        'name' => '整数',
                        'min'  => 0,
                        'max'  => 4294967295,
                        'min_s'=>-2147483648,
                        'max_s'=> 2147483647,
                        'size' => 4
                    ],
                    'bigint'  => [
                        'name' => '大整数',
                        'min'  => 0,
                        'max'  => 18446744073709551615,
                        'min_s'=>-9233372036854775808,
                        'max_s'=> 9233372036854775807,
                        'size' => 8
                    ]
                ],
                //小数类
                'dotnum' => [
                    'float' => [
                        'name' => '单精度浮点数值',        
                        'size' => 4
                    ],
                    'double' => [
                        'name' => '双精度浮点数值',
                        'size' => 8
                    ],
                    'decimal' => [
                        'name' => '小数'
                    ]
                ],
                
                
                //日期时间类
                'datetime'=>[
                    'date' => [
                        'name' => '日期',
                        'min'  => '1000-01-01',
                        'max'  => '9999-12-31',
                        'size' => 3
                    ],
                    'datetime' => [
                        'name' => '日期时间',
                        'min'  => '1000-01-01 00:00:00',
                        'max'  => '9999-12-31 23:59:59',
                        'size' => 8
                    ],
                    'timestamp' => [
                        'name' => '时间戳',
                        'min'  => '1970-01-01 00:00:00',
                        'max'  => '2038-1-19 11:14:07',
                        'size' => 4
                     ]
                    
                ],
                //字符串类
                'text' => [
                    'char' => [
                        'name' => '定长字符',
                        'min'  => 0,
                        'max'  => 255,
                        'size' => -1
                    ],
                    'varchar' => [
                        'name' => '变长字符',
                        'min'  => 0,
                        'max'  => 65535,
                        'size'  => -1
                    ],
                    'text' => [
                        'name' => '长文本',
                        'min'  => 0,
                        'max'  => 65535
                    ],
                    'mediumtext' => [
                        'name' => '中等长文本',
                        'min'  => 0,
                        'max'  => 16777215
                    ],
                    'longtext' => [
                        'name' => '极大长文本',
                        'min'  => 0,
                        'max'  => 4294967295
                    ]
                    
                ],
                //列表
                'list' => [
                    'enum' => [
                        'name' => '枚举单选'
                    ],
                    'set' => [
                        'name' => '多选'
                    ]
                ]
            ],
            //用户输入数据分类
            'input' => [
                //数字
                'num' => [
                    'name' => '整数',
                    'asso' => ['tinyint'=>'', 'smallint'=>'', 'mediumint'=>'','int'=>'','bigint'=>''] //关联的数据库类型
                ],
                'dotnum' => [
                    'name' => '小数',
                    'asso' => ['decimal'=>'','float'=>'', 'decimal'=>'']
                ],
                'datetime' => [
                    'name' => '日期时间',
                    'asso' => ['timestamp'=>'/time$/i','int'=>'/time$/i']
                ],
                'date'     => [
                    'name' => '日期',
                    'asso' => ['timestamp'=>'/date$/i','int'=>'/date$/i']
                ],
                //文本
                'text' => [
                    'name' => '单行文字',
                    'asso' => ['varchar'=>'']
                ],
				'password' => [
					'name' => '密码文本框',
					'asso' => ['varchar'=>'']
				],

                'textarea' => [
                    'name'=>'多行文字',
                    'asso' => ['text'=>'/content$/i','mediumtext'=>'/content$/i', 'longtext'=>'/content$/i']
                ],
                'editor_markdown'=>[
                    'name'=>'Markdown 编辑器',
                    'asso'=>['longtext'=>'/content$/i']
                ],
                
                'editor_baidu' => [
                    'name' => '',
                    'asso' => ['longtext' => '/content$/i', 'text'=> '/content$/i']
                ],
                //列表
                'list_s' => [
                    'name' => '单选列表',
                    'asso' => ['enum'=>'', 'tinyint'=>'']
                ],
                'list_m' => [
                    'name' => '多选列表',
                    'asso' => ['set'=>'']
                ],
                'image' => [
                    'name' => '单图',
                    'asso' => ['varchar'=>'/image$/i']
                ],
                'images' => [
                    'name' => '多图',
                    'asso' => ['text'=>'/images$/i']
                    
                ]
            ]
        ];
        $d = []; //数据库字段数组
        foreach($config['db'] as $k => $v){
            foreach($v as $kk => $vv){
                $vv['class'] = $k;
                $d[$kk] = $vv;                
            }
        }
        $fields2inputtype_role = []; //自动分配输入类型的规则
        foreach($config['input'] as $k => $v){
            foreach($v['asso'] as $kk => $vv){
                $fields2inputtype_role[$k][$kk] = $vv;
            }
            
        }

        $this->set('i2d', $config)
             ->set('d2i', $fields2inputtype_role)
             ->set('d', $d);
    }
}

//字段信息
class jhy_fieldinfo extends jhy_base{
//     //字段名
//     protected $column_name;
//     //默认值
//     protected $column_default;
//     //是否可以为空
//     protected $is_nullable;
//     //字段类型
//     protected $data_type;
//     //以字符为单位的最大长度
//     protected $character_maximum_length;
//     //精度
//     protected $numeric_precision;
//     //字段备注
//     protected $column_comment;
//     //主键
//     protected $column_key;

//     //备注解析内容
//     protected $column_comment_de;
//     //是否是带符号数
//     protected $signed;

    public function __construct($data=array()){
        $this->protected = array_merge($this->protected, [
            'column_name',
            'column_default',
            'is_nullable',
            'data_type',
            'character_maximum_length',
            'numeric_precision',
            'column_comment',
            'column_key',
            'column_comment_de',
            'signed'
        ]);
        foreach($data as $k => $v){
            try{
                $this->set($k, $v);
            }catch(\Exception $e){

            }
        }
    }
}

    // //处理结果
    // class jhy_return extends jhy_base{
    //  //信息
    //  protected $msg;
    //  //数据
    //  protected $data;
    //  //错误代码
    //  protected $code;

    //  public function __construct($type, $sucdataOrErrmsg, $sucmsgOrErrcode){
    //      if($type == 'suc'){
    //          $this->suc($sucdataOrErrmsg, $sucmsgOrErrcode);
    //      }elseif($type == 'err'){
    //          $this->err($sucdataOrErrmsg, $sucmsgOrErrcode);
    //      }elseif($type != ''){
    //          throw new Exception('invalidate type! Only support suc and err');
    //      }
    //      return $this;
    //  }
    //  /**
    //   * 返回成功结果
    //   * @param  array  $data [description]
    //   * @param  string $msg  [description]
    //   * @return [type]       [description]
    //   */
    //  public function suc($data=array(), $msg=''){
    //      $this->set('code', 0)
    //          ->set('data', $data)
    //          ->set('msg', $msg);
    //      return $this;
    //  }
    //  /**
    //   * 返回失败结果
    //   * @param  string  $errmsg [description]
    //   * @param  integer $code   [description]
    //   * @return [type]          [description]
    //   */
    //  public  function err($errmsg='', $code=255){
    //      if((int)$code == 0){
    //          $code=255;
    //      }
    //      $this->set('code',$code)
    //          ->set('msg',$errmsg);
    //      return $this;
    //  }
    //  //判断是否成功
    //  public static  function is_suc($jhy_return){
    //      if( (is_subclass_of($jhy_return, __CLASS__)) && $jhy_return->get('code') === 0){
    //          return true;
    //      }else{
    //          return false;
    //      }
    //  }

    // }

//字段备注信息
class jhy_fieldcommentinfo extends jhy_base{
//     //字段名
//     protected $fieldlan;
//     //状态类       状态值=状态名           cwait=等待,done=完成
//     protected $C;
//     //页面输入控件类型   控件类型名        tlist_s
//     protected $T;
//     //页面帮助文本       帮助文本          h请输入符合xx要求的数据
//     protected $H;
//     //数字最小值或文本最小长度             x15
//     protected $X;   
//     //数字最大值或文本最大长度,默认为字段类型决定    d32
//     protected $D;
//     //关联表及字段，不含表前缀            stablename.id
//     protected $S;
//     //本表与它表关联类型(1:1对1/n:1对多)，默认n       b1
//     protected $B;
//     //列表中是否显示(1/0)                  l0
//     protected $L;
//     //是否框架及DB自主维护，默认0          k1
//     protected $K;
//     //是否必填，默认为字段notnull default 决定 a0
//     protected $A;
//     //含有状态类c列表类型(r=radio,c=checkbox)
//     protected $E;
//     //上传类型,由各模块指定
//     protected $F;
//     //上传最大文件数
//     protected $G;
//     //扩展，暂不使用
//     //protected $N;
    

    
    public function __construct(){
        $this->protected = array_merge($this->protected,[
            ////字段名
            'fieldlan',
            //状态类       状态值=状态名           cwait=等待,done=完成
            'C',
            //页面输入控件类型   控件类型名        tlist_s
            'T',
            //页面帮助文本       帮助文本          h请输入符合xx要求的数据
            'H',
            //数字最小值或文本最小长度             x15
            'X',
            //数字最大值或文本最大长度,默认为字段类型决定    d32
            'D',
            //关联表及字段，不含表前缀            stablename.id
            'S',
            //本表与它表关联类型(1:1对1/n:1对多)，默认n       b1
            'B',
            //列表中是否显示(1/0)                  l0
            'L',
            //是否框架及DB自主维护，默认0          k1
            'K',
            //是否必填，默认为字段notnull default 决定 a0
            'A',
            //含有状态类c列表类型(r=radio,c=checkbox)
            'E',
            //上传类型,由各模块指定
            'F',
            //上传最大文件数
            'G',
            //扩展，暂不使用
            'N',
            'I'
        ]);
    }

}
if(!function_exists('dump')){
    function dump($data){echo '<pre>';var_dump($data);echo '</pre>';}
}





}

    // //成功的处理结果
    // class jhy_suc extends jhy_return{
    //  public function __construct($sucdata, $sucmsg){
    //      return parent::__construct('suc', $sucdata, $sucmsg);
    //  }
    // }
    // //失败的处理结果
    // class jhy_err extends jhy_return{
    //  public function __construct($errmsg, $errcode){
    //      return parent::__construct('err', $errmsg, $errcode);
    //  }
    // }


// $base = new jhy_suc();
// $base->set('errmsg','eeeeeeeee');

// var_dump($base->getMessage());die;


//  $jhy_db_decompose=new jhy_db_decompose();
//  $jhy_db_decompose->set('db_host','127.0.0.1')
//        ->set('db_uname','root')
//        ->set('db_upass','root')
//        ->set('db_name','phpcodemaker');

//  $fieldsinfo = $jhy_db_decompose->getFieldinfoByTablename('yys_test');

// // var_dump($fieldsinfo);

// $b = new jhy_createcode();
// $b->set('fieldsinfo',$fieldsinfo);
// $bb = $b->create(['com'=>array('inputtype'=>'text')]);
// var_dump($bb);
/*
$a = jhy_db_decompose::fieldCommentDecode('来源:cdb=数据库,ipt=输入,post=仅POST,get=仅GET,getpost=GET或POST:stablenametest.testfield');
echo '<pre>';
var_dump($a);
*/