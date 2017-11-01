<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <title data-i18n="title"></title>
    <link href="http://st.test.com/xnb_home/css/style-trade.css" rel="stylesheet" type="text/css">
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
<div class="wrap" style="background: #eff3f5">
    <!-- 头部开始 -->
    <!-- 头部导航结束 -->
    <div class="uccon_main">
        <div class="uc_topnav">
            <ul class="ucnav_ul clearfix">
                <li class="active">
                    <a href="http://trade.test.com/user/pBalance" class="ucnav_bal"
                       data-i18n="balances"></a>
                </li>
                <li>
                    <a href="http://trade.test.com/user/pOrder" class="ucnav_ord"
                       data-i18n="orders"></a>
                </li>
                <li >
                    <a href="http://trade.test.com/user/pIco" class="ucnav_localcoins"
                       data-i18n="ico_query"></a>
                </li>
                <li>
                    <a href="http://trade.test.com/user/pSecurity" class="ucnav_sec"
                       data-i18n="security_cent"></a>
                </li>
                <li>
                    <a href="http://trade.test.com/user/pProfile" class="ucnav_ver"
                       data-i18n="verify"></a>
                </li>
            </ul>
        </div>
        <div class="uc_balance">
            <!-- 切换链接 -->
            <div class="ucbal_tab clearfix">
                <div class="ucbal_tabbtn active fl">
                    <a href="javascript:;" data-i18n="charge_currency"></a>
                    <b></b>
                </div>
            </div>
            <!-- 勾选查询条 -->
            <div class="ucbal_seach clearfix">
                <!-- <div class="ucbal_checkbox fl">
                    <input type="checkbox" id="hideBalance" rel="0" placeholder="Search">
                    <span>隐藏0余额币种</span>
                </div>
                <input type="text" class="ucbal_searchbtn fr" id="balance_search"> -->
            </div>
            <div class="ucbal_tablebox">
                <table class="ucbal_table" id="balance_table">
                    <colgroup>
                        <col >
                    </colgroup>
                    <thead>
                    <tr>
                        <th data-i18n="name"></th>
                        <th data-i18n="total_balance"></th>
                        <th data-i18n="available"></th>
                        <th data-i18n="frozen"></th>
                        <th data-i18n="btc_value"></th>
                        <th data-i18n="action"></th>
                    </tr>
                    </thead>
                </table>
                <div class="page-wrap" id="newpage"></div>
            </div>
        </div>
    </div>

</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/personal/balance_main"></script>
</body>

</html>
