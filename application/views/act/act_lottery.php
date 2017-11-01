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
    <link href="http://st.test.com/xnb_home/css/style-active.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
</head>

<body>
<div class="wrap">
    <div class="lottery_content">
        <div class="alert_box"></div>
        <div style="height:140px;"></div>
        <div class="draw">
            <div class="turntable-bg">
                <div class="pointer"><img src="http://st.test.com/xnb_home/img/active/3.png" alt="pointer"></div>
                <div class="rotate"><img id="rotate" src="http://st.test.com/xnb_home/img/active/4.png" alt="turntable"></div>
            </div>
            <p class="cj-test1 js-text1 dn" data-i18n="text1"></p>
            <p class="cj-test2 js-text2 dn">
                <i data-i18n="text2"></i>
                <span id="lottery_num"></span>
                <i data-i18n="text3"></i>
            </p>
            <p class="cj-test3 dn js-text4" data-i18n="text6"></p>
            <p class="cj-test1 dn js-text3">
                <i data-i18n="text5"></i>
            </p>
            <p class="cj-test4 js-text7 dn" data-i18n="text7"></p>
        </div>
        <div class="index_lotterty">
            <h2 data-i18n="record"></h2>
            <table id="draw_history" class="layui-table lottery-tbale" lay-skin="nob" width="900">
                <colgroup>
                    <col width="30">
                    <col width="100">
                    <col width="100">
                </colgroup>
                <thead>
                <tr class="tr_lottery" align="center">
                    <th data-i18n="sequence"></th>
                    <th data-i18n="email"></th>
                    <th data-i18n="awards"> </th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="" style="width:900px; margin:0 auto; margin-top:100px;">
            <p style="text-align:center;font-size:16px;color:#fff;" data-i18n="text4"></p>
        </div>
    </div>
</div>

<script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/active/active"></script>
</body>

</html>