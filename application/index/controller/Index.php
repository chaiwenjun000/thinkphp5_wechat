<?php
namespace app\index\controller;

use wechat\Wechat;
use wechat\ErrCode;
use app\admin\model\TextReply;
use app\admin\model\ImgTextReply;
use app\admin\model\ReplyKeyword;

class Index
{
    protected $textReplyModel;
    protected $imgTextReplyModel;
    protected $replyKeywordModel;
    public function __construct()
    {
        $this->textReplyModel = new TextReply();
        $this->imgTextReplyModel = new ImgTextReply();
        $this->replyKeywordModel = new ReplyKeyword();
    }
    public function index()
    {
        $weObj = new Wechat();
        $weObj->valid();
        $weObj->getRev();
        $type = $weObj->getRev()->getRevType();
        $event = $this->weObj->getRev()->getRevEvent();
        //关键字回复
        switch ($type) {
            case Wechat::MSGTYPE_TEXT:
                $keyword = $weObj->getRev()->getRevContent();
                $this->reply($keyword);
                break;
        }
    }
    /**
     * 匹配关键字
     * 首先完整匹配
     * 如果未匹配进行包含匹配
     * 如果未匹配回复默认回复
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    private function reply($keyword)
    {
        $data = $this->replyKeywordModel->completeMatch($keyword);
        if(empty($data)){
            $data = $this->replyKeywordModel->unCompleteMatch($keyword);
        }
        if(empty($data)){
            
        }else{
            
        }
    }
}
