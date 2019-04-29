<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 文章查询
 * @type menu
 * @menudisplay false
 *
 */
class Article extends Home{
    
    /**
     * @title 文章列表
     * @type interface
     * @login 1
     * @param page   页数   1  0   
     * @param keyword  文章分类关键字 about  1  关键字在后台可设置
     * @return total_page   总页数  0   1  
     * @return page   当前页  0   1  每页10条
     * @return total   返回的条数  0   1  为0时没有data参数
     * @return data   收货地址   array   0   
     */
    public function listAc(){
        $request = request();
        $size = 10;

        $keyword = $request->post('keyword/s',''); 
        $page    = $request->post('page/s','1');
        $db_article = db('qhrlwx_article');

        if(empty($keyword)){
            $this->err('参数错误！');
        }

        $where['a.keyword'] = $keyword;
        $count = $db_article->alias('j')->join('qhrlwx_category a','j.cate_id = a.cate_id','LEFT')->where($where)->count();
        if($count == 0){
            $this->suc(['total' => 0]);
        }

        $total_page = ceil($count/$size);
        $page = $page > $total_page ? $total_page : $page;
        $start = (($page - 1) < 0 ? 0 : $page - 1) * $size;
        $list = $db_article->alias('j')
                    ->field('j.id,j.title,j.introduction,j.pic,j.create_time')
                    ->join('qhrlwx_category a','j.cate_id = a.cate_id','LEFT')
                    ->where($where)
                    ->order('j.sort desc')
                    ->limit($start,$size)
                    ->select();
        foreach ($list as &$value) {
            $value['pic'] = explode(',',$value['pic']);
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
     * @title 文章详情
     * @type interface
     * @login 1
     * @param id  文章id   1  1   
     */
    public function detailAc(){
        $request = request();
        $id = $request->post('id/s',''); 

        if(empty($id)){
            $this->err('参数错误！');
        }

        $info = db('qhrlwx_article')->find($id);
        $info['pic'] = explode(',',$info['pic']);
        $this->suc($info);
        
    }

}