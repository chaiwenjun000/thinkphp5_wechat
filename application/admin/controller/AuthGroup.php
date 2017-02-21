<?php
namespace app\admin\Controller;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule as AuthRuleModel;
class AuthGroup extends Base{
    protected $authGroupModel;
    protected $authRuleModel;
    public function __construct()
    {
        parent::__construct();
        $this->authGroupModel=new AuthGroupModel();
        $this->authRuleModel=new AuthRuleModel();
    }
    /**
     * 角色列表
     * @return [type] [description]
     */
    public function index(){
        $roles = $this->authGroupModel->getGroupPage(10);
        $this->assign('roles', $roles);
        return view('index');
    }
    /**
     * 添加角色
     */
    public function add(){
        if(request()->isPost()){
            if($this->authGroupModel->validate(true)->save(input('post.'))){
                return $this->success('添加成功','auth_group/index');
            }else{
                return $this->error($this->authGroupModel->getError());
            }
        }else{
            return view('add');
        }
    }

    /**
     * 编辑角色
     * @author ning
     * @DateTime 2016-06-22T23:15:58+0800
     * @return   [type]                   [description]
     */
    public function edit(){
        if(request()->isPost()){
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id || $id==1){
                return $this->error('参数错误');
            }
            if($this->authGroupModel->validate(true)->save(input('post.'),['id'=>input('post.id')])){
                return $this->success('修改成功','index');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            $data = $this->authGroupModel->getGroupById($id);
            $this->assign('data',$data);
            return view('edit');
        }
    }

    /**
     * 删除角色
     * @author ning
     * @DateTime 2016-06-23T10:23:24+0800
     * @return   [type]                   [description]
     */
    public function del(){
        $id = input('?param.id') ? input('param.id') : '';
        if(!$id || $id == 1){
            return $this->error('参数错误');
        }
        if($this->authGroupModel->where('id',$id)->delete()){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }

    /**
     * 资源管理
     * @author ning
     * @DateTime 2016-06-23T21:34:00+0800
     * @return   [type]                   [description]
     */
    public function resource(){
        if(request()->isPost()){
            $id = input('?post.id') ? input('post.id') : '';
            $resource=input('post.resource/a');
            sort($resource);
            $rules=implode(',',$resource);
            if(!$id || $id == 1){
                return $this->error('参数错误');
            }
            if($this->authGroupModel->isUpdate(true)->save(['rules'=>$rules],['id'=>$id])){
                $this->getSidebar();
                session('_auth_list_'.session('user_auth')['uid'].'1', null);
                return $this->success('修改成功');
            }else{
                return $this->error($this->authGroupModel->getError());
            }
        }else{
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            $data = $this->authGroupModel->getGroupById($id);
            $where['type']=1;
            $order='path,sort asc';
            $authRuleData = \think\Db::name('auth_rule')->field('id,name,title,pid,path')->where('type',1)->order('path,sort asc')->select();
            $resource = [];
            foreach ($authRuleData as $key => $value) {
                $path = explode('-', $value['path']);
                switch(count($path)){
                    case 1:
                        $resource[$value['id']] = $value;
                        break;
                    case 2:
                        $resource[$path[1]]['child'][$value['id']] = $value;
                        break;
                    case 3:
                        $resource[$path[1]]['child'][$path[2]]['child'][$value['id']] = $value;
                        break;
                }
            }   

            $this->assign('resource', $resource);
            $this->assign('data', $data);
            return view('resource');
        }
    }


}