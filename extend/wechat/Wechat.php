<?php 
namespace wechat;
use wechat\ErrCode;
use wechat\Prpcrypt;
use wechat\Log;
class Wechat{

    private $appId ;//微信开发者申请的appID
    private $appSecret ;//微信开发者申请的appSecret
    private $token ;//Token验证
    private $encodingAesKey;
    private $encryptType;
    private $accessToken;//获取到的access_token
    private $jsapiTicket;
    private $userToken;
    private $postXml;

    private $_msg;//发送的消息
    private $_receive;//接收的消息
    private $_textFilter = true;//是否过滤文本

    public $errCode = 0;
    public $errMsg  = "success";

    const CACHE_PATH = '../extend/wechat/cache/';
    const CACHE_NAME = 'cache.json';
    const CACHE_FILE = self::CACHE_PATH.self::CACHE_NAME;

    const MSGTYPE_TEXT     = 'text';             //  文本
    const MSGTYPE_IMAGE    = 'image';            //  图片
    const MSGTYPE_LOCATION = 'location';         //  位置
    const MSGTYPE_LINK     = 'link';             //  链接
    const MSGTYPE_EVENT    = 'event';            //  事件
    const MSGTYPE_MUSIC    = 'music';            //  音乐
    const MSGTYPE_NEWS     = 'news';             //  图文
    const MSGTYPE_VOICE    = 'voice';            //  语音
    const MSGTYPE_VIDEO    = 'video';            //  视频  
    const MSGTYPE_SHORT_VIDEO = 'shortvideo';    //  小视频 

    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';//用户同意授权获取code
    const OAUTH_AUTHORIZE_URL = '/authorize?';

    const EVENT_SUBSCRIBE   = 'subscribe';       //  关注
    const EVENT_UNSUBSCRIBE = 'unsubscribe';     //  取消关注
    const EVENT_SCAN        = 'SCAN';            //  扫描

    const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com';//API根路径
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';//API根路径
    const AUTH_URL       = '/token?grant_type=client_credential&';//获取accessToken的URL
    const GET_TICKET_URL = '/ticket/getticket?';//获取JSAPI授权TICKET的URL

    const MENU_CREATE_URL = '/menu/create?';//创建菜单的URL
    const MENU_ADD_CONDITIONAL_URL = '/menu/addconditional?';//创建个性化菜单的URL
    const MENU_GET_URL    = '/menu/get?';//获取菜单的URL
    const MENU_DELETE_URL = '/menu/delete?';//删除菜单的URL

    const TEMPLATE_SET_INDUSTRY_URL = '/message/template/api_set_industry?';//模版消息设置行业URL
    const TEMPLATE_GET_INDUSTRY_URL = '/message/template/get_industry?';//获取模版消息行业URL
    const TEMPLATE_ADD_TPL_URL  = '/message/template/api_add_template?';//添加模版消息URL
    const TEMPLATE_GET_LIST_URL = '/template/get_all_private_template?';//获取模版消息列表URL
    const TEMPLATE_DEL_URL  = '/template/del_private_template?';//删除模版消息列表URL
    const TEMPLATE_SEND_URL = '/message/template/send?';//发送模版消息设置行业URL

    const MEDIA_UPLOAD_URL = '/media/upload?';//素材上传URL
    const MEDIA_GET_URL    = '/media/get?';//素材获取URL
    const MEDIA_FOREVER_UPLOAD_URL = '/material/add_material?';//永久素材上传URL
    const MEDIA_FOREVER_NEWS_UPLOAD_URL = '/material/add_news?';//永久图文素材上传URL
    const MEDIA_FOREVER_NEWS_UPDATE_URL = '/material/update_news?';//永久素材更新URL
    const MEDIA_FOREVER_GET_URL = '/material/get_material?';//获取永久素材URL
    const MEDIA_FOREVER_DEL_URL = '/material/del_material?';//删除永久素材URL
    const MEDIA_FOREVER_COUNT_URL = '/material/get_materialcount?';//获取永久素材总数URL
    const MEDIA_FOREVER_BATCHGET_URL = '/material/batchget_material?';//获取永久素材列表URL

    const MEDIA_UPLOADIMG_URL = '/media/uploadimg?';//群发上传图片URL
    const MEDIA_UPLOADNEWS_URL = '/media/uploadnews?';//群发上传图文URL
    const MEDIA_VIDEO_UPLOAD = '/media/uploadvideo?';//群发上传视频URL
    const MASS_SEND_GROUP_URL = '/message/mass/sendall?';//根据分组进行群发URL
    const MASS_SEND_URL = '/message/mass/send?';//根据openId进行群发URL
    const MASS_DELETE_URL = '/message/mass/delete?';//删除群发URL
    const MASS_PREVIEW_URL = '/message/mass/preview?';//预览群发URL
    const MASS_QUERY_URL = '/message/mass/get?';//群发状态查询URL

    const GROUP_CREATE_URL='/groups/create?';//创建分组URL
    const GROUP_GET_URL='/groups/get?';//查询分组URL
    const USER_GROUP_URL='/groups/getid?';//查询用户分组URL
    const GROUP_UPDATE_URL='/groups/update?';//更新分组URL
    const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';//移动用户分组URL
    const GROUP_MEMBER_BATCHUPDATE_URL='/groups/members/batchupdate?';//批量移动用户分组URL
    const GROUP_DEL_URL='/groups/delete?';//删除分组URL
    const USER_UPDATEREMARK_URL='/user/info/updateremark?';//设置用户备注名URL
    const USER_INFO_URL='/user/info?';//获取用户基本信息（包括UnionID机制）URL
    const USER_INFO_BARCHGET_URL='/user/info/batchget?';//批量获取用户基本信息URL
    const USER_GET_URL='/user/get?';//获取用户列表URL

    const QRCODE_CREATE_URL='/qrcode/create?';//创建二维码URL
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';//通过ticket换取二维码URL
    const SHORT_URL='/shorturl?';//长链接转短链接URL

    //数据分析接口
    static $DATACUBE_URL_ARR = array(//用户分析
            'user' => array(
                    'summary' => '/datacube/getusersummary?',//获取用户增减数据
                    'cumulate' => '/datacube/getusercumulate?',//获取累计用户数据
            ),
            'article' => array(//图文分析
                    'summary' => '/datacube/getarticlesummary?',//获取图文群发每日数据
                    'total' => '/datacube/getarticletotal?',//获取图文群发总数据
                    'read' => '/datacube/getuserread?',//获取图文统计数据（getuserread）
                    'readhour' => '/datacube/getuserreadhour?',//获取图文统计分时数据
                    'share' => '/datacube/getusershare?',//获取图文分享转发数据
                    'sharehour' => '/datacube/getusersharehour?',//获取图文分享转发分时数据
            ),
            'upstreammsg' => array(//消息分析
                    'summary' => '/datacube/getupstreammsg?',//获取消息发送概况数据
                    'hour' => '/datacube/getupstreammsghour?',//获取消息分送分时数据
                    'week' => '/datacube/getupstreammsgweek?',//获取消息发送周数据
                    'month' => '/datacube/getupstreammsgmonth?',//获取消息发送月数据
                    'dist' => '/datacube/getupstreammsgdist?',//获取消息发送分布数据
                    'distweek' => '/datacube/getupstreammsgdistweek?',//获取消息发送分布周数据
                    'distmonth' => '/datacube/getupstreammsgdistmonth?',//获取消息发送分布月数据
            ),
            'interface' => array(//接口分析
                    'summary' => '/datacube/getinterfacesummary?',//获取接口分析数据
                    'summaryhour' => '/datacube/getinterfacesummaryhour?',//获取接口分析分时数据
            )
    );
    /**
     *  初始化
     *  $options = array(
     *          'token'          => 'Token', //填写你设定的Token
     *          'encodingAESKey' => 'encodingAESKey', //填写加密用的EncodingAESKey
     *          'appId'          => 'appId', //填写高级调用功能的appId
     *          'appSecret'      => 'appSecret' //填写高级调用功能的appSecret
     *  );
     */
    public function __construct($option=[])
    {
        $this->config();
        $this->token = isset($options['token'])?$options['token']:$this->configs['token'];
        $this->appId = isset($options['appId'])?$options['appId']:$this->configs['appId'];
        $this->appSecret = isset($options['appSecret'])?$options['appSecret']:$this->configs['appSecret'];
        $this->encodingAesKey = isset($options['encodingAesKey'])?$options['encodingAesKey']:$this->configs['encodingAesKey'];
        $this->accessToken = isset($options['accessToken'])?$options['accessToken']:$this->accessToken;
        $this->log = new Log();
    }
    /**
     * 验证服务器地址的有效性
     */
    private function checkSignature($echostr='')
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:$signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce     = isset($_GET["nonce"])?$_GET["nonce"]:'';

        $token  = $this->token;
        $tmpArr = array($token, $timestamp, $nonce, $echostr);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
//     public function test($value='')
//     {
//         $pc = new WxBizMsgCrypt('zjx','qvB1VkpijyInRcGaT4MwZvylrZlXVbsOv8BRYPVcUg5','wxa9ba75775bad4a83');
//         $signature='1f78f9de89d58d493712599a9e074c4f69c10c63';
//         $timestamp='1488526551';
//         $nonce='810713512';
//         $postStr='<xml>
//     <ToUserName><![CDATA[gh_6ec40d9f2640]]></ToUserName>
//     <Encrypt><![CDATA[NQrbf1/WiMuQpFlXrYG0jEbGj8qOnYmrK7P4JpV/3TERhWY8qppPl/Re0mJkYVVUjuZOU4vI2q3+reuhllKR53VRaKyFi7EIt3HPg0Zf65ASWNqdelzVeb7IlYpaPe56lIo9HkHYse2wWgYh4hkRkYI6+Zj/iZXrNrJgS6HCXmumIfNkMJDQIpb+V939X0bwL5PIftpFOIWNPtWfDofFrd9x/3DQI0JlRAfcUSwAXsqCGFDZhz238V1NsbxDtWjzUrYwRckHHCNcNGSMtHGerA5ic9IqfNirOpH99T3cyv1GnwI2yoVabMTp/IXVwVM6ovLW/cIKKexjPFDAQYYZvchQ/ZRhCpcWV0iDLRHUf6l+oTC2oha+LvySxCPq7od0u5zKSGx2HrBwZBDcngt2NS7bjBAHe0SVrFTYqGGEC04=]]></Encrypt>
// </xml>';
//         $res = $pc->decryptMsg($signature,$timestamp,$nonce,$postStr,$msg);
//         var_dump($msg);
//         $this->log->log($res);
//     }
    /**
     * 配置验证
     * @param  boolean $return [description]
     * @return [type]          [description]
     */
    public function valid($return=false)
    {
        $encryptStr="";
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $postStr = file_get_contents("php://input");
            $array = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encryptType = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"]: '';
            if ($this->encryptType == 'aes') { //aes加密
                $encryptStr = $array['Encrypt'];
                $pc = new Prpcrypt($this->encodingAesKey);
                $array = $pc->decrypt($encryptStr,$this->appId);

                if (!isset($array[0]) || ($array[0] != 0)) {
                    if (!$return) {
                        die('decrypt error!');
                    } else {
                        return false;
                    }
                }
                $this->postXml = $array[1];
                if (!$this->appId)
                    $this->appId = $array[2];//为了没有appid的订阅号。
            } else {
                $this->postXml = $postStr;
            }
        } elseif (isset($_GET["echostr"])) {
            $echoStr = $_GET["echostr"];
            if ($return) {
                if ($this->checkSignature())
                    return $echoStr;
                else
                    return false;
            } else {
                if ($this->checkSignature())
                    die($echoStr);
                else
                    die('no access');
            }
        }

        if (!$this->checkSignature($encryptStr)) {
            if ($return)
                return false;
            else
                die('no access');
        }

        return true;
        // $encryptStr="";
        // if ($_SERVER['REQUEST_METHOD'] == "POST") {
        //     $postStr = file_get_contents("php://input");
        //     $array = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        //     $this->encryptType = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"]: '';
        //     if ($this->encryptType == 'aes') { //aes加密
        //         $encryptStr = $array['Encrypt'];
        //         $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:''; 
        //         $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
        //         $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        //         $pc = new WxBizMsgCrypt($this->token,$this->encodingAesKey,$this->appId);
        //         $res = $pc->decryptMsg($signature,$timestamp,$nonce,$postStr,$msg);
        //         if (!isset($array[0]) || ($array[0] != 0)) {
        //             if (!$return) {
        //                 die('decrypt error!');
        //             } else {
        //                 return false;
        //             }
        //         }
        //         $this->postXml = $array[1];
        //         if (!$this->appId)
        //             $this->appId = $array[2];//为了没有appid的订阅号。
        //     } else {
        //         $this->postXml = $postStr;
        //     }
        // } elseif (isset($_GET["echostr"])) {
        //     $echoStr = $_GET["echostr"];
        //     if ($return) {
        //         if ($this->checkSignature())
        //             return $echoStr;
        //         else
        //             return false;
        //     } else {
        //         if ($this->checkSignature())
        //             die($echoStr);
        //         else
        //             die('no access');
        //     }
        // }

        // if (!$this->checkSignature($encryptStr)) {
        //     if ($return)
        //         return false;
        //     else
        //         die('no access');
        // }

        // return true;
        // $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        // if ($return) {
        //         if ($echoStr) {
        //             if ($this->checkSignature()) 
        //                 return $echoStr;
        //             else
        //                 return false;
        //         } else 
        //             return $this->checkSignature();
        // } else {
        //         if ($echoStr) {
        //             if ($this->checkSignature())
        //                 die($echoStr);
        //             else 
        //                 die('no access');
        //         }  else {
        //             if ($this->checkSignature())
        //                 return true;
        //             else
        //                 die('no access');
        //         }
        // }
        // return false;
    }
    /**
     * 获取错误信息
     * @return [type] [description]
     */
    public function getErrorText()
    {
        $errCode = new ErrCode();
        return $this->errCode.':'.$errCode::getErrText($this->errCode);
    }
    /******************************自定义菜单**********************************/
    /**
     * 创建普通菜单(认证后的订阅号可用)
     * @param array $data 菜单数组数据
     * example:
     *  array (
     *      'button' => array (
     *        0 => array (
     *          'name' => '扫码',
     *          'sub_button' => array (
     *              0 => array (
     *                'type' => 'scancode_waitmsg',
     *                'name' => '扫码带提示',
     *                'key' => 'rselfmenu_0_0',
     *              ),
     *              1 => array (
     *                'type' => 'scancode_push',
     *                'name' => '扫码推事件',
     *                'key' => 'rselfmenu_0_1',
     *              ),
     *          ),
     *        ),
     *        1 => array (
     *          'name' => '发图',
     *          'sub_button' => array (
     *              0 => array (
     *                'type' => 'pic_sysphoto',
     *                'name' => '系统拍照发图',
     *                'key' => 'rselfmenu_1_0',
     *              ),
     *              1 => array (
     *                'type' => 'pic_photo_or_album',
     *                'name' => '拍照或者相册发图',
     *                'key' => 'rselfmenu_1_1',
     *              )
     *          ),
     *        ),
     *        2 => array (
     *          'type' => 'location_select',
     *          'name' => '发送位置',
     *          'key' => 'rselfmenu_2_0'
     *        ),
     *      ),
     *  )
     * type可以选择为以下几种，其中5-8除了收到菜单事件以外，还会单独收到对应类型的信息。
     * 1、click：点击推事件
     * 2、view：跳转URL
     * 3、scancode_push：扫码推事件
     * 4、scancode_waitmsg：扫码推事件且弹出“消息接收中”提示框
     * 5、pic_sysphoto：弹出系统拍照发图
     * 6、pic_photo_or_album：弹出拍照或者相册发图
     * 7、pic_weixin：弹出微信相册发图器
     * 8、location_select：弹出地理位置选择器
     */
    public function createMenu($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MENU_CREATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * 创建个性化菜单(须先创建普通菜单)
        $data=array(
            'button' => array (
                0 => array (
                   'name' => '扫码',
                   'sub_button' => array (
                       0 => array (
                         'type' => 'scancode_waitmsg',
                         'name' => '扫码带提示',
                         'key' => 'rselfmenu_0_0',
                       ),
                       1 => array (
                         'type' => 'scancode_push',
                         'name' => '扫码推事件',
                         'key' => 'rselfmenu_0_1',
                       ),
                   ),
                ),
            ),
            'matchrule' => array (
                "sex"=>"1",
            ),
        );
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function createConditionalMenu($data)
    {
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MENU_ADD_CONDITIONAL_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * 获取菜单(认证后的订阅号可用)
     * @return array('menu'=>array(....s))
     */
    public function getMenu(){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::MENU_GET_URL.
                            'access_token='.$this->accessToken
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 删除菜单(认证后的订阅号可用)
     * @return boolean
     */
    public function deleteMenu(){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                                self::API_URL_PREFIX.
                                self::MENU_DELETE_URL.
                                'access_token='.$this->accessToken
                        );
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }
    /******************************消息管理**********************************/
    /**
     * 获取微信服务器发来的信息
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = !empty($this->postXml)?$this->postXml:file_get_contents("php://input");
        //兼顾使用明文又不想调用valid()方法的情况
        if (!empty($postStr)) {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }
    /**
     * 获取微信服务器发来的信息
     */
    public function getRevData()
    {
        return $this->_receive;
    }
    /**
     * 获取消息接受者
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else
            return false;
    }
    /**
     * 获取消息发送者
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else
            return false;
    }
    /**
     * 获取消息发送时间
     */
    public function getRevCreateTime() {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else
            return false;
    }
    /**
     * 获取接收消息的类型
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else
            return false;
    }
    /**
     * 获取接收消息内容正文
     */
    public function getRevContent()
    {
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容,需开通
            return $this->_receive['Recognition'];
        else
            return false;
    }
    /**
     * 获取接收消息图片
     */
    public function getRevPic()
    {
        if (isset($this->_receive['PicUrl']))
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'picurl'=>(string)$this->_receive['PicUrl'],    //防止picurl为空导致解析出错
            );
        else
            return false;
    }
    /**
     * 获取图片链接
     * @return [type] [description]
     */
    public function getRevPicUrl()
    {
        if (isset($this->_receive['PicUrl']))
            return (string)$this->_receive['PicUrl'];
        else
            return false;
    }
    /**
     * 获取语音识别消息
     * @return [type] [description]
     */
    public function getRevRecognition()
    {
        if (isset($this->_receive['Recognition']))
            return $this->_receive['Recognition'];
        else
            return false;
    }
    /**
     * 获取语音消息
     */
    public function getRevVoice()
    {
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'format'=>$this->_receive['Format'],
            );
        } else
            return false;
    }
    /**
     * 获取视频消息
     */
    public function getRevVideo()
    {
        if (isset($this->_receive['MediaId'])){
            return array(
                    'mediaid'=>$this->_receive['MediaId'],
                    'thumbmediaid'=>$this->_receive['ThumbMediaId']
            );
        } else
            return false;
    }
    /**
     * 获取地理位置消息
     */
    public function getRevLocation(){
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y'],
                'scale'=>$this->_receive['Scale'],
                'label'=>$this->_receive['Label']
            );
        } else
            return false;
    }
    /**
     * 获取链接消息
     */
    public function getRevLink()
    {
        if (isset($this->_receive['Url'])){
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        } else
            return false;
    }
    /**
     * 获取消息MediaId
     * @return [type] [description]
     */
    public function getRevMediaId()
    {
        if (isset($this->_receive['MediaId']))
            return $this->_receive['MediaId'];
        else
            return false;
    }
    /**
     * 获取消息ID
     */
    public function getRevID() 
    {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }
    /******************************网页开发**********************************/
    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callBack,$state='',$scope='snsapi_userinfo'){
        return self::OAUTH_PREFIX.
               self::OAUTH_AUTHORIZE_URL.
               'appid='.$this->appId.
               '&redirect_uri='.urlencode($callBack).
               '&response_type=code&scope='.$scope.
               '&state='.$state.
               '#wechat_redirect';
    }
    /**
     * 通过code获取Access Token
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken(){
        $code = isset($_GET['code'])?$_GET['code']:'';
        if (!$code) return false;
        $result = $this->httpGet(
                            self::API_BASE_URL_PREFIX.
                            self::OAUTH_TOKEN_URL.
                            'appid='.$this->appId.
                            '&secret='.$this->appSecret.
                            '&code='.$code.
                            '&grant_type=authorization_code'
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->userToken = $json['access_token'];
            return $json;
        }
        return false;
    }
    /******************************事件相关**********************************/
    /**
     * 获取接收事件推送
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
            $array['event'] = $this->_receive['Event'];
        }
        if (isset($this->_receive['EventKey'])){
            $array['key'] = $this->_receive['EventKey'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }
    /**
     * 获取上报地理位置事件
     */
    public function getRevEventLocation(){
            if (isset($this->_receive['Latitude'])){
                return array(
                'x'=>$this->_receive['Latitude'],
                'y'=>$this->_receive['Longitude'],
                'precision'=>$this->_receive['Precision'],
            );
        } else
            return false;
    }
    /**
    * 获取群发或模板消息发送结果
    * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH，即高级群发/模板消息
    */
    public function getRevResult(){
        if (isset($this->_receive['Status'])) //发送是否成功，具体的返回值请参考 高级群发/模板消息 的事件推送说明
            $array['Status'] = $this->_receive['Status'];
        if (isset($this->_receive['MsgID'])) //发送的消息id
            $array['MsgID'] = $this->_receive['MsgID'];

        //以下仅当群发消息时才会有的事件内容
        if (isset($this->_receive['TotalCount']))     //分组或openid列表内粉丝数量
            $array['TotalCount'] = $this->_receive['TotalCount'];
        if (isset($this->_receive['FilterCount']))    //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数
            $array['FilterCount'] = $this->_receive['FilterCount'];
        if (isset($this->_receive['SentCount']))     //发送成功的粉丝数
            $array['SentCount'] = $this->_receive['SentCount'];
        if (isset($this->_receive['ErrorCount']))    //发送失败的粉丝数
            $array['ErrorCount'] = $this->_receive['ErrorCount'];
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }
    /******************************回复消息**********************************/
    /**
     * 设置回复文本消息
     * @param string $text
     */
    public function text($text='')
    {

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$this->autoTextFilter($text),
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复图片消息
     * @param  string $mediaId [通过素材管理接口上传多媒体文件，得到的id]
     * @return [type]          [description]
     */
    public function image($mediaId='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_IMAGE,
            'Image'=>array('MediaId'=>$mediaId),
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复语音消息
     * @param string $mediaid
     */
    public function voice($mediaId='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_VOICE,
            'Voice'=>array('MediaId'=>$mediaId),
            'CreateTime'=>time(),
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复视频消息
     * @param string $mediaId
     */
    public function video($mediaId='',$title='',$description='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_VIDEO,
            'Video'=>array(
                    'MediaId'=>$mediaId,
                    'Title'=>$title,
                    'Description'=>$description
            ),
            'CreateTime'=>time(),
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复音乐
     * @param string $title
     * @param string $desc
     * @param string $musicUrl
     * @param string $hqMusicUrl
     * @param string $thumbmediaid 音乐图片缩略图的媒体id，非必须
     */
    public function music($title,$desc,$musicUrl,$hqMusicUrl='',$thumbMediaId='') 
    {
        $msg = array(
            'ToUserName'  => $this->getRevFrom(),
            'FromUserName'=> $this->getRevTo(),
            'CreateTime'  => time(),
            'MsgType'     => self::MSGTYPE_MUSIC,
            'Music'       => array(
                'Title'      => $title,
                'Description'=> $desc,
                'MusicUrl'   => $musicUrl,
                'HQMusicUrl' => $hqMusicUrl
            ),
        );
        if ($thumbMediaId) {
            $msg['Music']['ThumbMediaId'] = $thumbMediaId;
        }
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * 数组结构:
     *  array(
     *      "0"=>array(
     *          'Title'=>'Title',
     *          'Description'=>'Description',
     *          'PicUrl'=>'PicUrl',
     *          'Url'=>'Url'
     *      ),
     *      "1"=>....
     *  )
     */
    public function news($newsData=array())
    {
        $count = count($newsData);

        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_NEWS,
            'CreateTime'   => time(),
            'ArticleCount' => $count,
            'Articles'     => $newsData,
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置发送消息
     * @param array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     */
    public function Message($msg = '',$append = false)
    {
        if (is_null($msg)) {
            $this->_msg =array();
        }elseif (is_array($msg)) {
            if ($append)
                $this->_msg = array_merge($this->_msg,$msg);
            else
                $this->_msg = $msg;
            return $this->_msg;
        } else {
            return $this->_msg;
        }
    }
    /**
     * 
     * 回复微信服务器, 此函数支持链式操作
     * Example: $this->text('msg tips')->reply();
     * @param string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     */
    public function reply($msg=array(),$return = false)
    {

        if (empty($msg)) {
            if (empty($this->_msg))//防止不先设置回复内容直接调用reply方法导致异常
                return false;
            $msg = $this->_msg;
        }

        $xmldata = $this->xmlEncode($msg);

        if ($this->encryptType == 'aes') { //如果来源消息为加密方式
            $pc = new Prpcrypt($this->encodingAesKey);

            $array = $pc->encrypt($xmldata, $this->appId);

            $ret = $array[0];

            if ($ret != 0) {
                return false;
            }

            $timestamp = time();
            $nonce = rand(77,999)*rand(605,888)*rand(11,99);
            $encrypt = $array[1];
            $tmpArr = array($this->token, $timestamp, $nonce, $encrypt);//比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $signature = implode($tmpArr);
            $signature = sha1($signature);

            $xmldata = $this->generate($encrypt, $signature, $timestamp, $nonce);
        }

        if ($return)
            return $xmldata;
        else
            echo $xmldata;
        // if (empty($msg)){
        //     $msg = $this->_msg;
        // } 
        // $xmldata=  $this->xmlEncode($msg);
        
        // if ($return)
        //     return $xmldata;
        // else
            //     echo $xmldata;
    }
    /******************************模版消息**********************************/
    /**
     * 模板消息 设置所属行业
     * @param int $id1  公众号模板消息所属行业编号，参看官方开发文档 行业代码
     * @param int $id2  同$id1。但如果只有一个行业，此参数可省略
     * @return boolean|array
     */
    public function setTMIndustry($id1,$id2=''){
        if ($id1) $data['industry_id1'] = $id1;
        if ($id2) $data['industry_id2'] = $id2;
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_SET_INDUSTRY_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取设置的行业信息
     * @return [type] [description]
     */
    public function getTMIndustry()
    {
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_SET_INDUSTRY_URL.
                            'access_token='.$this->accessToken.
                            '&type=jsapi'
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 模板消息 添加消息模板
     * 成功返回消息模板的调用id
     * @param string $tpl_id 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @return boolean|string
     */
    public function addTemplateMessage($tpl_id){
        $data = array ('template_id_short' =>$tpl_id);
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_ADD_TPL_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json['template_id'];
        }
        return false;
    }
    /**
     * 获取已添加至帐号下所有模板列表
     * @return [type] [description]
     */
    public function getTMList()
    {
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_GET_LIST_URL.
                            'access_token='.$this->accessToken.
                            '&type=jsapi'
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 删除模板
     * $data=array('template_id'=>'template_id')
     * @param [type] $data [description]
     */
    public function delTemplate($data)
    {
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_DEL_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 发送模板消息
     * @param array $data 消息结构
     *  {
            "touser":"OPENID",
            "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
            "url":"http://weixin.qq.com/download",
            "topcolor":"#FF0000",
            "data":{
                "参数名1": {
                    "value":"参数",
                    "color":"#173177"    //参数颜色
                    },
                "Date":{
                    "value":"06月07日 19时24分",
                    "color":"#173177"
                    },
                "CardNumber":{
                    "value":"0426",
                    "color":"#173177"
                    },
                "Type":{
                    "value":"消费",
                    "color":"#173177"
                    }
            }
        }
     * @return boolean|array
     */
    public function sendTemplateMessage($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::TEMPLATE_SEND_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /******************************群发消息**********************************/
    /**
     * 上传图片素材(认证后的订阅号可用)
     * 请注意，本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。
     * 图片仅支持jpg/png格式，大小必须在1MB以下。
     * @param  [type] $data E:\www\think\public\1.png
     * @return [type]       [description]
     */
    public function uploadImg($data)
    {
        if (!$this->accessToken && !$this->checkAuth()) return 1;
        
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_UPLOADIMG_URL.
                            'access_token='.$this->accessToken,
                            $data,
                            true
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 上传视频素材(认证后的订阅号可用)
     * @param array $data 消息结构
     * {
     *     "media_id"=>"",     //通过上传媒体接口得到的MediaId
     *     "title"=>"TITLE",    //视频标题
     *     "description"=>"Description"        //视频描述
     * }
     * @return boolean|array
     * {
     *     "type":"video",
     *     "media_id":"mediaid",
     *     "created_at":1398848981
     *  }
     */
    public function uploadMpVideo($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::UPLOAD_MEDIA_URL.
                            self::MEDIA_VIDEO_UPLOAD.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 上传图文消息素材，用于群发(认证后的订阅号可用)
     * @param array $data 消息结构{"articles":[{...}]}
     * @return boolean|array
     */
    public function uploadArticles($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_UPLOADNEWS_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     *  根据群组id群发图文消息(认证后的订阅号可用)
     *  注意：视频需要在调用uploadMedia()方法后，再使用uploadMpVideo()方法生成，
     *             然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param array $data 消息结构
     * {
     *     "filter"=>array(
     *         "is_to_all"=>False,//是否群发给所有用户True不用分组id,False需填写分组id
     *         "group_id"=>"2"//群发的分组id
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function sendGroupMassMessage($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MASS_SEND_GROUP_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     *  根据OpenID列表群发图文消息(订阅号不可用)
     *  注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *             然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param array $data 消息结构
     * {
     *     "touser"=>array(
     *         "OPENID1",
     *         "OPENID2"
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function sendMassMessage($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MASS_SEND_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 删除群发图文消息(认证后的订阅号可用)
     * 只能删除图文消息和视频消息
     * @param int $msg_id 消息id
     * @return boolean|array
     */
    public function deleteMassMessage($msgId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MASS_DELETE_URL.
                            'access_token='.$this->accessToken,
                            self::json_encode(array('msg_id'=>$msgId))
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     *  预览群发消息(认证后的订阅号可用)
     *  注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *             然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param array $data 消息结构
     * {
     *     "touser"=>"OPENID",
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function previewMassMessage($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MASS_PREVIEW_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 高级群发消息, 查询群发消息发送状态(认证后的订阅号可用)
     * @param int $msg_id 消息id
     * @return boolean|array
     * {
     *     "msg_id":201053012,     //群发消息后返回的消息id
     *     "msg_status":"SEND_SUCCESS" //消息发送后的状态，SENDING表示正在发送 SEND_SUCCESS表示发送成功
     * }
     */
    public function queryMassMessage($msgId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MASS_QUERY_URL.
                            'access_token='.$this->accessToken,
                            self::json_encode(array('msg_id'=>$msgId))
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /******************************素材管理**********************************/
    /**
     * 上传临时素材，有效期为3天(认证后的订阅号可用)
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * 注意：临时素材的media_id是可复用的！
     * @param array $data {"media":'@Path\filename.jpg'}
     * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @return boolean|array
     */
    public function uploadMedia($data, $type){
        if (!$this->accessToken && !$this->checkAuth()) return 1;
        
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_UPLOAD_URL.
                            'access_token='.$this->accessToken.
                            '&type='.$type,
                            $data,
                            true
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取临时素材(认证后的订阅号可用)
     * @param string $media_id 媒体文件id
     * @param boolean $is_video 是否为视频文件，默认为否
     * @return raw data
     */
    public function getMedia($mediaId,$isVideo=false){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        //原先的上传多媒体文件接口使用 self::UPLOAD_MEDIA_URL 前缀
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        $urlPrefix = $isVideo?str_replace('https','http',self::API_URL_PREFIX):self::API_URL_PREFIX;
        $result = $this->httpGet(
                            $urlPrefix.
                            self::MEDIA_GET_URL.
                            'access_token='.$this->accessToken.
                            '&media_id='.$mediaId
                        );
        if ($result){
            if (is_string($result)) {
                $json = json_decode($result,true);
                if (isset($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
            }
            return $result;
        }
        return false;
    }
    /**
     * 上传永久图文素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * {
            "articles": [
                {
                   "title": TITLE,
                   "thumb_media_id": THUMB_MEDIA_ID,
                   "author": AUTHOR,
                   "digest": DIGEST,
                   "show_cover_pic": SHOW_COVER_PIC(0 / 1),
                   "content": CONTENT,
                   "content_source_url": CONTENT_SOURCE_URL
                },
                //若新增的是多图文素材，则此处应有几段articles结构，最多8段
            ]
        }
     * @param array $data 消息结构{"articles":[{...}]}
     * @return boolean|array
     */
    public function uploadForeverArticles($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_NEWS_UPLOAD_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 上传永久素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $data {"media":'@Path\filename.jpg'}
     * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @param boolean $is_video 是否为视频文件，默认为否
     * @param array $video_info 视频信息数组，非视频素材不需要提供 array('title'=>'视频标题','introduction'=>'描述')
     * @return boolean|array
     */
    public function uploadForeverMedia($data,$type,$isVideo=false,$videoInfo=array()){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        //#TODO 暂不确定此接口是否需要让视频文件走http协议
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        //$url_prefix = $is_video?str_replace('https','http',self::API_URL_PREFIX):self::API_URL_PREFIX;
        //当上传视频文件时，附加视频文件信息
        if ($isVideo) $data['description'] = self::json_encode($videoInfo);
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_UPLOAD_URL.
                            'access_token='.$this->accessToken.
                            '&type='.$type,
                            $data,
                            true
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取永久素材(认证后的订阅号可用)
     * 返回图文消息数组或二进制数据，失败返回false
     * @param string $media_id 媒体文件id
     * @param boolean $is_video 是否为视频文件，默认为否
     * @return boolean|array|raw data
     */
    public function getForeverMedia($mediaId,$isVideo=false){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array('media_id' => $mediaId);
        //#TODO 暂不确定此接口是否需要让视频文件走http协议
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        //$url_prefix = $isVideo?str_replace('https','http',self::API_URL_PREFIX):self::API_URL_PREFIX;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_GET_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            if (is_string($result)) {
                $json = json_decode($result,true);
                if ($json) {
                    if (isset($json['errcode'])) {
                        $this->errCode = $json['errcode'];
                        $this->errMsg = $json['errmsg'];
                        return false;
                    }
                    return $json;
                } else {
                    return $result;
                }
            }
            return $result;
        }
        return false;
    }
    /**
     * 删除永久素材(认证后的订阅号可用)
     * @param string $media_id 媒体文件id
     * @return boolean
     */
    public function delForeverMedia($mediaId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array('media_id' => $mediaId);
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_DEL_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * 修改永久图文素材(认证后的订阅号可用)
     * 永久素材也可以在公众平台官网素材管理模块中看到
     * @param string $media_id 图文素材id
     * @param array $data 消息结构{"articles":[{...}]}
     * @param int $index 更新的文章在图文素材的位置，第一篇为0，仅多图文使用
     * @return boolean|array
     */
    public function updateForeverArticles($mediaId,$data,$index=0){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        if (!isset($data['media_id'])) $data['media_id'] = $mediaId;
        if (!isset($data['index'])) $data['index'] = $index;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_NEWS_UPDATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取永久素材总数(认证后的订阅号可用)
     * @return boolean|array
     * 返回数组格式:
     * array(
     *  'voice_count'=>0, //语音总数量
     *  'video_count'=>0, //视频总数量
     *  'image_count'=>0, //图片总数量
     *  'news_count'=>0   //图文总数量
     * )
     */
    public function getForeverCount(){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_COUNT_URL.
                            'access_token='.$this->accessToken
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取永久素材列表(认证后的订阅号可用)
     * @param string $type 素材的类型,图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param int $offset 全部素材的偏移位置，0表示从第一个素材
     * @param int $count 返回素材的数量，取值在1到20之间
     * @return boolean|array
     * 返回数组格式:
     * array(
     *  'total_count'=>0, //该类型的素材的总数
     *  'item_count'=>0,  //本次调用获取的素材的数量
     *  'item'=>array()   //素材列表数组，内容定义请参考官方文档
     * )
     */
    public function getForeverList($type='image',$offset=0,$count=10){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
            'type' => $type,
            'offset' => $offset,
            'count' => $count,
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::MEDIA_FOREVER_BATCHGET_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /******************************用户管理**********************************/
    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'group'=>array('name'=>$name)
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::GROUP_CREATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public function getGroup(){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::GROUP_GET_URL.
                            'access_token='.$this->accessToken
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取用户所在分组
     * @param string $openid
     * @return boolean|int 成功则返回用户分组id
     */
    public function getUserGroup($openid){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'openid'=>$openid
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::USER_GROUP_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            } else
                if (isset($json['groupid'])) return $json['groupid'];
        }
        return false;
    }
    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupId,$name){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'group'=>array('id'=>$groupId,'name'=>$name)
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::GROUP_UPDATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupId,$openId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'openid'=>$openId,
                'to_groupid'=>$groupId
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::GROUP_MEMBER_UPDATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 批量移动用户分组
     * @param int $groupid 分组id
     * @param string $openid_list 用户openid数组,一次不能超过50个
     * @return boolean|array
     */
    public function batchUpdateGroupMembers($groupId,$openIdList){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'openid_list'=>$openIdList,
                'to_groupid'=>$groupId
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::GROUP_MEMBER_BATCHUPDATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 删除分组
     * @param int $groupid 分组id
     * @return boolean|array
     */
    public function deleteGroup($groupId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
                'group'=>array('id'=>$groupId)
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::GROUP_DEL_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 设置用户备注名
     * @param string $openid
     * @param string $remark 备注名
     * @return boolean|array
     */
    public function updateUserRemark($openId,$remark){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
            'openid'=>$openId,
            'remark'=>$remark
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::USER_UPDATEREMARK_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($openId){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::USER_INFO_URL.
                            'access_token='.$this->accessToken.
                            '&openid='.$openId
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
    * 批量获取用户基本信息
    * $data=array(
    *         'user_list'=>array(
    *             0=>array(
    *                 openid=>openid,
    *                 lang=>lang,
    *             ),
    *             1=>array(
    *                 openid=>openid,
    *                 lang=>lang,
    *             ),
    *         )
    *     );
    * @param  array $data openId数组
    * @return [type]       [description]
    */
    public function getUserInfoBarchget($data){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::USER_INFO_BARCHGET_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 批量获取关注用户列表
     * @param  $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     */
    public function getUserList($nextOpenId=''){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::USER_GET_URL.
                            'access_token='.$this->accessToken.
                            '&next_openid='.$nextOpenId
                        );
        if ($result){
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /******************************帐号管理**********************************/
    /**
     * 创建二维码ticket
     * @param int|string $scene_id 自定义追踪id,临时二维码只能用数值型
     * @param int $type 0:临时二维码；1:数值型永久二维码(此时expire参数无效)；2:字符串型永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为2592000秒
     * @return array('ticket'=>'qrcode字串','expire_seconds'=>2592000,'url'=>'二维码图片解析后的地址')
     */
    public function createQRCode($sceneId,$type=0,$expire=2592000){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        if (!isset($sceneId)) return false;
        switch ($type) {
            case '0':
                if (!is_numeric($sceneId))
                    return false;
                $actionName = 'QR_SCENE';
                $actionInfo = array('scene'=>(array('scene_id'=>$sceneId)));
                break;

            case '1':
                if (!is_numeric($sceneId))
                    return false;
                $actionName = 'QR_LIMIT_SCENE';
                $actionInfo = array('scene'=>(array('scene_id'=>$sceneId)));
                break;

            case '2':
                if (!is_string($sceneId))
                    return false;
                $actionName = 'QR_LIMIT_STR_SCENE';
                $actionInfo = array('scene'=>(array('scene_str'=>$sceneId)));
                break;

            default:
                return false;
        }

        $data = array(
            'action_name'    => $actionName,
            'expire_seconds' => $expire,
            'action_info'    => $actionInfo
        );
        if ($type) {
            unset($data['expire_seconds']);
        }

        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::QRCODE_CREATE_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket) {
        return self::QRCODE_IMG_URL.urlencode($ticket);
    }
    /**
     * 长链接转短链接接口
     * @param string $long_url 传入要转换的长url
     * @return boolean|string url 成功则返回转换后的短url
     */
    public function getShortUrl($longUrl){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        $data = array(
            'action'=>'long2short',
            'long_url'=>$longUrl
        );
        $result = $this->httpPost(
                            self::API_URL_PREFIX.
                            self::SHORT_URL.
                            'access_token='.$this->accessToken,
                            self::jsonEncode($data)
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json['short_url'];
        }
        return false;
    }
    /******************************数据统计**********************************/
    /**
     * 获取统计数据
     * $weObj->getDatacube('user','cumulate','2017-02-08','2017-02-15');
     * @param  type $type     数据分类(user|article|upstreammsg|interface)分别为(用户分析|图文分析|消息分析|接口分析)
     * @param  string $subType   数据子分类，参考 DATACUBE_URL_ARR 常量定义部分
     * @param  string $beginDate [开始时间]
     * @param  string $endDate   [结束时间]
     * @return boolean|array 成功返回查询结果数组，其定义请看官方文档
     */
    public function getDatacube($type,$subType,$beginDate,$endDate=''){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        if (!isset(self::$DATACUBE_URL_ARR[$type]) || !isset(self::$DATACUBE_URL_ARR[$type][$subType]))
            return false;
        $data = array(
            'begin_date'=>$beginDate,
            'end_date'=>$endDate?$endDate:$beginDate
        );
        $result = $this->httpPost(
                                self::API_BASE_URL_PREFIX.
                                self::$DATACUBE_URL_ARR[$type][$subType].
                                'access_token='.$this->accessToken,
                                self::jsonEncode($data)
                            );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return isset($json['list'])?$json['list']:$json;
        }
        return false;
    }
    /******************************凭证获取**********************************/
    /**
     * 获取access_token
     * @param string $appid 如在类初始化时已提供，则可为空
     * @param string $appsecret 如在类初始化时已提供，则可为空
     * @param string $token 手动指定access_token，非必要情况不建议用
     */
    public function checkAuth($appId='',$appSecret='',$token=''){
        if (!$appId || !$appSecret) {
            $appId = $this->appId;
            $appSecret = $this->appSecret;
        }
        if ($token) { //手动指定token，优先使用
            $this->accessToken=$token;
            return $this->accessToken;
        }
        $authName = 'access_token_'.$appId;
        if ($rs = $this->getCache($authName))  {
            $this->accessToken = $rs;
            return $rs;
        }
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::AUTH_URL.
                            'appid='.$appId.
                            '&secret='.$appSecret
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg  = $json['errmsg'];
                return false;
            }
            $this->accessToken = $json['access_token'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 7100;
            $this->setCache($authName,$this->accessToken,$expire);
            return $this->accessToken;
        }
        return false;
    }
    /**
     * 获取JSAPI授权TICKET
     * @param string $appid 用于多个appid时使用,可空
     * @param string $jsapi_ticket 手动指定jsapi_ticket，非必要情况不建议用
     */
    public function getJsTicket($appId='',$jsapiTicket=''){
        if (!$this->accessToken && !$this->checkAuth()) return false;
        if (!$appId) $appId = $this->appId;
        if ($jsapiTicket) { //手动指定token，优先使用
            $this->jsapiTicket = $jsapiTicket;
            return $this->jsapiTicket;
        }
        $authName = 'jsapi_ticket_'.$appId;
        if ($rs = $this->getCache($authName))  {
            $this->jsapiTicket = $rs;
            return $rs;
        }
        $result = $this->httpGet(
                            self::API_URL_PREFIX.
                            self::GET_TICKET_URL.
                            'access_token='.$this->accessToken.
                            '&type=jsapi'
                        );
        if ($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->jsapiTicket = $json['ticket'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 7100;
            $this->setCache($authName,$this->jsapiTicket,$expire);
            return $this->jsapiTicket;
        }
        return false;
    }
    /******************************工具函数**********************************/
    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function data2xml($data)
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data2xml($val) : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }   
    /**
     * 过滤xml特殊字符
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
    */
    public function xmlEncode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') 
    {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = '';
        $xml   .= "<{$root}{$attr}>";
        $xml   .= self::data2xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }
    /**
     * 过滤文字回复\r\n换行符
     * @param string $text
     * @return string|mixed
     */
    private function autoTextFilter($text) 
    {
        if (!$this->_textFilter){
            return $text;
        } 
        return str_replace("\r\n", "\n", $text);
    }
    /**
     * GET 请求
     * @param string $url
     */
    private function httpGet($url)
    {
        $ch = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($ch);
        $aStatus  = curl_getinfo($ch);
        curl_close($ch);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function httpPost($url,$param,$postFile=false){
        $ch = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $postFile) {
            //$strPOST = new \CURLFile($param);        
            if($postFile){
                if (class_exists('\CURLFile')) {  
                    $strPOST = array('media' => new \CURLFile($param));
                } else {  
                    $strPOST = array('media' => '@' . $param);  
                } 
            }else{
                $strPOST = $param; 
            }   
        } else {
            $arrPOST = array();
            foreach($param as $key=>$val){
                $arrPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $arrPOST);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($ch);
        $aStatus  = curl_getinfo($ch);
        curl_close($ch);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * 获取缓存
     * @param  [type] $authName [description]
     * @return [type]           [description]
     */
    private function getCache($authName)
    {
        if(file_exists(self::CACHE_FILE)){
            $data = json_decode(file_get_contents(self::CACHE_FILE), true);
            if(empty($data[$authName])){
                return false;
            }
            if($data[$authName]['expire'] < time()){
                return false;  
            }else{
                return $data[$authName]['accessToken'];
            }
        }else{
            return false;
        }
    }
    /**
     * 设置缓存
     * @param [type] $authName    [description]
     * @param [type] $accessToken [description]
     * @param [type] $expire      [description]
     */
    private function setCache($authName,$accessToken,$expire)
    {
        if (!is_dir(self::CACHE_PATH)){
            mkdir(self::CACHE_PATH, 0777); //使用最大权限0777创建文件
        } 
        if (!file_exists(self::CACHE_FILE)) { //如果不存在则创建

            $fp = fopen(self::CACHE_FILE, "w");

            chmod(self::CACHE_FILE, 0777); //修改为0777最大权限

            $data[$authName]['accessToken'] = $accessToken;
            $data[$authName]['expire']      = time() + $expire;
            $data[$authName]['number']      = 1;

            fwrite($fp, json_encode($data)); 
            fclose($fp);
        }else{
            $data = json_decode(file_get_contents(self::CACHE_FILE), true);
            
            $data[$authName]['accessToken'] = $accessToken;
            $data[$authName]['expire'] = time() + $expire;
            $data[$authName]['number'] = empty($data[$authName])?1:$data[$authName]['number']+1;

            $fp = fopen(self::CACHE_FILE, "w");
            fwrite($fp, json_encode($data)); 
            fclose($fp);
        }
    }
    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    private static function jsonEncode($arr) {
        if (count($arr) == 0) return "[]";
        $parts  = array ();
        $isList = false;

        $keys = array_keys ($arr);
        $maxLength = count ($arr) - 1;
        if (($keys [0] === 0) && ($keys [$maxLength] === $maxLength )) { 
            $isList = true;
            for($i = 0; $i < count ( $keys ); $i ++) { 
                if ($i != $keys [$i]) { 
                    $isList = false; 
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) {
                if ($isList)
                    $parts [] = self::jsonEncode ( $value ); 
                else
                    $parts [] = '"' . $key . '":' . self::jsonEncode ( $value ); 
            } else {
                $str = '';
                if (! $isList)
                    $str = '"' . $key . '":';
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; 
                elseif ($value === false)
                    $str .= 'false'; 
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; 
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($isList)
            return '[' . $json . ']'; 
        return '{' . $json . '}'; 
    }
     /**
     * xml格式加密，仅请求为加密方式时再用
     */
    private function generate($encrypt, $signature, $timestamp, $nonce)
    {
        //格式化加密信息
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    public function config(){
        $configs=require '/Config.php';
        foreach ($configs as $key => $config) {
            $this->configs[$key]=$config;
        }
    }
}