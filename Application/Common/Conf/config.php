<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'bbs',          // 数据库名
    'DB_USER'               =>  'bbs',      // 用户名
    'DB_PWD'                =>  'bbs',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    =>  false, // 数据库查询的SQL创建缓存 3.2.3版本废弃
    'DB_SQL_BUILD_QUEUE'    =>  'file',   // SQL缓存队列的缓存方式 支持 file xcache和apc 3.2.3版本废弃
    'DB_SQL_BUILD_LENGTH'   =>  20, // SQL缓存的队列长度 3.2.3版本废弃
    'DB_SQL_LOG'            =>  false, // SQL执行日志记录 3.2.3版本废弃
    'DB_BIND_PARAM'         =>  false, // 数据库写入数据自动参数绑定
    'DB_DEBUG'              =>  false,  // 数据库调试模式 3.2.3新增
    'DB_LITE'               =>  false,  // 数据库Lite模式 3.2.3新增

    'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置


    // 配置邮件发送服务器
    // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.ym.163.com',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'support@imxcai.com',//你的邮箱名
    'MAIL_FROM' =>'support@imxcai.com',//发件人地址
    'MAIL_FROMNAME'=>'XCAI',//发件人姓名
    'MAIL_PASSWORD' =>'xcai1234',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件


    //URL不区分大小写
    'URL_CASE_INSENSITIVE' => true,
    //启用URL重写模式
    'URL_MODEL' => 2,

    //设置默认访问模块
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin'),
    'DEFAULT_MODULE'       =>    'Home',  // 默认模块



    //启用路由功能
    'URL_ROUTER_ON'=>true,
    'URL_ROUTE_RULES'=>array(
        'u/[:user\s]'=>'User/index',
        't/:id\d'=>'Article/index',
        'n/[:id\d]'=>'Node/index',
    ),

    'VAR_FILTERS'=>'strip_tags',


    'DEFAULT_THEME' => 'new',

);
