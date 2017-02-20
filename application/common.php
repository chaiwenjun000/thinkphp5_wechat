<?php
// 应用公共文件
/**
 * dump的增强
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function dd($data){
    dump($data);
    die;
}
/**
 * 简单加密
 * @return [type] [description]
 */
function wxEncrypt($data){
    return md5($data);
}