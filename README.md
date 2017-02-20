微信开发包
===============

### 版本要求

>>=PHP5.6

### 实现以下功能

>自定义菜单  
>消息管理（客服消息未实现）  
>微信网页授权  
>素材管理  
>用户管理  
>帐号管理  
>数据管理

### 配置

在```extend\wechat\Config.php```中配置

### 使用


```
Use wechat\Wechat;
$weObj = new Wechat();
$weObj->valid();
```
