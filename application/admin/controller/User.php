<?php
namespace app\admin\controller;
use app\admin\model\User as UserModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\repository\UserRepository;
/**
 * 用户管理
 */
class User extends Base{
    protected $userModel;
    protected $authGroupModel;
    protected $userRepository;
    protected $authGroupAccessModel;
    public function __construct()
    {
        parent::__construct();
        $this->userModel=new UserModel();
        $this->authGroupModel=new AuthGroupModel();
        $this->userRepository =new UserRepository();
        $this->authGroupAccessModel = new authGroupAccessModel();
    }
    /**
     * 退出登录状态
     * @return   
     */
    public function logout(){
        session(null);
        return $this->success('退出成功','index/index');
    }
    /**
     * 后台用户列表
     * @return   
     */
    public function index(){
        $list = $this->userModel->getUserList();
        $this->assign('list', $list);
        return view('index');
    }
    /**
     * 添加用户
     * @author ning
     * @DateTime 2016-06-24T22:54:28+0800
     */
    public function add(){
        if(request()->isPost()){
            $userName = input('?post.username') ? input('post.username') : '';
            if(!$userName){
                return $this->error('请填写用户名');
            }
            $groupId = input('?post.group_id') ? input('post.group_id') : '';
            if(!$groupId){
                return $this->error('请创建角色','auth_group/add');
            }
            $res = $this->userRepository->register($userName, '123456');
            if($res>0){
                $insertData = [
                    'uid'=>$res,
                    'group_id'=>$groupId
                ];  
                $this->authGroupAccessModel->insert($insertData);
                return $this->success('添加成功','index');
            }else{
                return $this->error($res);
            }

        }else{
            $authGroupData = $this->authGroupModel->getGroupData();
            $this->assign('authGroupData',$authGroupData);
            return view('add');
        }
    }
    /**
     * 编辑用户
     * @return   [type]                   [description]
     */
    public function edit(){
        if(request()->isPost()){
            $id = input('?post.id') ? input('post.id') : '';
            if(!$id || $id==1){
                return $this->error('参数错误');
            }
            $groupId = input('?post.group_id') ? input('post.group_id') : '';
            if(!$groupId){
                return $this->error('请创建角色','auth_group/add');
            }
            $data = ['user_name'=>input('post.username')];
            $res = $this->userRepository->updateInfoNotCheck($id, $data);
            if($res['status']){
                $this->authGroupAccessModel->where('uid',$id)
                                           ->update(['group_id'=>$groupId]);
                return $this->success('修改成功','index');
            }else{
                return $this->error($res['info']);
            }
        }else{
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id || $id == 1){
                return $this->error('参数错误');
            }
            $data = $this->userModel->where('user_id',$id)->find();
            $groupId = $this->authGroupAccessModel ->getGroupId($id);
            $data['group_id'] = $groupId;
            $authGroupData = $this->authGroupModel->getGroupData();
            $this->assign('authGroupData',$authGroupData);
            $this->assign('data',$data);
            return view('edit');
        }
    }

    /**
     * 删除用户
     * @return   [type]                   [description]
     */
    public function del(){
        $id = input('?param.id') ? input('param.id') : '';
        if(!$id || $id == 1){
            return $this->error('参数错误');
        }
        if($this->userModel->where('id',$id)->delete()){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
}   