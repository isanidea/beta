<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">

    <title><?php echo $title;?></title>
</head>
<body>
<!--[if lt IE 10]>
<div class="promote_bar" id="promote_bar">
    <div class="boardalert">
        <div class="bordalert-text pos-relative">
            <span>重要提示：系统检测到您的浏览器版本太低，为了您的账户安全及操作体验，请升级浏览器，建议您使用谷歌浏览器。</span>
            <a href="http://browsehappy.com/" target="_blank" class="color-red">点击升级</a>
        </div>
    </div>
</div>
<![endif]-->
<div class="wrap">
    <!-- 头部开始 -->
    <div class="hd_nav">
        <div class="hd_navinfo clearfix">
            <div class="hdnav_lt fl">
                <ul>
                    <li class="logowen">
                        <a href="http://trade.test.com">
                            <img src="http://st.test.com/xnb_home/img/logo.png" alt="">
                        </a>
                    </li>
                    <li>
                        <a href="http://trade.test.com">首页</a>
                    </li>
                    <li>
                        <a href="http://trade.test.com/deal/pDealcent">交易中心</a>
                    </li>
<!--                    <li id="localcoins" class="hdnav_localcoins">-->
<!--                        <a href="javascript:;">交易比特币</a>-->
<!--                        <div class="hselectlang" style="display: none;">-->
<!--                            <ul>-->
<!--                                <li>-->
<!--                                    <a href="./trade_bit/index.html#sell">-->
<!--                                        出售比特币-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li>-->
<!--                                    <a href="./trade_bit/index.html#buy">-->
<!--                                        购买比特币-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li>-->
<!--                                    <a href="./trade_bit/create_ad.html">-->
<!--                                        发布交易广告-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li>-->
<!--                                    <a href="./personal/user_orders.html">-->
<!--                                        我的订单-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </div>-->
<!--                    </li>-->
                    <li>
                        <a href="http://trade.test.com/user/pBalance">个人中心</a>
                    </li>
                    <li>
                        <a href="http://trade.test.com/cms/pNotice">系统公告</a>
                    </li>
                    <li>
                        <a href="http://trade.test.com/act/pActive">最新活动</a>
                    </li>
                </ul>
            </div>

            <div class="add_hanavrtbox fr">
                <!-- 已登录状态的头部右侧 -->
                <div class="hdnav_rt fl hdlgnav">
                    <div class="hd_myidbox ">
                        <a href="http://trade.test.com/user/pBalance" class="hd_myid"><?php echo $userinfo['f_email'];?></a>
                        <div class="hidemenu">
                            <ul>
                                <li>
                                    <a href="http://trade.test.com/user/pBalance" class="hdmu_bal">资产中心</a>
                                </li>
                                <li>
                                    <a href="http://trade.test.com/user/pOrder" class="hdmu_ord">成交查询</a>
                                </li>
                                <li>
                                    <a href="http://trade.test.com/user/pAd" class="hdmu_cw">比特币交易管理</a>
                                </li>
                                <li>
                                    <a href="http://trade.test.com/user/pSecurity" class="hdmu_sec">安全中心</a>
                                </li>
                                <li>
                                    <a href="http://trade.test.com/user/pProfile" class="hdmu_ver">实名认证</a>
                                </li>
                                <li style="border:none;">
                                    <a href="http://trade.test.com/user/loginout" class="hdmu_log">退出</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 头部导航结束 -->