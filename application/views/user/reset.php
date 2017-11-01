<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="全球数字货币在线交易平台，支持比特币交易">
    <title data-i18n="title"></title>

    <link rel="shortcut icon" href="http://st.test.com/xnb_home/img/favicon.ico">
    <link href="http://st.test.com/xnb_home/html/../css/style-register.css" rel="stylesheet" type="text/css">

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

    <div class="register_main">
        <!-- 表单信息 -->
        <div class="reg_con">

            <form onsubmit="return false;" class="myregister">
                <h2 data-i18n="reset"></h2>
                <div class="reg_messbox">
                    <div class="reg_messlist clearfix">
                        <label data-i18n="email"></label>
                        <input type="text" id="email" disabled>
                    </div>
                    <div class="reg_messlist clearfix">
                        <label data-i18n="new_password"></label>
                        <input type="password" name="pwd"
                               id="pwd" maxlength="20" placeholder="" data-i18n="placeholder_password">
                    </div>
                    <div class="add_pswstrong clearfix" style="display: none;">
                        <div class="pswstreng_box">
                            <span id="weak" class="highlight_span"></span>
                            <em data-i18n="weak"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="medium"></span>
                            <em data-i18n="middle"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="tough"></span>
                            <em data-i18n="strong"></em>
                        </div>
                    </div>
                    <div class="reg_messlist clearfix">
                        <label data-i18n="re_password"></label>
                        <input type="password" name="repwd" id="repwd" maxlength="20" placeholder="" data-i18n="placeholder_re_password">
                    </div>

                    <div class="reg_messlist clearfix">
                        <label data-i18n="code"></label>
                        <input type="text" class="codeipt" name="captcha" id="captcha" placeholder="" data-i18n="placeholder_code">
                        <img src="http://trade.test.com/user/get_verify_img" onclick="this.src='http://trade.test.com/user/get_verify_img?'+Date.now();"  class="vercode" title="点击刷新">
                    </div>

                    <div class="reg_messlist clearfix" style="margin-bottom: 10px;">
                        <p class="error_confirm" style="display:none;"></p>
                    </div>
                    <div class="reg_messlist clearfix">
                        <input type="button" value="" class="reg_sub" id="resetpwd_btn" data-i18n="value_submit">
                    </div>
                </div>
            </form>

        </div>
    </div>
    <!-- 脚部 -->

    <!--脚部结束-->
</div>
<script data-main="http://st.test.com/xnb_home/js/module/user/resetpwd.js" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>

</body>
</html>