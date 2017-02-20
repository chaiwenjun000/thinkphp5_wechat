<?php 
namespace app\admin\model;
use think\Model;
class AuthGroup extends Model{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    /**
     * 获取管理员权限信息
     * @return [type] [description]
     */
    public function getAdminRules()
    {
        $where['id']=1;
        return $this->where($where)->value('rules');
    }
    /**
     * 设置管理员权限信息
     * @param [type] $value [description]
     */
    public function setAdminRules($rules)
    {
        return $this->where('id',1)->update(['rules'=>$rules]);
    }
    /**
     * 获取角色信息
     * @return [type] [description]
     */
    public function getGroupInfo()
    {
        return $this->field('id,rules')->select();
    }
    /**
     * 设置角色信息
     * @return [type] [description]
     */
    public function setGroupInfo($groupId,$rules)
    {
        return $this->where('id', $groupId)->update(['rules'=>$rules]);
    }
}