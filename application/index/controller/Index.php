<?php
namespace app\index\controller;

use wechat\Wechat;
use wechat\ErrCode;


class Index
{
    public function index()
    {
        $weObj = new Wechat();
        $weObj->valid();
        $weObj->getRev();
        
        // $weObj->checkAuth();
        // $weObj->getJsTicket();
        // $data1=array (
        //    'button' => array (
        //      0 => array (
        //        'name' => '扫码',
        //        'sub_button' => array (
        //            0 => array (
        //              'type' => 'scancode_waitmsg',
        //              'name' => '扫码带提示',
        //              'key' => 'rselfmenu_0_0',
        //            ),
        //            1 => array (
        //              'type' => 'scancode_push',
        //              'name' => '扫码推事件',
        //              'key' => 'rselfmenu_0_1',
        //            ),
        //        ),
        //      ),
        //      1 => array (
        //        'name' => '发图',
        //        'sub_button' => array (
        //            0 => array (
        //              'type' => 'pic_sysphoto',
        //              'name' => '系统拍照发图',
        //              'key' => 'rselfmenu_1_0',
        //            ),
        //            1 => array (
        //              'type' => 'pic_photo_or_album',
        //              'name' => '拍照或者相册发图',
        //              'key' => 'rselfmenu_1_1',
        //            )
        //        ),
        //      ),
        //      2 => array (
        //        'type' => 'location_select',
        //        'name' => '发送位置',
        //        'key' => 'rselfmenu_2_0'
        //         ),
        //     ),
        // );
        // $data2=array(
        //     'button' => array (
        //         0 => array (
        //            'name' => '扫码',
        //            'sub_button' => array (
        //                0 => array (
        //                  'type' => 'scancode_waitmsg',
        //                  'name' => '扫码带提示',
        //                  'key' => 'rselfmenu_0_0',
        //                ),
        //                1 => array (
        //                  'type' => 'scancode_push',
        //                  'name' => '扫码推事件',
        //                  'key' => 'rselfmenu_0_1',
        //                ),
        //            ),
        //         ),
        //         1 => array (
        //            'name' => '发图',
        //            'sub_button' => array (
        //                0 => array (
        //                     'type' => 'pic_sysphoto',
        //                     'name' => '系统拍照发图',
        //                     'key' => 'rselfmenu_1_0',
        //                ),
        //                1 => array (
        //                     'type' => 'pic_photo_or_album',
        //                     'name' => '拍照或者相册发图',
        //                     'key' => 'rselfmenu_1_1',
        //                )
        //            ),
        //         ),
        //     ),
        //     'matchrule' => array (
        //         "sex"=>"1",
        //     ),
        // );
        // $weObj->createMenu($data1);
        // $weObj->createConditionalMenu($data2);
        // $weObj->text($weObj->errCode)->reply();
        //$weObj->deleteMenu();
        //$weObj->getMenu();
        switch ($weObj->getRev()->getRevType()) {
            case 'text':
                $weObj->text('1231')->reply();
                break;
            case 'image':
                $weObj->image($weObj->getRev()->getRevMediaId())->reply();
                break;
            case 'voice':
                $weObj->text($weObj->getRev()->getRevContent())->reply();
                break;
        }
    }
    public function test(){
        $weObj = new Wechat();
        
        // $data=array(
        //         'articles'=>array(
        //             0=>array(
        //                  "thumb_media_id"=>"kzlh8NVy2Ys9JeqKntLhPqtCCBEm5Z_R6O18XLTngMzFuhWLAl4jOnvNtZ_2q3yN",
        //                  "author"=>"xxx",
        //                  "title"=>"Happy Day",
        //                  "content_source_url"=>"www.qq.com",
        //                  "content"=>"content",
        //                  "digest"=>"digest",
        //                  "show_cover_pic"=>1
        //                 )
        //             )
        //     );
        // $data=array(
        //     'filter'=>array(
        //         'is_to_all'=>true,
        //         ),
        //     'mpnews'=>array(
        //         'media_id'=>'JqOXqbFO7IYBF4487AZAhNvGMpo_6ndzCwHhgqw6lCwRWsuaK-hZRBuwTwIYF2yz',
        //         ),
        //     'msgtype'=>'mpnews',
        //     );
        //$res=$weObj->sendGroupMassMessage($data);
        $res=$weObj->getMenu();
        // $errCode = new ErrCode();
        // dump($errCode::getErrText($weObj->errCode));
        dump($res);
    }
}
