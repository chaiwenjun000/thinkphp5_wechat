<?php
namespace app\index\controller;

use wechat\Wechat;
use wechat\ErrCode;
use wechat\Log;
use app\admin\model\SubReply;
use app\admin\model\DefReply;
use app\admin\model\TextReply;
use app\admin\model\ImgTextReply;
use app\admin\model\ReplyKeyword;

class Index
{
    protected $wechat;
    protected $subReplyModel;
    protected $defReplyModel;
    protected $textReplyModel;
    protected $imgTextReplyModel;
    protected $replyKeywordModel;
    
    public function __construct()
    {
        $this->wechat = new Wechat();
        $this->subReplyModel = new SubReply();
        $this->defReplyModel = new DefReply();
        $this->textReplyModel = new TextReply();
        $this->imgTextReplyModel = new ImgTextReply();
        $this->replyKeywordModel = new ReplyKeyword();
    }
    public function index()
    {
        $this->wechat->valid();
        $this->wechat->getRev();
        $type = $this->wechat->getRev()->getRevType();
        $event = $this->wechat->getRev()->getRevEvent();
        //关键字回复
        switch ($type) {
            case Wechat::MSGTYPE_TEXT:
                $keyword = $this->wechat->getRev()->getRevContent();
                $this->reply($keyword);
                break;
        }
        //事件回复
        switch ($event['event']) {
            // 关注
            case Wechat::EVENT_SUBSCRIBE:
                $subReply = cache('subReply');
                if(!$subReply){
                    $subReply = $this->subReplyModel->getLast();
                    cache('subReply', $subReply, 24*3600);
                }
                if($subReply){
                    $this->reply($subReply);
                }else{
                    $this->defauleReply();
                }
                exit;
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
        //获取完全匹配
        $data = $this->replyKeywordModel->completeMatch($keyword);

        if(empty($data)){
            //获取包含匹配
            $data = $this->replyKeywordModel->unCompleteMatch($keyword);
        }

        if(empty($data)){//未匹配
            $this->defauleReply();
        }else{
            switch ($data->getData('module')) {
                case Wechat::MSGTYPE_IMAGE:
                    $info = $this->imgTextReplyModel->find($data['pid']);
                    $content[] = [
                        'Title'=>$info['title'],
                        'Description'=>$info['text'],
                        'PicUrl'=>$this->getImgUrl($info['pic']),
                        'Url'=>$this->getUrl($info['url'])
                    ];
                    $this->wechat->news($content)->reply();
                    exit;
                    break;
                case Wechat::MSGTYPE_TEXT:
                    $info = $this->textReplyModel->getText($data['pid']);
                    $this->wechat->text($info)->reply();
                    exit;
                    break;
            }
        }
    }
    /**
     * 默认回复
     * @return [type] [description]
     */
    private function defauleReply()
    {
        $defReply = cache('defReply');
        if(!$defReply){
            $defReply = $this->defReplyModel->getLast();
            cache('defReply', $defReply, 24*3600);
        }
        if($defReply){
            $this->reply($defReply);
        }
    }
    /**
     * [getUrl description]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    private function getUrl($url)
    {
        return $url;
    }
    /**
     * 获取图片地址
     * @param  [type] $pic [description]
     * @return [type]      [description]
     */
    private function getImgUrl($pic){
        //如果是外部的网址
        if(strpos($pic, 'http') === false){
            return 'http://zan.ittun.com'.$pic;            
        }else{
            return $pic;
        }
    }
}
