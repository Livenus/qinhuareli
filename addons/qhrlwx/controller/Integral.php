<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 商品查询
 * @type menu
 * @menudisplay false
 *
 */
class Integral extends Home{

	 /**
     * @title 商品列表
     * @type interface
     * @login 1
     * @param page   页数   1  0   
     * @return total_page   总页数  0   1  
     * @return page   当前页  0   1  每页10条
     * @return total   返回的条数  0   1  为0时没有data参数
     * @return data  商品信息   array   0   
     */
    public function listAc(){
        $request = request();
        $size = 10;

       	$page    = $request->post('page/s','1');
        $db_integral = db('qhrlwx_integral');

       	$count = $db_integral->count();
        if($count == 0){
            $this->suc(['total' => 0]);
        }

        $total_page = ceil($count/$size);
        $page = $page > $total_page ? $total_page : $page;
        $start = (($page - 1) < 0 ? 0 : $page - 1) * $size;
        $list = $db_integral->field('id,title,integral,image')
                    ->where('stat',0)
                    ->order('sort desc')
                    ->limit($start,$size)
                    ->select();
        foreach ($list as &$value) {
            $value['image'] = explode(',',$value['image']);
        }
        $return = [
            'total'      => $count,
            'total_page' => $total_page,
            'page'       => $page,
            'data'       => $list
        ];

        $this->suc($return);
        
    }
	/**
	 * @title 商品详情
	 * @type interface
	 * @login 1
	 * @param id  商品id		1	1
	 */
	public function detailAc(){
	        $request = request();
	        $id = $request->post('id/s',''); 

	        if(empty($id)){
	            $this->err('参数错误！');
	        }

	        $info = db('qhrlwx_integral')->find($id);
	        $info['image'] = explode(',',$info['image']);
	        $this->suc($info);
	        
	}

}
