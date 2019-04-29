<?php
namespace addons\qhrlwx\controller;

/**
 * @title 订单
 * @type menu
 * @menudisplay false
 */
class Order extends Home{
	/**
	 * @title 订单页面
	 * @type interface
	 * @login 1
	 * @param  id 订单id 
	 *
	 */
	public function detailAc(){
		$request= request();
		$id = $request->post('id/s','');

		if (empty($id)) {
			$this->err('参数错误!');
		}

		$db_qhrlwx_order = db('qhrlwx_order');
		$field = 'a.price,b.title,b.image,b.integral';
		$info = $db_qhrlwx_order->alias("a")->field($field)
				->join("qhrlwx_integral b","a.integral_id=b.id","left")
				->where('id',$id)
				->find();
		$info['image'] = json_encode(explode(',',$info['image']));

		$this->suc($info);
	}

	/**
	 * @title 添加订单
	 * @type  interface 
	 * @login  1
	 * @param id  积分商品id   1    1
	 * @param integral  积分商品兑换所需积分  1   1
	 * @param  house_id   房间id   1    1 
	 */
	public function addAc(){
		//$user = $this->getLoginUserinfo();
		$request = request();
		$data = [];
        $data['user_id'] = $user['id'] = 10;
        $data['create_time'] = time();
        $data['house_id'] = $request->post('house_id/d','');
        $data['integral_id'] = $request->post('id/d','');
        $data['price'] = $request->post('integral/s','');

		$info = db('qhrlwx_relation_house')->where('user_id',$user['id'])
				->where('house_id', $data['house_id'])
				->select();

        if(empty($data['house_id'])){
            $this->err('参数错误！');
        }
        if ($info) {
        	
	        $re = db('qhrlwx_order')->insert($data);

	        if ($re) {
	        	$this->suc('订单添加成功!');
	        }else{
	        	$this->err('订单添加失败!');
	        }
        }else{
        	$this->err('房屋参数错误!');
        }
    }

	/**
	 * @title 保存订单
	 * @type  interface
	 * @login  1
	 * @param  id  订单id  1   1
	 * 
	 */
	public function editAc(){
		//用户确认兑换商品以后,将订单表的状态更改为已兑换
		//$user = $this->getLoginUserinfo();
		$request = request();
		$id = $request->post('id/d','');
		$user_id = $user['id']=9;
		//验证订单
		$info = db('qhrlwx_order')->find($id);

		if(empty($info) || ($user_id != $info['user_id'])){
			$this->err('订单不存在!');
		}
		
		$stat = 1;//1代表已兑换状态
		$re =db('qhrlwx_order')->where('id',$id)->update(['stat'=>$stat]);
		if ($re) {
		 	$this->suc('兑换成功!');
		}else{
			$this->err('兑换失败!');
		} 

	}

	/**
	 * @title 用户订单列表
	 * @type interface
	 * @login 1
	 * @param  user_id 用户id  1  1 
	 * @param page   页数   1  0   
     * @return total_page   总页数  0   1  
     * @return page   当前页  0   1  每页10条
     * @return total   返回的条数  0   1  为0时没有data参数
     * @return data  订单信息   array   0   
     */
	public function listAc(){
        $request = request();
        $size = 10;
        $user_id =	$request->post('user_id/d','');
       	$page    = $request->post('page/s','1');
        $db_order = db('qhrlwx_order');
       
       	$count = $db_order->where('user_id',$user_id)->count();
        if($count == 0){
            $this->suc(['total' => 0]);
        }

        $total_page = ceil($count/$size);
        $page = $page > $total_page ? $total_page : $page;
        $start = (($page - 1) < 0 ? 0 : $page - 1) * $size;
        $list = $db_order->field('id,house_id,integral_id,stat,price,create_time')
        			->where('user_id',$user_id)
                    ->order('create_time desc')
                    ->limit($start,$size)
                    ->select();
      
        $return = [
            'total'      => $count,
            'total_page' => $total_page,
            'page'       => $page,
            'data'       => $list
        ];

        $this->suc($return);
        
    }
}