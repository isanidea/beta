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
                    <a href="http://trade.test.com/user/pBalance" data-i18n="charge_currency"></a>
                    <b></b>
                </div>
            </div>
            <div class="ucbal_tablebox ucbal_coinin">
                <h2 class="ucbal_cointit"><span id="tb_coin_name"></span> <i data-i18n="withdrawals"></i> <span>( <i data-i18n="withdrawals_num"></i><span id="tb_coin_num"></span> )</span></h2>
                <div class="coinin_con">
                    <form class="coinout_fm" method="post" onsubmit="return false;" id="submit_coinout" >
                        <div class="coinout_list clearfix">
                            <label data-i18n="withdrawals_address"></label>
                            <input type="text" id="address" name="address">
                        </div>
                        <div class="coinout_list clearfix">
                            <label data-i18n="withdrawals_amount"></label>
                            <input type="text" name="num" style="display: none";>
                            <input type="text" id="num" name="num" class="num_select">
                            <label data-topic="coinout_detail" class="dn" id="coinout_detail"></label>
                        </div>
                        <div class="coinout_list clearfix">
                            <label data-i18n="trade_password"></label>
                            <input type="password" id="tpwd" name="tpwd">
                        </div>
                        <div class="coinout_list clearfix">
                            <label data-i18n="net_fee"></label>
                            <input type="text" id="wl_cost" name="wl_cost" disabled="">
                        </div>
                        <div class="reg_messlist clearfix">
                            <p class="error_confirm" style="display:none;"></p>
                        </div>
                        <input type="submit" value="确定" class="coinout_sub" data-i18n="value_submit">
                    </form>
                </div>
                <div class="coinout_notices">
                    <h3 data-i18n="notice_text1"></h3>
                    <p style="color: #f00;" data-i18n="notice_text2"></p>
                    <p data-limit="notice_text3"></p>
                    <p data-fee="notice_text4"></p>
                    <!-- <p data-i18n="notice_text5"></p> -->
                    <p data-than="notice_text6"></p>
                </div>
            </div>
        </div>
        <!-- Withdrawal History -->
        <div class="ucbal_historybox">
            <div class="ucbal_histitle clearfix">
                <span class="deposit_til fl" data-i18n="withdraw_history"></span>
                <!--                     <a href="/html/personal/user_balance.html/coinoutcsv/name/btc" class="ucbalhis_checkout fr" onclick="return check(&#39;btc&#39;);">导出记录</a> -->
            </div>
            <div class="ucbal_tablebox" style="min-height: 200px;">
                <table class="ucbalhis_table" id='coinout_table'>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-i18n="address"></th>
                        <th data-i18n="amount"></th>
                        <th data-i18n="time"></th>
                        <th data-i18n="status"></th>
                        <th data-i18n="action"></th>
                    </tr>
                    </thead>
                </table>
                <ul id="newspage" class="page-wrap"></ul>
            </div>
        </div>
    </div>

</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/personal/coinout_main"></script>
</body>

</html>
