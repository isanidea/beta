<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="全球数字货币在线交易平台，支持比特币交易">
    <title data-i18n="title"></title>

    <link rel="shortcut icon" href="http://st.test.com/xnb_home/img/favicon.ico">
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

    <div class="con_main">
        <div id="login_fm" class="mylogin">
            <div class="login_box clearfix">
                <h2 data-i18n="user_login"></h2>
                <div class="lg_infolist clearfix" style="border-bottom: 1px solid rgb(225, 228, 234);">
                    <span class="lg_sptxt lg_emtxt fl"></span>
                    <input type="text" name="email" class="lg_ipt fl" id="lg_myem" data-i18n="placeholder_email" placeholder="" >
                </div>
                <div class="lg_infolist clearfix" style="border-bottom: 1px solid rgb(225, 228, 234);">
                    <span class="lg_sptxt fl"></span>
                    <input type="password" name="pwd" class="lg_ipt fl" data-i18n="placeholder_password" placeholder="" id="lg_mypsw" maxlength="20">
                </div>
                <div class="lg_infolist clearfix" style="border-bottom: 1px solid rgb(225, 228, 234);">
                    <span class="lg_sptxt lg_yzmtxt fl"></span>
                    <input type="text" name="code" class="lg_ipt fl" data-i18n="placeholder_code" placeholder="" id="ver_code" maxlength="10">
                    <img src="http://trade.test.com/user/get_verify_img" onclick="this.src='http://trade.test.com/user/get_verify_img?'+Date.now();" class="vercode" data-i18n="title_refresh" title="点击刷新">
                </div>
                <a href="http://trade.test.com/user/pForget" class="lg_forgot" data-i18n="forget_password"></a>
                <div class="clearfix"></div>
                <div class="lg_infolist" style="border:none;">
                    <p class="error_confirm lgerror_confirm"></p>
                </div>
                <button class="lg_submit" id="lg_btn" data-i18n="login"></button>
                <a href="http://trade.test.com/user/pRegister" data-i18n="register" class="lg_creat"></a>

                <div class="other-login-container">
                    <div class="facebook icon" id="fbLogin"></div>
                    <div class="twitter icon" id="twitterLogin"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script data-main="http://st.test.com/xnb_home/js/module/user/login.js" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>
</body>
</html>