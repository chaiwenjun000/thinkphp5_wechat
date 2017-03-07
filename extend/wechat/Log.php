<?php 
namespace wechat;

class Log{
    const LOG_PATH = '../extend/wechat/log/';
    const LOG_NAME = 'log.log';
    const LOG_FILE = self::LOG_PATH.self::LOG_NAME;
    function __construct() {
        
    }
    public function log($data)
    {
        $data = date('Y-m-d H:i:s').'--------------'.$data."\r\n";
        $this->putFile($data);
    }
    public function putFile($data)
    {
        if (!is_dir(self::LOG_PATH)){
            mkdir(self::LOG_PATH, 0777); //使用最大权限0777创建文件
        } 
        if (!file_exists(self::LOG_FILE)) { //如果不存在则创建

            $fp = fopen(self::LOG_FILE, "a+");

            chmod(self::LOG_FILE, 0777); //修改为0777最大权限

            $a=fwrite($fp, $data); 

            fclose($fp);
        }else{
            $fp = fopen(self::LOG_FILE, "a+");
            $a=fwrite($fp, $data); 
            fclose($fp);
        }
    }
}