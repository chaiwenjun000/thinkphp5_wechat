<?php
namespace app\admin\controller;
use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthGroup as AuthGroupModel;

class AuthRule extends Base{

    protected $authRuleModel;
    protected $authGroupModel;

    public function __construct(){
        parent::__construct();
        $this->authRuleModel=new AuthRuleModel();
        $this->authGroupModel=new AuthGroupModel();
    }
    /**
     * 权限列表
     * @return [type] [description]
     */
    public function index(){
        $where['status']=1;
        $authRuleData = $this->authRuleModel->getRuleData($where);

        foreach ($authRuleData as $k => $v) {
            $authRuleData[$k]['count'] = count(explode('-', $v['path']));
        }

        $this->assign('data', $authRuleData);
        return view('index');
    }
    /**
     * 添加权限
     * @author ning
     * @DateTime 2016-06-22T22:26:12+0800
     */
    public function add(){
        if(request()->isPost()){
            $data=input('post.');
            if(!empty($data['is_show'])){
                $data['is_show']=1;
            }
            if($this->authRuleModel->validate(true)->save($data)){
                // 给管理员添加全部权限
                $adminRules = $this->authGroupModel->getAdminRules();
                $rules = $adminRules . ',' . $this->authRuleModel->id;
                $this->authGroupModel->setAdminRules($rules);
                //刷新菜单栏
                $this->getSidebar();
                session('_auth_list_'.session('user_auth')['uid'].'1', null);
                return $this->success('添加成功','index');
            }else{
                return $this->error($this->$authRuleModel->getError());
            }
        }else{
            $where['status'] = 1;
            $where['type']   = 1;
            $where['is_show'] = 1;
            $pidData = $this->authRuleModel->getRuleData($where);

            foreach ($pidData as $key => $value) {
                $pidData[$key]['count'] = count(explode('-', $value['path']));
            }
            $this->assign('pidData', $pidData);
            return view('add');
        }
    }

    /**
     * 编辑权限
     * @author ning
     * @DateTime 2016-06-23T21:33:15+0800
     * @return   [type]                   [description]
     */
    public function edit(){
        if(request()->isPost()){
            $id = input('?post.id') ? input('post.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            if(in_array($id, explode(',','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17'))){
                return $this->error('该权限不允许编辑');
            }
            $data=input('post.');

            if(!empty($data['is_show'])){
                $data['is_show']=1;
            }
            if($this->authRuleModel->validate(true)->save($data, ['id'=>$id])){
                $this->getSidebar();
                session('_auth_list_'.session('user_auth')['uid'].'1', null);
                return $this->success('修改成功','index');
            }else{
                return $this->error($this->authRuleModel->getError());
            }           
        }else{
            $id = input('?param.id') ? input('param.id') : '';
            if(!$id){
                return $this->error('参数错误');
            }
            $data = $this->authRuleModel->getRuleInfo($id);
            $where['status'] = 1;
            $where['type']   = 1;
            $where['is_show'] = 1;
            $pidData = $this->authRuleModel->getRuleData($where);
            foreach ($pidData as $key => $value) {
                $pidData[$key]['count'] = count(explode('-', $value['path']));
            }
            $this->assign('pidData', $pidData);         
            $this->assign('data',$data);
            return view('edit');
        }
    }

    /**
     * 删除权限
     * @author ning
     * @DateTime 2016-06-26T11:12:01+0800
     * @return   [type]                   [description]
     */
    public function del(){
        $id = input('?param.id') ? intval(input('param.id')) : '';
        if(!$id){
            return $this->error('参数错误');
        }
        if(in_array($id, explode(',', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17'))){
            return $this->error('该权限不允许编辑');
        }
        $ids = [$id];

        $child1 = $this->authRuleModel->getRuleChild($id);
        if($child1){
            foreach ($child1 as $key => $value) {
                $ids[] = $value['id'];
                $child2 = $this->authRuleModel->getRuleChild($value['id']);
                if($child2){
                    foreach ($child2 as $k2 => $v2) {
                        $ids[] = $v2['id'];
                    }
                }
            }
        }
        
        $this->authRuleModel->deleteRule($ids);

        $rules = $this->authGroupModel->getGroupInfo();
        foreach ($rules as $rule) {
            $rulesArr = explode(',', $rule['rules']);
            $rules = implode(',', array_diff($rulesArr, $ids));
            $this->authGroupModel->setGroupInfo($rule['id'],$rules);
        }
        $this->getSidebar();
        session('_auth_list_'.session('user_auth')['uid'].'1', null);
        return $this->success('删除成功','auth_rule/index');
    }
}