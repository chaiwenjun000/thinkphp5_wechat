<?php
namespace app\admin\controller;
/**
 * 菜单刷新
 */
class Refresh extends Base{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->getSidebar();
        return $this->success('刷新成功');
    }
}