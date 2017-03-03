<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\WechatConfig as WechatConfigModel;

class WechatConfig extends Base
{
    protected $wechatConfigModel;
    protected $request;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->wechatConfigModel=new WechatConfigModel();
        $this->request=$request::instance();
    }
    /**
     * 微信配置
     * @return [type] [description]
     */
    public function index()
    {
        if($this->request->isPost()){
            $appId=$this->request->param('appId');
            $appSecret=$this->request->param('appSecret');
            $token=$this->request->param('token');
            $encodingAesKey=$this->request->param('encodingAesKey');
            $this->wechatConfigModel->startTrans();
            try {
                $this->wechatConfigModel->saveAll([
                ['id'=>1,'config_name'=>'appId','config_value'=>$appId],
                ['id'=>2,'config_name'=>'appSecret','config_value'=>$appSecret],
                ['id'=>3,'config_name'=>'token','config_value'=>$token],
                ['id'=>4,'config_name'=>'encodingAesKey','config_value'=>$encodingAesKey],
                ]);
                $this->wechatConfigModel->commit(); 
                $this->putFile(); 
                $this->success('配置成功','index');
            } catch (Exception $e) {
                $this->wechatConfigModel->rollback();  
            }
        }
        $configs=$this->wechatConfigModel->select()->toArray();
        $this->assign('configs',$configs);
        return view('index');
    }
    /**
     * 写入配置文件
     * @return [type] [description]
     */
    private function putFile(){
        $configs = $this->wechatConfigModel
                       ->field('config_name,config_value')
                       ->select()
                       ->toArray();
        foreach ($configs as $key => $config) {
            $data[$config['config_name']]=$config['config_value'];
        }
        //配置文件路径
        $path =EXTEND_PATH.'wechat'.DS.'Config.php';
        $str = '<?php return '.var_export($data,true).';';
        file_put_contents($path,$str);
    }
}
