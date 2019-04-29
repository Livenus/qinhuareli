<?php
namespace addons\qhrlwx\controller;

/**
 * @title 文章
 * @type menu
 *
 */

class Admin_article extends Admin_qhrlwx{
    /**
     * @title 文章列表
     * @type menu
     * @icon fa fa-list
     */
    public function listAc(){

          return j_help()->handleList([
            'topbuttons'=>'qhrlwx/Admin_article/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'qhrlwx/Admin_article/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id
             $qhrlwx/Admin_article/del#删除#glyphicon glyphicon-edit#btn btn-warning#del#id',
            'search'    => 'j.title$a.cate_name',
            'query'     => [
                'db_table' => 'qhrlwx_article',
                'db_join'  => '__QHRLWX_CATEGORY__ a#a.cate_id=j.cate_id#left',
                'db_fields' => 'j.id$j.title$j.introduction$j.pic$a.cate_name#scate_name$j.create_time$j.sort',
                'db_where'    =>'j.title#like#%#ptitle#like3#string$a.cate_name#like#%#pscate_name#like3#string',
            ]
        ]);
                
    }
    
    /**
     * @title 添加
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();
        $db_article = db('qhrlwx_article');
        //查询全部分类
        $cate = db("qhrlwx_category")->field("cate_id,cate_name")->order('sort asc')->select();
        $cate = array_column($cate,null,'cate_id');
        if ($request::instance()->isAjax()){

            $data = $this->_get_param($cate);
            $data['create_time'] = time();

            $rest = $db_article->insert($data);
             if($rest !== false){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加失败！');
            }

        }

        $this->assign("cate",$cate);
        return $this->fetch();
    }

     /**
     * @title 修改文章
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $id = $request->param('id','');
        $db_article = db('qhrlwx_article');
        $info = $db_article->find($id);
        //查询全部分类
        $cate = db("qhrlwx_category")->field("cate_id,cate_name")->order('sort asc')->select();
        $cate = array_column($cate,null,'cate_id');
        if ($request::instance()->isAjax()){

            if(empty($info)){
                $this->a_err('文章不存在！');
            }
            $data = $this->_get_param($cate);

            $rest = $db_article->where('id',$id)->update($data);

            if($rest !== false){
                $this->a_suc('修改成功！');
            }else{
                $this->a_err('修改失败！');
            }
        }

        $info['pic'] = json_encode(explode(',',$info['pic']));

        $this->assign('info',$info);
        $this->assign("cate",$cate);
        return $this->fetch();
        
    }

     
    /**
     * @title 获取数据
     * @type menu
     * @menudisplay false
     */
    private function _get_param($cate){
        $request = request();
        $data = [];
        $data['title']        = $request->param('title/s','');
        $data['content']      = $request->param('content/s','');
        $data['cate_id']      = $request->param('cate_id/s','');
        $data['introduction'] = $request->param('introduction/s','');
        $data['sort']         = $request->param('sort/s','');
        $data['pic']          = $request->param('pic/a','');
        $data['pic'] = implode(',',$data['pic']);

        if(empty($data['title'])){
            $this->a_err('文章标题不能为空！');
        }

        if(empty($data['content'])){
            $this->a_err('文章内容不能为空！');
        }

        if(empty($data['cate_id']) || empty($cate[$data['cate_id']])){
            $this->a_err('文章分类错误！');
        }

        return $data;
    }


    /**
     * @title 删除文章
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrlwx_article']);
    }
}