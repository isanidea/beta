<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="CoinComing">
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
                    <a href="http://trade.test.com/user/pBalance"
                       data-i18n="charge_currency"></a>
                    <b></b>
                </div>
            </div>
            <div class="ucbal_tablebox ucbal_coinin" style="min-height: 200px;">
                <h2 class="ucbal_cointit" data-i18n="BTC_charge"></h2>
                <div class="coinin_con">
                    <div class="coinin_left">
                        <p class="coinin_address"></p>
                    </div>
                    <div class="coinin_right mt30" id="qrcode"></div>
                    <span class="coinin_span desc" data-i18n="desc"></span>
                </div>
            </div>
        </div>
        <!-- Deposit History -->
        <div class="ucbal_historybox">
            <div class="ucbal_histitle clearfix">
                <span class="deposit_til fl" data-i18n="charge_recording"></span>
                <!--  <a href="/html/personal/user_balance.html/coinincsv/name/btc" class="ucbalhis_checkout fr" onclick="return check(&#39;btc&#39;);">导出记录</a> -->
            </div>
            <div class="ucbal_tablebox" style="min-height: 200px;">
                <table class="ucbalhis_table" id="coin_in">
                    <colgroup>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-i18n="address"></th>
                        <th data-i18n="amount"></th>
                        <th data-i18n="time"></th>
                        <th data-i18n="status"></th>
                    </tr>
                    </thead>
                </table>
                <ul id="newspage" class="page-wrap"></ul>
            </div>
        </div>
    </div>


</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js"
        data-main="http://st.test.com/xnb_home/js/module/personal/coinin_main"></script>
</body>

</html>
