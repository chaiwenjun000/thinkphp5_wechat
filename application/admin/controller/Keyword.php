<?php
namespace app\admin\controller;
use app\admin\model\ReplyKeyword as ReplyKeywordModel;

class Keyword extends Base{
    protected $replyKeywordModel;
    public function __construct(){
        parent::__construct();
        $this->replyKeywordModel = new ReplyKeywordModel();
    }
    public function index(){
        $list = $this->replyKeywordModel->paginate(10);
        $this->assign('list',$list);
        return view('index');
    }
}