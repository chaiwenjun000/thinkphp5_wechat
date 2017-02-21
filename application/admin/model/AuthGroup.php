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
     * 获取角色信息用于权限操作
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
    /**
     * 获取角色信息用于添加用户下拉框
     * @return [type] [description]
     */
    public function getGroupData()
    {
        return $this->field('id,title')->where('id','neq',1)->where('status',1)->select();
    }
    /**
     * 获取角色信息分页
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    public function getGroupPage($page)
    {
        return $this->where('status',1)->paginate($page);
    }
    /**
     * 获取角色信息by ID
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getGroupById($id)
    {
        return $this->where('id',$id)->find();
    }
}