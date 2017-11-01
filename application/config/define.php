<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * fun:页面定义常量
 */

// 页面常量定义
$config['page'] = array(
    'login' => "http://hsgj.taxusglobal.com/member/home",  # 登陆页面链接
    'home' => "http://hsgj.taxusglobal.com/member/home",  # 登陆页面链接
    'err_page' => "http://hsgj.taxusglobal.com/member/err_page",  # 登陆页面链接
    'default' => "http://www.baidu.com", # 登陆页面链接
);

// redis key 前缀定义
$config['redis_pre_key'] = array(
    'IDMAKER_PRE'         => "idType_",            // idType_typeid     类型id为typeid的当前id的值
    'MK'                  => "mk_",                // mk_mk             单个mk的上次请求时间
    'SESSION_KEY_PRE'     => "mid_session_key_",   // mid_session_key_  mid为XXX的session key
    'USER_INFO_PRE'       => "userinfo_",          // userinfo_mid      用户信息
    'MID_TO_UIN'          => "uin_",               // uin_mid           mid对应的uin
    'PHONE_VERIFY_CODE'   => "phone_code_",        // phone_code_1355   手机号码为1355的用户的手机验证码
    'IMG_VERIFY_CODE'     => "img_code_",          // img_code_mk       mk为XXX的用户的图片验证码
    'LEVEL_RULE'          => "level_rule",         // level_rule        规则信息
);

// 错误码
$config['errnum'] = array(
    "USER_PARAM_ERR"           => 0x10000000,      // 用户接口,参数错误
    "USER_MID_PW_CHECK_ERR"    => 0x10000001,      // mid,pw校验失败,密码错误
    "USER_VERIFY_IMG_ERR"      => 0x10000002,      // 图片验证码校验失败
    "USER_PHONE_CODE_ERR"      => 0x10000003,      // 手机验证码校验失败
    "USER_ACTIVE_ALREADY"      => 0x10000004,      // 用户已经激活无需激活
    "USER_MID_EXIST_ALREADY"   => 0x10000005,      // 该用户mid标识已经存在不可用
    "USER_GET_NEW_MID_ERR"     => 0x10000006,      // 获取newmid失败,稍后请重试
    "USER_PHONE_NOT_ONLY"      => 0x10000007,      // 电话号码不唯一
    "USER_IDCARD_NOT_ONLY"     => 0x10000008,      // 身份证不唯一
    "USER_GET_USER_INFO_ERR"   => 0x10000009,      // 获取用户数据失败
    "USER_PHONE_ERR"           => 0x1000000a,      // 手机号码未验证
    "USER_REPASS_ERR"          => 0x1000000b,      // 安全密码校验错误
    "USER_GET_USERINFO_ERR"    => 0x1000000c,      // 获取用户信息失败
    "USER_UPDATE_USERINFO_ERR" => 0x1000000d,      // 更新用户数据失败
    "USER_BIND_PHONE_ERR"      => 0x1000000e,      // 用户绑定手机号码错误
    "USER_NEW_OLD_SAME_ERR"    => 0x1000000f,      // 新老密码不能相同

    "CMS_PARAM_ERR"            => 0x10010000,      // 新闻接口,参数错误
    "CMS_GET_NEWS_LIST_ERR"    => 0x10010001,      // 获取新闻列表信息失败
    "CMS_GET_NEWS_TOTAL_ERR"   => 0x10010002,      // 获取新闻列表总量失败

    "DEAL_PARAM_ERR"           => 0x10020000,      // 订单接口,参数错误
    "DEAL_NOT_ENOUGH_MONEY"    => 0x10020001,      // 订单接口,用户余额不足
    "DEAL_ALREADY_PAYED"       => 0x10020002,      // 订单接口,订单已经支付
    "DEAL_GET_DEALINFO_ERR"    => 0x10020003,      // 订单接口,获取订单信息失败
    "DEAL_GET_USERINFO_ERR"    => 0x10020004,      // 订单接口,获取用户信息失败
    "DEAL_ADD_DEALINFO_ERR"    => 0x10020005,      // 订单接口,创建订单失败
    "DEAL_GET_DEALLIST_ERR"    => 0x10020006,      // 订单接口,获取订单list失败
    "DEAL_GET_DEALLIST_TOTAL_ERR"    => 0x10020007,      // 订单接口,获取订单list总数失败
    "DEAL_OWNER_ERR"                 => 0x10020008,      // 订单接口,该订单不属于该用户,无法查看订单数据
    "DEAL_DEALINFO_MID_ERR"          => 0x10020009,      // 订单接口,订单不属于该用户
    "DEAL_UPDATE_DEALINFO_ERR"       => 0x1002000a,      // 订单接口,更新订单状态失败
    "DEAL_STATE_ERR"                 => 0x1002000b,      // 订单接口,订单状态错误,该订单状态不为待支付订单,不可取消
    "DEAL_PAY_ERR"                   => 0x1002000e,      // 订单接口,订单支付失败

    "GOODS_PARAM_ERR"            => 0x10030000,      // 商品接口,参数错误
    "GOODS_GET_SKU_LIST_ERR"     => 0x10030001,       // 获取商品列表信息失败
    "GOODS_GET_SKU_TOTAL_ERR"    => 0x10030002,       // 获取商品列表总量失败
    "GOODS_GET_DETAIL_ERR"       => 0x10030003,      // 获取商品详情失败

    "FINANCE_PARAM_ERR"                       => 0x10040000,      // 财务接口,参数错误
    "FINANCE_GET_REGMONEY_LIST_ERR"           => 0x10040001,      // 财务接口,获取注册币流水列表失败
    "FINANCE_GET_REGMONEY_TOTAL_ERR"          => 0x10040002,      // 财务接口,获取注册币流水列表总量失败
    "FINANCE_GET_MONEY_LIST_ERR"              => 0x10040003,      // 财务接口,获取奖金流水列表总量失败
    "FINANCE_GET_MONEY_TOTAL_ERR"             => 0x10040004,      // 财务接口,获取奖金流水列表总量失败
    "FINANCE_NOT_ENOUGH_MONEY"                => 0x10040005,      // 财务接口,用余额不足,无法提现
    "FINANCE_ATM_ERR"                         => 0x10040006,      // 财务接口,提现失败
    "FINANCE_MONEY_2_REGMONEY_ERR"            => 0x10040007,      // 财务接口,奖金转注册币失败
    "FINANCE_GET_ATM_LIST_ERR"                => 0x10040008,      // 财务接口,获取提现流水失败
    "FINANCE_GET_ATM_TOTAL_ERR"               => 0x10040008,      // 财务接口,获取提现总额失败
);
?>