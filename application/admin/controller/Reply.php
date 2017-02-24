<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\SubReply as SubReplyModel;
use app\admin\model\TextReply as TextReplyModel;
use app\admin\model\ImgTextReply as ImgTextReplyModel;
use app\admin\model\ReplyKeyword as ReplyKeywordModel;

class Reply extends Base
{
    protected $request;
    protected $subReplyModel;
    protected $textReplyModel;
    protected $imgTextReplyModel;
    protected $replyKeywordModel;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request=$request;
        $this->subReplyModel=new SubReplyModel();
        $this->textReplyModel=new TextReplyModel();
        $this->imgTextReplyModel=new ImgTextReplyModel();
        $this->replyKeywordModel = new ReplyKeywordModel();
    }
    /**
     * 关注回复
     * @return [type] [description]
     */
    public function subscribe()
    {
        if($this->request->isPost()){
            $this->subReplyModel->save($this->request->param());
            $this->success('操作成功','subscribe');
        }else{
            $data= $this->subReplyModel->getLast();
            $this->assign('data',$data);
            return view('subscribe');
        }
    }
    /**
     * 文本回复
     * @return [type] [description]
     */
    public function text()
    {   
        $list=$this->textReplyModel->paginate(10);
        $this->assign('list',$list);
        return view('text');
    }
    /**
     * 文本回复添加
     * @return [type] [description]
     */
    public function textAdd()
    {
        if($this->request->isPost()){
            $this->textReplyModel->save($this->request->param());
            $data=array(
                'pid'=>$this->textReplyModel->id,
                'keyword'=>$this->request->param('keyword'),
                'type'=>$this->request->param('type'),
                'module'=>'text',
                );
            $this->replyKeywordModel->save($data);
            $this->success('操作成功','reply/text');
        }else{
            return view('textadd');
        }
    }
    /**
     * 文本回复编辑
     * @return [type] [description]
     */
    public function textEdit()
    {
        if($this->request->isPost()){
            $id=$this->request->param('id');
            $this->textReplyModel->save($this->request->param(),['id'=>$id]);
            $data=array(
                'keyword'=>$this->request->param('keyword'),
                'type'=>$this->request->param('type'),
                'module'=>'text',
                );
            $this->replyKeywordModel->save($data,['pid'=>$id]);
            $this->success('操作成功','reply/text');
        }else{
            $id=$this->request->param('id');
            $data=$this->textReplyModel->get($id);
            $this->assign('data',$data);
            return view('textedit');
        }
    }
    /**
     * 文本回复删除
     * @return [type] [description]
     */
    public function textDel()
    {
        $id = input('?param.id') ? input('param.id') : '';
        if(!$id){
            return $this->error('参数错误');
        }
        if($this->textReplyModel->destroy($id)){
            $this->replyKeywordModel->softDel($id);
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
    /**
     * 图文回复
     * @return [type] [description]
     */
    public function imgText()
    {
        $list=$this->imgTextReplyModel->paginate(10);
        $this->assign('list',$list);
        return view('imgtext');
    }
    /**
     * 图文回复添加
     * @return [type] [description]
     */
    public function imgTextAdd()
    {
        if($this->request->isPost()){
            $data=$this->request->param();
            if(isset($data['showpic'])){
                $data['showpic']=1;
            }else{
                $data['showpic']=0;
            }
            $this->imgTextReplyModel->save($data);
            $data=array(
                'pid'=>$this->imgTextReplyModel->id,
                'keyword'=>$this->request->param('keyword'),
                'type'=>$this->request->param('type'),
                'module'=>'img',
                );
            $this->replyKeywordModel->save($data);
            $this->success('操作成功','reply/imgtext');
        }else{
            return view('imgtextadd');
        }
    }
    /**
     * 图文回复编辑
     * @return [type] [description]
     */
    public function imgTextEdit()
    {
        if($this->request->isPost()){
            $id=$this->request->param('id');
            $data=$this->request->param();
            if(isset($data['showpic'])){
                $data['showpic']=1;
            }else{
                $data['showpic']=0;
            }
            $this->imgTextReplyModel->save($data,['id'=>$id]);
            $data=array(
                'keyword'=>$this->request->param('keyword'),
                'type'=>$this->request->param('type'),
                'module'=>'text',
                );
            $this->replyKeywordModel->save($data,['pid'=>$id]);
            $this->success('操作成功','reply/imgtext');
        }else{
            $id=$this->request->param('id');
            $data=$this->imgTextReplyModel->get($id);
            $this->assign('data',$data);
            return view('imgtextedit');
        }
    }
    /**
     * 图文回复删除
     * @return [type] [description]
     */
    public function imgTextDel()
    {
        $id = input('?param.id') ? input('param.id') : '';
        if(!$id){
            return $this->error('参数错误');
        }
        if($this->imgTextReplyModel->destroy($id)){
            $this->replyKeywordModel->softDel($id);
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
}
