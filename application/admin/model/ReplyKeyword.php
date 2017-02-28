<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class ReplyKeyword extends Base
{
    use SoftDelete;
    private $_type=array(
            1=>'完整匹配',
            2=>'包含匹配',
        );
    private $_module=array(
            'image'=>'图文',
            'text'=>'文本',
        );
    /**
     * 获取器
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function getTypeAttr($value){
        return  $this->_type[$value];
    }
    /**
     * 获取器
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function getModuleAttr($value){
        return  $this->_module[$value];
    }
    /**
     * 软删除
     * @param  [type] $pid [description]
     * @return [type]      [description]
     */
    public function softDel($pid)
    {
        $id=$this->where('pid',$pid)->value('id');
        $this->destroy($id);
    }
    /**
     * 完整匹配
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    public function completeMatch($keyword)
    {
        return $this->where('keyword','like',$keyword)
                    ->where('type',1)
                    ->field('pid,module')
                    ->find();
    }
    /**
     * 包含匹配
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    public function unCompleteMatch($keyword)
    {
        return $this->where('keyword','like','%'.$keyword.'%')
                    ->where('type',2)
                    ->field('pid,module')
                    ->find();
    }
    public function getAllKeyword()
    {
        return $this->where('type',1)->field('keyword')->select();
    }
}
