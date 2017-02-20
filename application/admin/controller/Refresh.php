<?php
namespace app\admin\controller;

class Refresh extends Base{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $this->getSidebar();
        return $this->success('刷新成功');
    }
}