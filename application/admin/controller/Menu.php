<?php
namespace app\admin\controller;
use app\admin\model\Menu as MenuModel;
use think\Db;
use wechat\Wechat;
/**
 * 菜单管理
 */
class Menu extends Base{
    protected $menuModel;
    public function __construct(){
        parent::__construct();
        $this->menuModel=new MenuModel;
    }
    /**
     * 菜单列表
     * @return [type] [description]
     */
    public function index(){
        $list = $this->menuModel->getMenuData();
        foreach($list as $key=>$value){
            $list[$key]['count'] = count(explode('-',$value['path']));
        }
        $this->assign('list',$list);
        return view('index');
        return view('index');
    }
    /**
     * 添加菜单
     */
    public function add(){
        if(request()->isPost()){
            $data=input('post.');
            if(!empty($data['is_show'])){
                $data['is_show']=1;
            }else{
                $data['is_show']=0;
            }
            if(empty($data['pid'])){
                $data['path']=0;
            }else{
                $path=$this->authRuleModel->getRulePath($data['pid']);
                $data['path']=$path.'-'.$data['pid'];
            }
            if($this->menuModel->validate(true)->save($data)){
                return $this->success('添加成功','menu/index');
            }else{
                return $this->error($this->menuModel->getError());
            }
        }else{
            $this->getFirst();
            return view('add');
        }
    }
    /**
     * 编辑菜单
     * @return [type] [description]
     */
    public function edit()
    {
        if(request()->isPost()){
            $id = input('?post.id') ? input('post.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            $data=input('post.');
            if(!empty($data['is_show'])){
                $data['is_show']=1;
            }else{
                $data['is_show']=0;
            }
            if(empty($data['pid'])){
                $data['path']=0;
            }else{
                $path=$this->menuModel->getMenuPath($data['pid']);
                $data['path']=$path.'-'.$data['pid'];
            }
            if($this->menuModel->validate(true)->save($data,['id'=>$id])){
                return $this->success('修改成功','menu/index');
            }else{
                return $this->error($this->menuModel->getError());
            }
        }else{
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            $data = Db::name('menu')->where('id',$id)->find();
            $this->getFirst();
            $this->assign('data', $data);
            return view('edit');
        }
    }
    /**
     * 删除菜单
     * @return [type] [description]
     */
    public function del()
    {
        $id = input('?param.id') ? input('param.id') : '';
        if(!$id){
            return $this->error('参数错误');
        }
        $ids = [$id];
        $child=$this->menuModel->getMenuChild($id);
        if($child){
            foreach ($child as $key => $value) {
                $ids[] = $value['id'];
            }
        }
        if($this->menuModel->deleteMenu($ids)){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
    /**
     * 获取一级菜单
     * @return [type] [description]
     */
    private function getFirst(){
        $data = $this->menuModel->getFirstMenu();
        array_unshift($data, ['id'=>0,'title'=>'一级菜单']);
        $this->assign('pids', $data);
    }
    /**
     * 生成菜单
     * @return [type] [description]
     */
    public function create()
    {
        $weObj = new Wechat();
        $parentMenus=$this->menuModel->getFirstMenu(3);
        $menu['button'] = [];
        foreach ($parentMenus as $key => $parentMenu) {
            $childMenus=$this->menuModel->getMenuChild(5,'sort','title,url,keyword');
            if ($childMenus) {
                $menu['button'][$key]=[
                    'name'=>$parentMenu['title'],
                    'sub_button'=>[],
                ];
                foreach ($childMenus as $k => $childMenu) {
                    if ($childMenu) {
                        $menu['button'][$key]['sub_button'][$k]=[
                            'type'=>'view',
                            'name'=>$childMenu['title'],
                            'url'=>$childMenu['url'],
                        ];
                    } else {
                        $menu['button'][$key]['sub_button'][$k] = [
                            'type'=>'click',
                            'name'=>$childMenu['title'],
                            'key'=>$childMenu['keyword']
                        ];
                    }
                    
                }
            } else {
                if($parentMenu['url']){
                    $menu['button'][$key] = [
                        'type'=>'view',
                        'name'=>$parentMenu['title'],
                        'url'=>$parentMenu['url']
                    ];
                }else{
                    $menu['button'][$key] = [
                        'type'=>'click',
                        'name'=>$parentMenu['title'],
                        'key'=>$parentMenu['keyword']
                    ];
                }
            }
        }
        $result = $weObj->createMenu($menu);
        if($result){
            return $this->success('菜单生成成功');
        }else{
            return $this->error($weObj->getErrorText());
        }
    }
}