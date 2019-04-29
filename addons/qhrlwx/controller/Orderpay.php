<?php 
namespace addons\qhrlwx\controller;

/**
 * @title 缴费
 * @type menu
 * @menudisplay false
 *
 */
class Orderpay extends Home{
    /**
     * @title首页
     * @type menu
     * @login 0
     * @menudisplay false
     */
    public function indexAc(){

        return 'Orderpay_index';
    }
}