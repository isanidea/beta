<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">

    <title data-i18n="title"></title>
    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
    <link href="http://st.test.com/xnb_home/css/style-login.css" rel="stylesheet" type="text/css">

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

    <div class="con_main">
        <form id="login_fm" class="mylogin" onsubmit="return false;">
            <div class="login_box clearfix">
                <h2 data-i18n="forget"></h2>
                <div class="lg_infolist clearfix" style="border-bottom: 1px solid rgb(225, 228, 234);">
                    <span class="lg_sptxt lg_emtxt fl"></span>
                    <input type="email" name="email" class="lg_ipt fl" id="lg_myem" placeholder="" data-i18n="placeholder_email"
                           autofocus="on">
                </div>
                <div class="lg_infolist clearfix" style="border-bottom: 1px solid rgb(225, 228, 234);">
                    <span class="lg_sptxt lg_yzmtxt fl"></span>
                    <input type="text" name="captcha" class="lg_ipt fl" data-i18n="placeholder_code" placeholder="" id="ver_code">
                    <!--<button type="button" data-i18n="get_code" class="vercode" disabled="true">获取验证码</button>-->
                    <img src="http://trade.test.com/user/get_verify_img" onclick="this.src='http://trade.test.com/user/get_verify_img?'+Date.now();"  class="vercode" title="点击刷新">
                </div>
                <div class="clearfix"></div>
                <div class="lg_infolist" style="border:none;">
                    <p class="error_confirm lgerror_confirm"></p>
                </div>
                <button  class="lg_submit" id="lg_btn" data-i18n="submit"></button>
            </div>
        </form>
    </div>
    <!-- 脚部 -->

    <!--脚部结束-->
</div>


<script data-main="http://st.test.com/xnb_home/js/module/user/forget.js" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>

</body>
</html>