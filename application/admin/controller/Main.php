<?php
namespace app\admin\controller;

use wechat\Wechat;

class Main extends Base{
    protected $wechat;
    public function __construct(){
        parent::__construct();
        $this->wechat = new Wechat();
    }
    public function index(){
        return view('index');
    }
}