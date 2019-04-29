<?php
namespace addons\qhrlwx\controller;

/**
* @title 积分礼品
* @type menu
*/
class Admin_integral extends Admin_qhrlwx{
	/**
	 * @tilte 积分礼品列表
	 * @type menu
	 * @icon fa fa-list
	 */
	public function listAc(){

          return j_help()->handleList([
            'topbuttons'=>'qhrlwx/Admin_integral/add#新增#glyphicon glyphicon-plus#btn btn-primary#add##',
            'buttons'   =>'qhrlwx/Admin_integral/edit#修改#glyphicon glyphicon-edit#btn btn-warning#edit#id
             $qhrlwx/Admin_integral/del#删除#glyphicon glyphicon-edit#btn btn-warning#del#id',
            'search'    => 'j.title',
            'query'     => [
                'db_table' => 'qhrlwx_integral',
             
                'db_fields' => 'j.id$j.title$j.content$j.image$j.integral$j.create_time$j.sort$j.stat$j.stock',
                'db_where'    =>'j.title#like#%#ptitle#like3#string',
            ]
        ]);
                
    }
	
	 /**
     * @title 添加礼品
     * @type menu
     * @menudisplay false
     */
    public function addAc(){
        $request = request();
        $db_integral = db('qhrlwx_integral');
       
       
        if ($request::instance()->isAjax()){

            $data = $this->_get_param();
            $data['create_time'] = time();
        
            $rest = $db_integral->insert($data);
             if($rest !== false){
                $this->a_suc('添加成功！');
            }else{
                $this->a_err('添加失败！');
            }

        }

       return $this->fetch();
    }

     /**
     * @title 修改礼品
     * @type menu
     * @menudisplay false
     */
    public function editAc(){
        $request = request();
        $id = $request->param('id','');
        $db_integral = db('qhrlwx_integral');
        $info = $db_integral->find($id);
       
        if ($request::instance()->isAjax()){

            if(empty($info)){
                $this->a_err('礼品不存在！');
            }
            $data = $this->_get_param();
            $rest = $db_integral->where('id',$id)->update($data);

            if($rest !== false){
                $this->a_suc('修改成功！');
            }else{
                $this->a_err('修改失败！');
            }
        }

        $info['image'] = json_encode(explode(',',$info['image']));

        $this->assign('info',$info);
       
        return $this->fetch();
        
    }


    /**
     * @title 删除礼品
     * @type menu
     * @menudisplay false
     */
    public function delAc(){
        return j_help()->handleDel(['table'=>'qhrlwx_integral']);
    }


     /**
     * @title 获取数据
     * @type menu
     * @menudisplay false
     */
    private function _get_param(){
        $request = request();
        $data = [];
        $data['title']        = $request->param('title/s','');
        $data['content']      = $request->param('content/s','');
        $data['integral']      = $request->param('integral/s','');
      	$data['sort']         = $request->param('sort/s','');
      	$data['stat']         = $request->param('stat/s','');
        $data['stock']         = $request->param('stock/s','');
        $data['image']          = $request->param('image/a','');
        $data['image'] = implode(',',$data['image']);

        if(empty($data['title'])){
            $this->a_err('礼品名称不能为空！');
        }

        if(empty($data['content'])){
            $this->a_err('礼品详细介绍不能为空！');
        }

        if(empty($data['integral'])){
            $this->a_err('礼品兑换积分不能为空！');
        }
       
        return $data;
    }
}