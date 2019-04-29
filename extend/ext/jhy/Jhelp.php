<?php 



namespace ext\jhy;

use think\Config;

use think\Db;

class Jhelp extends \think\Controller{

    

    private $jhy_db_decompose;

    private $jhy_createcode;

    

    protected $view;

    public function __construct(){

        \think\Loader::import('Createcode', ROOT_PATH  . 'extend/ext/jhy','.php');

        $jhy_db_decompose=new \jhy\jhy_db_decompose();

        $jhy_db_decompose->set('db_host',Config::get('database.hostname'))

        ->set('db_uname',Config::get('database.username'))

        ->set('db_upass',Config::get('database.password'))

        ->set('db_name', Config::get('database.database'));

        $this->jhy_db_decompose = $jhy_db_decompose;

    

        $jhy_createcode = new \jhy\jhy_createcode();

        $this->jhy_createcode = $jhy_createcode;
        
        $this->view    = \think\View::instance(Config::get('template'), Config::get('view_replace_str'));
        
    }

    

    /**

     * @title 处理删除，同时删除关联表记录

     * @param array $param  参数

     *    table 要删除的表名，不带前缀

     * @param request $request 请求

     */

    public function handleDel($param, $request){

        if(!isset($request)){

            $request = request();

        }

        

        $id = $request->param('id');

        if(!$id){

            return $this->error('参数错误!');

        }

        $tablename = $param['table'];

        

        //解析表字段

        $jhy_db_decompose = $this->jhy_db_decompose;

        $fieldsinfo = $jhy_db_decompose->getFieldinfoByTablename(Config::get('database.prefix') . $tablename);

        $fieldsinfo = $fieldsinfo->get('data');

        

        

        $relations = [];

        $vals = Db::table('__'.strtoupper($tablename).'__')->find($id);

        foreach($fieldsinfo as $k => $v){

            $_tem = $v->get('column_comment_de')->get('B');

            if($_tem){

                $relations[$k] = $_tem;

        

                if($_tem['rtype'] == 'n'){

                    //检查关联表，此表：它表 为1对多时，它表有关系数据时不能删除

                    if(Db::table('__'.strtoupper($_tem['rtable']).'__')->where([$_tem['rfield']=>$vals[$k]])->count($_tem['rfield'])){

                        return $this->error('请先删除子目下的内容才能执行此删除操作!');

                    }

                }elseif($_tem['rtype'] == '1'){

                    //此表：它表 为1对1时,同时删除关联表

                    Db::table('__'.strtoupper($_tem['rtable']).'__')->where([$_tem['rfield']=>$vals[$k]])->delete();

                }

            }

        }

        //删除主表

        Db::table('__'.strtoupper($tablename.'__'))->delete($id);

        

        return $this->success('删除成功!');

    }

    

    

    public function handleAdd($param, $request, $is_add=false){

        return $this->handleEdit($param, $request, true);

    }

    



    /**

     * @title 处理修改

     * @param array $param 参数

     *    table 数据表名

     *    name 修改窗体的标题

     *    values 设置此值，优先级大于数据库里值显示在表单中

     *    formfields 表单的字段名，逗号分隔，且字段名在数据表中存在，默认为本表全部字段

     *    notablefields 非数据表字段。设置此值，数据表则失效

     *    notablevalues 非数据表值。

     *    savefields    保存数据时需要的字段，如无则POST全部

     * @param array $request 请求

     *    id  要修改实体ID

     *    pid 要修改实体的上级ID,没有可不填

     * @param bool  $is_add 是否为请添加

     */

    public function handleEdit($param, $request, $is_add=false){

        if(!isset($request)){

            $request = request();

        }



        $opInfo = $param;

        $opInfo['type'] = $is_add?'add':'edit';

        

        //元素ID

        $id = $request->param('id');

        $pid = $request->param('pid');



        $tablename = $opInfo['table'];

        

        if($opInfo['type'] == 'edit'){

            if($opInfo['notablefields']){

                $vals =  $opInfo['notablevalues'];            

            }else{

                $vals = j_model($tablename)->find($id);

                if($vals) $vals = $vals->getData();

                if(isset($opInfo['values']) && is_array($opInfo['values']))

                    $vals = array_merge($vals, $opInfo['values']);

            }

        }elseif($opInfo['type'] == 'add'){

            $vals = [];

        }else{

            return 'type not found!!';

        }

        

        

        $config = array(

            'title'   => $opInfo['name'],

            'table'    => $tablename,

            'type'    => $opInfo['type']

        );

        //提交

        $jhy_db_decompose = $this->jhy_db_decompose;

        

        

        if($opInfo['notablefields']){

            $fieldsinfo = $opInfo['notablefields'];

        }else{

            $fieldsinfo = $jhy_db_decompose->getFieldinfoByTablename(Config::get('database.prefix') . $config['table']);

            $fieldsinfo = $fieldsinfo->get('data');

            //表单字段

            if($opInfo['formfields']){

                $fieldArr = explode(',', $opInfo['formfields']);

                $_fields =[];

                foreach($fieldArr as $k => $v){

                    $_fields[$v] = $fieldsinfo[$v];

                }

                $fieldsinfo = ($_fields);

            }

        }

        

        $jhy_createcode = $this->jhy_createcode;

        $jhy_createcode->set('fieldsinfo',$fieldsinfo); //处理一下参数

        $fieldsinfo = $jhy_createcode->get('fieldsinfo');

        $diy_fieldsinfo = [];

        //关联

        // $relations = [];

        foreach($fieldsinfo as $k => $v){

            if(is_string($v)){

                return $v;

            }elseif(!isset($v)){

                return 'The field `'.$k . '` is not found!';

            }elseif(is_array($v)){

                //$v = $jhy_createcode;

            }

            $_tem = $v->get('column_comment_de');

            if(is_string($_tem)){

                return $_tem;

            }

            $_tem = $_tem->get('B');

            if(!empty($_tem)){

                //1对多

                if($_tem['rtype'] == 'm'){

                    $_list = Db::table('__'.strtoupper($_tem['rtable']).'__')->field($_tem['rname'] . ',' . $_tem['rfield'])->select();

                    $_tem2 = [];

                    foreach($_list as $kk => $vv){

                        $_tem2[$vv[$_tem['rfield']]] = '(#'.$vv[$_tem['rfield']].')' .$vv[$_tem['rname']];

                    }

                    $_list = $_tem2;

        

                    // $relations[$v->get('column_name')] = $_tem;

                    $diy_fieldsinfo[$v->get('column_name')] = ['inputtype'=>'list_s','items'=>$_list,'default'=>(string)$pid,'is_multi'=>false];

                }

        

            }

            //

            $_tem = $v->get('column_comment_de')->get('C');

            if(!empty($_tem)){

                $diy_fieldsinfo[$v->get('column_name')] = ['inputtype'=>'list_s'];

            }

        }

         

        if($request->isPost()){



            $model = j_model($tablename);



            $postData = $request->post();

            if(isset($opInfo['savefields'])){

                $_postData = [];

                foreach(explode(',', $opInfo['savefields']) as $k => $v){

                    $_postData[$v] = $postData[$v];

                }

                $postData = $_postData;

            }else{

                

            }

            //数据验证，是否符合数据库字段的要求

            $supportdata = new \jhy\jhy_inputAndDbRole();

            

            $roledate = $supportdata['d'];

            

            foreach($postData as $k => $v){

                $_type = $fieldsinfo[$k]['data_type'];

                $class = $roledate[$_type]['class']; 

                $_fieldlan = $fieldsinfo[$k]['column_comment_de']['fieldlan'];

                if($class == 'num'){

                    //比较数的大小

                    

                    if($v['signed']){

                        //有符号数

                        $_max = $roledate[$_type]['max'];

                        $_min = $roledate[$_type]['min'];

                    }else{

                        $_max = $roledate[$_type]['max_s'];

                        $_min = $roledate[$_type]['min_s'];

                    }

                    if(null != ($fieldsinfo[$k]['column_comment_de']['D'])){

                        $_max = min($_max, $fieldsinfo[$k]['column_comment_de']['D']);

                    }

                    if(null != ($fieldsinfo[$k]['column_comment_de']['X'])){

                        $_min = max($_min, $fieldsinfo[$k]['column_comment_de']['X']);

                    }

                    if(($v > $_max || $v < $_min)){

                        return $this->error('['.$_fieldlan .']超出范围。请输入'.$_min . '到' .$_max.'之间的数。');

                    }

                }elseif($class == 'text'){

                    //检查文本长度

                    $fieldsinfo[$k]['character_maximum_length'] = (isset($fieldsinfo[$k]['character_maximum_length'])?$fieldsinfo[$k]['character_maximum_length']:255);

                    $_max = min($roledate[$_type]['max'], ($fieldsinfo[$k]['character_maximum_length']));

                    $_min = 0;

                    

                    if(null != ($fieldsinfo[$k]['column_comment_de']['D'])){

                        $_max = min($_max, $fieldsinfo[$k]['column_comment_de']['D']);

                    }

                    if(null != ($fieldsinfo[$k]['column_comment_de']['X'])){

                        $_min = max($_min, $fieldsinfo[$k]['column_comment_de']['X']);

                    }

                    if($_max && strlen($v) > $_max ){

                        return $this->error('['.$_fieldlan .']长度超出范围。请输入长度小于'.$_max.'的文本');

                    }elseif(strlen($v) < $_min){

                        return $this->error('['.$_fieldlan .']长度超出范围。请输入长度大于'.$_min.'的文本');

                    }

                 

                    

                    

                }elseif($class == 'list'){

                    //提交的值是否在选项中

                    if(!array_key_exists($v, $fieldsinfo[$k]['column_comment_de']['C'])){

                        return $this->error('['.$_fieldlan .']超出范围。请选择正确的项.');

                    }

                   

                }

                

            }

            

            

//            dump($postData);die;

            $dataModel = $model;

            if($opInfo['type'] == 'edit'){

                unset($postData['id']);

                $dataModel->data($postData,true);

                $dataModel->isUpdate(true)->save([],['id'=>$id]);

            }elseif($opInfo['type'] == 'add'){

        

                $dataModel->data($postData,true);

                $dataModel->isUpdate(false)->save();

            }

        

            return $this->success();

        }

        

        $htmlArr = $jhy_createcode->createFormitems($diy_fieldsinfo,'',$vals);

        

        $html = '';

        foreach($htmlArr['item'] as $k => $v){

            $html .= $v['html'];

        }

        

        //		dump($htmlArr);

        $pageContent = $jhy_createcode->createSubmit();

        //dump($html);die;

        $pageContent = $this->view->fetch($pageContent,

            array_merge(['html'   => $html,

                'validate'=> $htmlArr['validate'],

            ], $config)

        

            ,[],[],true);
        // file_put_contents('sos.txt', $pageContent."\r\n\r\n",FILE_APPEND);
        return ($pageContent);

    }

    

    /**

     * @title 处理列表

     * @param array $param 参数列表

  

     *    topbuttons: 列表顶部的操作按扭

     *    buttons   : 每条记录后面的操作按钮

     *    search    : 搜索条件 

     *    query     : 查询

     *       query.db_where:  查询条件

     *       query.db_fields:  查询字段

     *       query.db_orderby:  查询排序

     *       query.db_join : 查询连表

     *       query.db_table   : 查询表

     *       query.notabledata : 非数据表数据

     *       query.notablefields : 非数据表字段

     *   loginid    : 登录系统ID，用于检查权限并隐藏功能按钮

     * @param array $request 请求参数

     * @return html|json

     */

    public function handleList($param=[], $request){

        if(!isset($request)){

            $request = request();

        }

        

        //list 必须要查询

        $queryinfo = $param['query'];

        if(!(isset($queryinfo) && is_array($queryinfo))){

            return '出错了(query)';

        }

        $opInfo = $param;

        

        

        $tplname = md5(json_encode($param)) . '_list.html';
        $filename = RUNTIME_PATH . 'jhy/jhy/view/' . $tplname;

        if(!is_file($filename) || Config::get('app_debug')){

            //表前缀

            $tablefixed = Config::get('database.prefix'); 

                	

    

            //查询参数

            $where = $order = $join = $fields = $buttons = $search = array();

            //本次查询所需要的数据表，及其字段[table1=>[f1=>falias1,f2=>falias2],table2=[]]

            $tables2fields = array();

            //

            $tables2fieldsInfo = array();

            //本次查询所需要的别名，及对应的表名 [alias1=>table1,...]

            $alias2table = array();

            

            //添加第一张表

            $tables2fields[$tablefixed. $queryinfo['db_table']] = array();

            $alias2table['j'] = $tablefixed. $queryinfo['db_table'];

            

            $wherePostOrGet = array();

            //解析查询条件

            if($queryinfo['db_where']){

                $_tem1 = explode('$', $queryinfo['db_where']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    $_count = count($_tem2);

                    if($_count == 6){ //post 或 get

                        $wherePostOrGet[$_tem2[0]] = substr($_tem2[3],1);

                        $_postOrGet = substr($_tem2[3],0,1)=='p'?'post':'param';

                        $val = $request->$_postOrGet(substr($_tem2[3],1));

                        if($val == 'null' || $val == 'not null' || $val == 'jhynull'){

                            unset($val);

                        }

                        if(isset($val)){

                            //like

                            if('like1' == $_tem2[4]){ //

                                $val = $val . '%';

                                $_tem2[4] = substr($_tem2[4], 0, strlen($_tem2[4])-1);

                            }elseif('like2' == $_tem2[4]){

                                $val = '%'. $val;

                                $_tem2[4] = substr($_tem2[4], 0, strlen($_tem2[4])-1);

                            }elseif('like3' == $_tem2[4]){

                                $val = '%'. $val . '%';

                                $_tem2[4] = substr($_tem2[4], 0, strlen($_tem2[4])-1);

                            }elseif('like' == $_tem2[4]){

                                continue;

                            }

                            if($_tem2[5] == 'string'){$val = (string)$val;}

                            elseif($_tem2[5] == 'int'){$val = (int)$val;}

                            elseif($_tem2[5] == 'float'){$val = (float)$val;}

                            else{return 'invalidate input type ' . $_tem2[5] ;}

                            $where[$_tem2[0]] = [$_tem2[4],$val];

                        }

                    }

                    //无POST/GET,

                    if(empty($where[$_tem2[0]])){

                        if(isset($_tem2[2])){

                            $val = $_tem2[2];

                            //like

                            $continue=false;

                            if('like1' == $_tem2[1]){ //

                                $val = $val . '%';

                                $_tem2[1] = substr($_tem2[1], 0, strlen($_tem2[1])-1);

                            }elseif('like2' == $_tem2[1]){

                                $val = '%'. $val;

                                $_tem2[1] = substr($_tem2[1], 0, strlen($_tem2[1])-1);

                            }elseif('like3' == $_tem2[1]){

                                $val = '%'. $val . '%';

                                $_tem2[1] = substr($_tem2[1], 0, strlen($_tem2[1])-1);

                            }elseif('like' == $_tem2[1]){

                                $continue = true;

                            }

                            //           dump($continue);

                            if(!$continue || request()->isGet()){

                                if($_count  == 3 || $_count == 6){ //无post或get

                                    $where[$_tem2[0]] = [$_tem2[1],$val];

                                }

                            }

                        }

                    }

                    if(strval($val) == ''){

                        unset($where[$_tem2[0]] );

                    }

            

            

                }

            }

                    

            //解析查询排序

            if($queryinfo['db_orderby']){

                $_tem1 = explode('$', $queryinfo['db_orderby']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    $_count = count($_tem2);

                    if($_count == 3){  //post 或 get

                        $_postOrGet = substr($_tem2[2],0,1)=='p'?'post':'get';

            

            

                        $val = $request->$_postOrGet(substr($_tem2[2],1).'_o');

            

                        if(!isset($val)){

                            $val = (string)$_tem2[1];

                        }else{

                            $val = (string)$val;

                        }

            

                    }

            

                    if($_count == 2 || isset($val)){

                        if($_count == 2) $val = (string)$_tem2[1];

                        if(!in_array($val, array('desc','asc'))){

                            return 'invalidate order type(' . $_tem2[0] . '=>' . $val.')! only support asc/desc !';

                        }

                        $order[$_tem2[0]] = $val;

                    }

                }

            }

                    

            //解析连表

            if($queryinfo['db_join']){

                $_tem1 = explode('$', $queryinfo['db_join']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    $_count = count($_tem2);

                    if($_count == 3){

                        if(!in_array($_tem2[2],array('inner','left','right','full'))){

                            return 'invalidte join type('.$_tem2[2].')! only support inner/left/right/full !!';

                        }

                        $join[] = array($_tem2[0], $_tem2[1], $_tem2[2]);

                        $alias2table[jhy_getFullTablename($_tem2[0], $tablefixed, 'alias')] =   jhy_getFullTablename($_tem2[0], $tablefixed, 'tablename');

                    }

                }

            }

                    

            //解析查询字段

            if( !isset($queryinfo['notablefields']) && !$queryinfo['db_fields'] || $queryinfo['db_fields'] == '*'){

                $jhy_db_decompose = $this->jhy_db_decompose;

                $fieldsinfo = $jhy_db_decompose->getFieldinfoByTablename(Config::get('database.prefix') . $queryinfo['db_table']);

                if($fieldsinfo){

                    $fieldsinfo = $fieldsinfo->get('data');

                    $queryinfo['db_fields'] = 'j.'.implode('$j.',array_keys($fieldsinfo));

            

                }else{

                    return '表名错误';

                }

            }

                    

            if($queryinfo['db_fields']){

                $_tem1 = explode('$', $queryinfo['db_fields']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    $_count = count($_tem2);

            

                    $_tem3 = explode('.',$_tem2[0]);

                    $fields[$_tem2[0]] = (isset($_tem2[1]) && !empty($_tem2[1]))?$_tem2[1]:$_tem3[1];

                    $tables2fields[$alias2table[$_tem3[0]]][$_tem3[1]] = (isset($_tem2[1]) && !empty($_tem2[1]))?$_tem2[1]:$_tem3[1];

            

            

                }

            }

                    

            //每条记录后台的操作按钮

            if($opInfo['buttons']){

                $_tem1 = explode('$', $opInfo['buttons']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    

                    //权限

                    if(isset($param['loginid']) && $param['loginid'] > 0){

                        $loginid = $param['loginid'];

                        $mcaArr = explode('/', trim($_tem2[0],'/'));

                        $mca = $mcaArr[0].'/'. $mcaArr[1].'/'. $mcaArr[2];

                        if(true !== j_hasAuth($loginid, 0, $mca)){

                          

                            continue;

                        }

                    }

                    

                    $buttons[$k]['opid'] = $_tem2[0];

                    $buttons[$k]['name'] = $_tem2[1];

                    $buttons[$k]['icon'] = $_tem2[2];

                    $buttons[$k]['classname'] = $_tem2[3];

                    $buttons[$k]['opname'] = $_tem2[4];

                    $buttons[$k]['opvars'] = $_tem2[5];

                    //查询按钮操作的URL

                    $buttons[$k]['opvars'] .= ',!/index.php?s=' . $buttons[$k]['opid'];

            

                }

            }

                    

            //列表顶部功能按钮

            if($opInfo['topbuttons']){

                $_tem1 = explode('$', $opInfo['topbuttons']);

                foreach($_tem1 as $k => $v){

                    $_tem2 = explode('#', $v);

                    

                    //权限

                    if(isset($param['loginid']) && $param['loginid'] > 0){

                        $loginid = $param['loginid'];

                        $mcaArr = explode('/', trim($_tem2[0],'/'));

                        $mca = $mcaArr[0].'/'. $mcaArr[1].'/'. $mcaArr[2];

                        if(true !== j_hasAuth($loginid, 0, $mca)){

                    

                            continue;

                        }

                    }

                    

                    $topbuttons[$k]['opid'] = $_tem2[0];

                    $topbuttons[$k]['name'] = $_tem2[1];

                    $topbuttons[$k]['icon'] = $_tem2[2];

                    $topbuttons[$k]['classname'] = $_tem2[3];

                    $topbuttons[$k]['opname'] = $_tem2[4];

                    $topbuttons[$k]['opvars'] = $_tem2[5];

                    //查询按钮操作的URL

                    $topbuttons[$k]['opvars'] .= ',!/index.php?s=' . $topbuttons[$k]['opid'];

            

                }

            }

                    

                    

                    

            $jhy_db_decompose = $this->jhy_db_decompose;

            

            //列表搜索条件

            if($opInfo['search']){

                $_tem1 = explode('$', $opInfo['search']);

                //entity查询是否支持

                foreach($_tem1 as $k => $v){

                    if(array_key_exists($v, $where)){

                        $_tablealias = substr($v, 0, strpos($v, '.'));

                        $_tablefield = substr($v, strpos($v, '.')+1);

                        $_tablename = $alias2table[$_tablealias];

            

                        if(empty($tables2fieldsInfo[$_tablename])){

                            // 							    dump($tables2fieldsInfo);

                            $tables2fieldsInfo[$_tablename] = $jhy_db_decompose->getFieldinfoByTablename($_tablename);

            

                        }else{

                             

                        }

                        $search[$_tablename.$_tablefield] = $tables2fieldsInfo[$_tablename]->get('data')[$_tablefield];

                        //搜索中， POST GET 参数名更改

                        if($wherePostOrGet[$_tablealias . '.'.$_tablefield]){

                            $search[$_tablename.$_tablefield]->set('column_name', $wherePostOrGet[$_tablealias . '.'.$_tablefield]);

                        }

                    }

                }

            }

                    

                    

            $jhy_db_decompose = $this->jhy_db_decompose;

            $jhy_createcode = $this->jhy_createcode;

            $fieldsinfo = new \jhy\jhy_db_decompose();

            //本次查询所用的表字段信息

            $_odata = array();

            $_Cdata = array(); //enum/set等类型语言放入列表数据中

            

            if(isset($queryinfo['notablefields'])){

                $_odata = $queryinfo['notablefields'];

            }else{

                foreach($tables2fields as $k => $v){

                    $_fieldsinfo = $jhy_db_decompose->getFieldinfoByTablename($k)->get('data');

                    foreach($_fieldsinfo as $kk => $vv){

                        $_tem1 = $vv['column_name'];

        

                        if(isset($v[$_tem1])){

                            $vv['column_name']= $v[$_tem1];

                            $_odata[] = $vv;

                            if($vv->get('column_comment_de')->get('C')){

                                $_Cdata[$_tem1] = $vv->get('column_comment_de')->get('C');

                            }

                        }

                    }

                }

            }

            $fieldsinfo->set('data', $_odata);

    

            $config['filename'] =$filename;

            $config['fields'] = $fieldsinfo;

            $config['url'] = $request->url(true);

            $config['buttons'] = $buttons;

            $config['topbuttons'] = $topbuttons;

            $config['search']   = $search;

    

            $html = $jhy_createcode->createlist($config);

        }

                

        $pageContent = $this->view->fetch($filename,array_merge(['html' => $html], $config));



        if($request->isPost()){

            $offset = (int)$request->post('offset');

            $limit  = (int)$request->post('limit');

            $page = (int)$request->post('page');

            if(!$page && $offset){

                $page = $offset/$limit+1;

            }

            $page = max($page, 1);

      

            if(isset($queryinfo['notablefields'])){

                $_list = $queryinfo['notabledata'];

                $count = count($_list);

                //处理分页

                $start = ($page-1)*$limit;

                $end = $start + $limit;

                $i=0;

                $list = [];

                foreach($_list as $k => &$v){

                    if(++$i > $start && $i < $end){

                        $list[] = $v;

                    }

                }

            }else{

                $tablename = ucfirst($queryinfo['db_table']);

    

                $dataModel = j_model($tablename)->alias('j')

                ->join($join)

                ->where($where);

                $dataCountModel = clone $dataModel;

                $list = $dataModel->field($fields)->page($page, $limit)->order($order)->select();

                $count = $dataCountModel->count();

            }

            return json(['rows' => $list, 'total' => $count]);

        }

        return $pageContent;



    }

}

/**

 * 返回数据库名

 * @param  [type] $tablenamestr  __TABLENAME1__ a 或 jhy_tablename1 a

 * @param  [type] $prefix       [description]

 * @param  string $type         [description]

 * @return [type]               [description]

 */

function jhy_getFullTablename($tablenamestr, $prefix, $type='all' /*all/tablename/alias*/){



    $tableArr = explode(' ', $tablenamestr);

    $_match = array();

    if(preg_match('/__([A-Z_]*)__/', $tableArr[0], $_match)){

        $tablename = $prefix.strtolower($_match[1]);

    }else{

        $tablename = $tableArr[0];

    }

    $alias = $tableArr[1];

    $return = array('tablename'=>$tablename, 'alias'=>$alias);

    if($type == 'all'){

        return $return;

    }else{

        return $return[$type];

    }

}