<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">
    <title data-i18n="title">订单详情</title>

    <link href="http://st.test.com/xnb_home/css/style-localcoins.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">

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

    <!-- 头部导航结束 -->
    <!-- 委托信息-home -->
    <div class="lc_ad_con">
        <div class="lc_common_title" data-i18n="order_info"></div>
        <div class="lc_common_con">
            <ul class="lc_order_info">
                <li>
                    <span class="label" data-i18n="type"></span>
                    <span class="content" id="od_type"></span>
                </li>
                <li class="lc_order_info_price">
                    <span class="label" data-i18n="total_money"></span>
                    <span class="content" id="od_money"></span>
                </li>
                <li>
                    <span class="label" data-i18n="total_amount"></span>
                    <span class="content" id="od_num"></span>
                </li>
                <li>
                    <span class="label" data-i18n="status"></span>
                    <span class="content" id="od_statue"></span>
                </li>
                <li>
                    <span class="label" data-i18n="not_trade_money"></span>
                    <span class="content" id="od_unmoney"></span>
                </li>
                <li>
                    <span class="label" data-i18n="not_trade_amount"></span>
                    <span class="content" id="od_unnum"></span>
                </li>
                <li>
                    <span class="label" data-i18n="create_time"></span>
                    <span class="content" id="od_time"></span>
                </li>
                <li>
                    <span class="label" data-i18n="has_traded_money"></span>
                    <span class="content" id="od_fimoney"></span>
                </li>
                <li>
                    <span class="label" data-i18n="has_traded_amount"></span>
                    <span class="content" id="od_finum"></span>
                </li>
            </ul>
        </div>
        <!-- 委托信息-end -->
        <!-- 成交记录-home -->
        <div class="">
            <div class="ucord_title clearfix"><span class="fl" data-i18n="complete_history"></span></div>
            <div class="">
                <table class="ordhis_table" id="turnover_history">
                    <colgroup>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th data-i18n="th_time"></th>
                        <th data-i18n="th_type"></th>
                        <th data-i18n="th_currency"></th>
                        <th data-i18n="th_price"></th>
                        <th data-i18n="th_complete_amount"></th>
                        <th data-i18n="th_status"></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- 成交记录-end -->
        <!-- 常见问题-home -->
        <div class="lc_common_ques">
            <div class="title" data-i18n="faq">常见问题</div>
            <a class="lc_common_ques_more" target="_blank" href="http://st.test.com/xnb_home/html/problem/index.html" data-i18n="more_problem">更多问题</a>
            <div class="ques_con">
                <ul id="problemList"></ul>
            </div>
        </div>
        <!-- 常见问题-end -->
    </div>
    <!-- 脚部 -->

    <!--脚部结束-->
    <script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/personal/order_detail"></script>
</body>
</html>