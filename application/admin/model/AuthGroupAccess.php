<?php
namespace app\admin\model;
use think\Model;
class AuthGroupAccess extends Model{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    /**
     * 获取角色ID
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function getGroupId($userId)
    {
        return $this->where('uid',$userId)->value('group_id');
    }
}