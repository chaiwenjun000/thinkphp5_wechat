<?php
namespace app\admin\model;
use think\Model;
class Menu extends Model{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    //设置当前模型对应的完整数据表名称
    protected $table = 'wx_menu';
    /**
     * 获取所有菜单数据
     * @return [type] [description]
     */
    public function getMenuData()
    {
        $field='id,title,keyword,url,pid,sort,path,is_show,concat(path,"-",id) as bpath';
        return $this->field($field)->order('bpath')->select();
    }
    /**
     * 获取所有一级菜单
     * @return [type] [description]
     */
    public function getFirstMenu($limit='',$order='sort')
    {
        return $this->where('pid',0)
                    ->where('is_show',1)
                    ->limit($limit)
                    ->order($order)
                    ->select();
    }
    /**
     * 获取子菜单
     * @param  [type] $menuId [description]
     * @return [type]         [description]
     */
    public function getMenuChild($menuId,$limit='',$order='sort',$field='id')
    {
        return $this->where('pid',$menuId)
                    ->field($field)
                    ->limit($limit)
                    ->order($order)
                    ->select();
    }
    /**
     * 获取菜单路径
     * @param  [type] $menuId [description]
     * @return [type]         [description]
     */
    public function getMenuPath($menuId)
    {
        return $this->where('id',$menuId)->value('path');
    }
    /**
     * 删除菜单
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function deleteMenu($ids)
    {
        return $this->where('id','in',$ids)->delete();
    }

}