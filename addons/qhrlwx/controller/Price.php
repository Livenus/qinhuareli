<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 定价
 * @type menu
 * @menudisplay false
 *
 */
class Price extends Home{
    /**
     * @title首页
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function indexAc(){

        return 'Price_index';
    }
}