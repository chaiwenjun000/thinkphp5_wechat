<?php
namespace app\admin\model;
use think\Model;
class AuthRule extends Model{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    /**
     * 获取权限数据
     * @param  [type] $where [description]
     * @param  string $order [description]
     * @return [type]        [description]
     */
    public function getRuleData($where,$order='bpath')
    {
        $field='id,name,title,pid,sort,path,type,icon,concat(path,"-",id) as bpath';
        return $this->field($field)
                    ->where($where)
                    ->order($order)
                    ->select();
    }
    /**
     * 获取指定权限信息
     * @return [type] [description]
     */
    public function getRuleInfo($ruleId)
    {
        $field='id,name,title,type,condition,pid,sort,is_show,icon';
        return $this->field($field)->where('id',$ruleId)->find();
    }
    /**
     * 获取规则路径
     * @param  [type] $ruleId [description]
     * @return [type]         [description]
     */
    public function getRulePath($ruleId)
    {
        return $this->where('id',$ruleId)->value('path');
    }
    /**
     * 获取指定权限子权限
     * @param  [type] $ruleId [description]
     * @return [type]         [description]
     */
    public function getRuleChild($ruleId)
    {
        return $this->field('id')->where('pid',$ruleId)->select();
    }
    /**
     * 删除权限
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function deleteRule($ids)
    {
        return $this->where('id','in',$ids)->delete();
    }
}