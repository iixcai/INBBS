﻿## 简介

NBBS一个比较稳定的测试版发布了，正如你现在看到的样子，当然，它看起来离一个轻型社区的样子还差很多，但是目前核心的功能已经实现，在9.4正式不删数据测试。
## v0.2.0 build-150827
###论坛说明:  
INBBS是由PHP语言编写，使用Mysql数据库，缓存使用的是Kvdb、Memcache进行数据缓存，Storage存放上传的文件，目前INBBS轻论坛部署在SAE（新浪云计算）上。 
核心组件： 
1、Thinkphp框架 
2、Bootstrap前端框架


*  MVC支持-基于多层模型（M）、视图（V）、控制器（C）的设计模式
*  ORM支持-提供了全功能和高性能的ORM支持，支持大部分数据库
*  模板引擎支持-内置了高性能的基于标签库和XML标签的编译型模板引擎
*  RESTFul支持-通过REST控制器扩展提供了RESTFul支持，为你打造全新的URL设计和访问体验
*  云平台支持-提供了对新浪SAE平台和百度BAE平台的强力支持，具备“横跨性”和“平滑性”，支持本地化开发和调试以及部署切换，让你轻松过渡，打造全新的开发体验。
*  CLI支持-支持基于命令行的应用开发
*  RPC支持-提供包括PHPRpc、HProse、jsonRPC和Yar在内远程调用解决方案
*  MongoDb支持-提供NoSQL的支持
*  缓存支持-提供了包括文件、数据库、Memcache、Xcache、Redis等多种类型的缓存支持

## 大道至简的开发理念  
*  登录注册 
*  注册激活
*  主题发布 
*  评论发布 
*  修改资料
*  主题收藏
*  帐户锁定
*  节点分类
*  ......

## 安全性

框架在系统层面提供了众多的安全特性，确保你的网站和产品安全无忧。这些特性包括：

*  XSS安全防护
*  表单自动验证
*  强制数据类型转换
*  输入数据过滤
*  表单令牌验证
*  防SQL注入
*  图像上传检测

## 商业友好的开源协议

遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。
