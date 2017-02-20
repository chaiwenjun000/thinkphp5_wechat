<?php 
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\repository\UserRepository;
use app\admin\model\User;
use app\admin\model\AuthGroupAccess;


class Index extends Controller{

    protected $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
        $this->authGroupAccess = new AuthGroupAccess();
    }
    /**
     * 登录
     * @return [type] [description]
     */
    public function index()
    {
        $request=Request::instance();
        // 检测登录状态
        if(session('user_auth')){
            $this->redirect('main/index');
        }
        if($request->isPost()){
            $userName = $request->param('username');
            $password = $request->param('password');
            $type = $request->param('type')?$request->param('type'):1;
            if(!$userName || !$password){
                return $this->error('请填写用户名或密码');
            }
            $userId=$this->userRepository->login($userName,$password,$type);
            if($userId>0){
                $groupId = $this->authGroupAccess->getGroupId($userId);
                $auth = [
                    'uid'=>$userId,
                    'group_id'=>$groupId,
                    'username'=>$userName,
                    'last_login_time'=>time(),
                ];
                session('user_auth',$auth);
                return $this->success('登录成功','main/index');
            }else{
                switch ($userId) {
                    case '-1':
                        $error = '用户不存在或被禁用';
                        break;
                    case '-2':
                        $error = '密码错误';
                        break;
                    
                    default:
                        $error = '未知错误';
                        break;
                }
                return $this->error($error);
            }
        }else{
            return view('index');
        }
    }
}