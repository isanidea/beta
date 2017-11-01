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
    <link href="http://st.test.com/xnb_home/js/lib/layer/skin/default/layer.css" rel="stylesheet" type="text/css">
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
                <li>
                    <a href="http://trade.test.com/user/pBalance" class="ucnav_bal"
                       data-i18n="balances"></a>
                </li>
                <li  class="active">
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
                    <a href="javascript:;" data-i18n="my_orders"></a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;" data-i18n="cancel_orders"></a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;" data-i18n="completed_orders"></a>
                    <b></b>
                </div>
            </div>
            <!-- My Open Orders -->
            <div class="ucord_hisbox">
                <div class="ucord_title clearfix">
                    <span class="fl" data-i18n="my_orders"></span>
                    <!-- <div class="ucordtit_selt fr">
                        <label>币种：</label>
                        <select class="ord_type" id="trade_coin">
                            <option value="all">全部</option>
                            <option value="ltc">LTC</option>
                            <option value="eth">ETH</option>
                            <option value="zec">ZEC</option>
                            <option value="fct">FCT</option>
                            <option value="lsk">LSK</option>
                            <option value="etc">ETC</option>
                            <option value="doge">DOGE</option>
                            <option value="mzc">MZC</option>
                            <option value="hxi">HXI</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt fr">
                        <label>类型：</label>
                        <select class="ord_type" id="trade_flag">
                            <option value="all">全部</option>
                            <option value="buy">买入</option>
                            <option value="sale">卖出</option>
                        </select>
                    </div> -->
                </div>
                <div class="ucord_tablebox">
                    <table class="ordhis_table" id="myentrust">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th data-i18n="order_time"></th>
                            <th data-i18n="type"></th>
                            <th data-i18n="coin"></th>
                            <th data-i18n="price"></th>
                            <th data-i18n="amount"></th>
                            <th data-i18n="deal_amount"></th>
                            <th data-i18n="not_deal"></th>
                            <th data-i18n="status"></th>
                            <th data-i18n="action"></th>
                        </tr>
                        </thead>
                    </table>
                    <ul id="newspage" class="page-wrap"></ul>
                </div>
            </div>
            <div class="ucord_hisbox" style="display: none">
                <div class="ucord_title clearfix">
                    <span class="fl" data-i18n="history_orders"></span>
                    <!-- <div class="ucordtit_selt fr">
                        <label>币种：</label>
                        <select class="ord_type" id="ls_trade_coin">
                            <option value="all">全部</option>
                            <option value="ltc">LTC</option>
                            <option value="eth">ETH</option>
                            <option value="zec">ZEC</option>
                            <option value="fct">FCT</option>
                            <option value="lsk">LSK</option>
                            <option value="etc">ETC</option>
                            <option value="doge">DOGE</option>
                            <option value="mzc">MZC</option>
                            <option value="hxi">HXI</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt fr">
                        <label>类型：</label>
                        <select class="ord_type" id="ls_trade_flag">
                            <option value="all">全部</option>
                            <option value="buy">买入</option>
                            <option value="sale">卖出</option>
                        </select>
                    </div> -->
                </div>
                <div class="ucord_tablebox">
                    <table class="ordhis_table" id="entrust_cancal">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th data-i18n="order_time">下单时间</th>
                            <th data-i18n="type">类型</th>
                            <th data-i18n="coin">币种</th>
                            <th data-i18n="price">价格</th>
                            <th data-i18n="amount">数量</th>
                            <th data-i18n="deal_amount">已成交数量</th>
                            <th data-i18n="not_deal">未成交数量</th>
                            <th data-i18n="status">状态</th>
                            <th data-i18n="action">操作</th>
                        </tr>
                        </thead>
                    </table>
                    <ul id="newspage2" class="page-wrap"></ul>
                </div>
            </div>
            <div class="ucord_hisbox" style="display: none">
                <div class="ucord_title clearfix">
                    <span class="fl" data-i18n="completed_history"></span>
                    <!-- <div class="ucordtit_selt fr">
                        <label>币种：</label>
                        <select class="ord_type" id="history_coin">
                            <option value="all">全部</option>
                            <option value="ltc">LTC</option>
                            <option value="eth">ETH</option>
                            <option value="zec">ZEC</option>
                            <option value="fct">FCT</option>
                            <option value="lsk">LSK</option>
                            <option value="etc">ETC</option>
                            <option value="doge">DOGE</option>
                            <option value="mzc">MZC</option>
                            <option value="hxi">HXI</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt fr">
                        <label>类型：</label>
                        <select class="ord_type" id="history_flag">
                            <option value="all">全部</option>
                            <option value="buy">买入</option>
                            <option value="sale">卖出</option>
                        </select>
                    </div> -->
                </div>
                <div class="ucord_tablebox">
                    <table class="ordhis_table" id="entrust_finish">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th data-i18n="order_time">下单时间</th>
                            <th data-i18n="type">类型</th>
                            <th data-i18n="coin">币种</th>
                            <th data-i18n="price">价格</th>
                            <th data-i18n="amount">数量</th>
                            <th data-i18n="deal_amount">已成交数量</th>
                            <th data-i18n="not_deal">未成交数量</th>
                            <th data-i18n="status">状态</th>
                            <th data-i18n="action">操作</th>
                        </tr>
                        </thead>
                    </table>
                    <ul id="newspage3" class="page-wrap"></ul>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js"
        data-main="http://st.test.com/xnb_home/js/module/personal/orders_main"></script>
</body>
</html>
