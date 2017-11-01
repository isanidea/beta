<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>个人中心-比特币交易管理</title>
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
                    <a href="/xnb_home/html/personal/user_balance.html" class="ucnav_bal">资产中心</a>
                </li>
                <li>
                    <a href="/xnb_home/html/personal/user_orders.html" class="ucnav_ord">成交查询</a>
                </li>
                <li class="active">
                    <a href="/xnb_home/html/personal/user_ad.html" class="ucnav_localcoins">比特币交易管理</a>
                </li>
                <li>
                    <a href="/xnb_home/html/personal/user_security.html" class="ucnav_sec">安全中心</a>
                </li>
                <li>
                    <a href="/xnb_home/html/personal/user_profile.html" class="ucnav_ver">实名认证</a>
                </li>
            </ul>
        </div>
        <div class="uc_balance">
            <div class="ucbal_tab clearfix">
                <div class="ucbal_tabbtn active fl">
                    <a href="javascript:;">已发布广告</a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;">未完成订单</a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;">已完成订单</a>
                    <b></b>
                </div>
            </div>
            <div class="ucord_hisbox">
                <form class="ucord_title clearfix" id="form" method="post">
                    <div class="ucordtit_selt lr">
                        <label>状态：</label>
                        <select class="ord_type" id="status" name="status">
                            <option value="">全部</option>
                            <option value="1">开放中</option>
                            <option value="2">关闭中</option>
                            <option value="3">交易中</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lr">
                        <label>类型：</label>
                        <select class="ord_type" id="type" name="type">
                            <option value="">全部</option>
                            <option value="sell">出售</option>
                            <option value="buy">购买</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <label>创建时间：</label>
                        <input class="lc_startDate" type="text" id="startDate1" readonly="">
                        <i></i>
                        <input class="lc_startDate" type="text" id="endDate1" readonly="">
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <input type="submit" name="" value="搜 索">
                    </div>
                </form>
                <div class="ucord_tablebox">
                    <table class="ordhis_table lc_user_table" id="advertisement">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>类型</th>
                            <th>价格/BTC</th>
                            <th>总数量</th>
                            <th>已成交量</th>
                            <th>创建时间</th>
                            <th>状态</th>
                            <th class="todo">操作</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="ucord_hisbox" style="display: none">
                <form class="ucord_title clearfix" id="form" method="post">
                    <div class="ucordtit_selt lc_search_panel lr">
                        <label>订单编号：</label>
                        <input class="input" type="text" name="ordernum" value="">
                    </div>
                    <div class="ucordtit_selt lr">
                        <label>状态：</label>
                        <select class="ord_type" name="status">
                            <option value="">全部</option>
                            <option value="1">未付款</option>
                            <option value="2">待放币</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lr">
                        <label>类型：</label>
                        <select class="ord_type" name="type">
                            <option value="">全部</option>
                            <option value="1">购买</option>
                            <option value="2">出售</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <label>创建时间：</label>
                        <input class="lc_startDate" type="text" id="startDate2" readonly="">
                        <i></i>
                        <input class="lc_startDate" type="text" id="endDate2" readonly="">
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <input type="submit" value="搜 索">
                    </div>
                </form>
                <div class="ucord_tablebox">
                    <table class="ordhis_table lc_user_table" id="unfsorder">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>订单号</th>
                            <th>类型</th>
                            <th>付款方式</th>
                            <th>价格／BTC</th>
                            <th>交易金额</th>
                            <th>交易数量(BTC)</th>
                            <th>交易对方</th>
                            <th>交易状态</th>
                            <th class="todo">操作</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="ucord_hisbox" style="display: none">
                <form class="ucord_title clearfix" id="form" method="post">
                    <div class="ucordtit_selt lc_search_panel lr">
                        <label>订单编号：</label>
                        <input class="input" type="text" name="ordernum" value="">
                    </div>
                    <div class="ucordtit_selt lr">
                        <label>状态：</label>
                        <select class="ord_type" id="status" name="status">
                            <option value="">全部</option>
                            <option value="2">已付款已放币</option>
                            <option value="3">订单取消</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lr">
                        <label>类型：</label>
                        <select class="ord_type" id="type" name="type">
                            <option value="">全部</option>
                            <option value="1">购买</option>
                            <option value="2">出售</option>
                        </select>
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <label>创建时间：</label>
                        <input class="lc_startDate" type="text" id="startDate3" readonly="">
                        <i></i>
                        <input class="lc_startDate" type="text" id="endDate3" readonly="">
                    </div>
                    <div class="ucordtit_selt lc_search_panel lr">
                        <input type="submit" name="" value="搜 索">
                    </div>
                </form>
                <div class="ucord_tablebox">
                    <table class="ordhis_table lc_user_table" id="fsorder">
                        <colgroup>
                            <col>
                        </colgroup>
                        <thead>
                        <th>ID</th>
                        <th>订单号</th>
                        <th>类型</th>
                        <th>付款方式</th>
                        <th>价格／BTC</th>
                        <th>交易金额</th>
                        <th>交易数量(BTC)</th>
                        <th>交易对方</th>
                        <th>交易状态</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js"
        data-main="http://st.test.com/xnb_home/js/module/personal/ad_main"></script>
</body>

</html>