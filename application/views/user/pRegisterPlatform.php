<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">
    <meta name="author" content="">
    <title  data-i18n="title"></title>

    <link rel="shortcut icon" href="http://st.test.com/xnb_home/img/favicon.ico">
    <link href="http://st.test.com/xnb_home/css/style-register.css" rel="stylesheet" type="text/css">
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
    <div class="register_main">
        <!-- 顶部进度 -->
        <!-- 表单信息 -->
        <div class="reg_con">
            <!--第一步：创建账号-->
            <form  method="post" id="register_fm" class="myregister">
                <h2 data-i18n="create_account"></h2>
                <div class="reg_messbox">
                    <div class="reg_messlist clearfix">
                        <label data-i18n="email"></label>
                        <input type="email" name="email"
                               id="email" placeholder=""  data-i18n="placeholder_email">
                    </div>
                    <div class="reg_messlist clearfix">
                        <label data-i18n="password"></label>
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
                            <em data-i18n="medium"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="tough"></span>
                            <em data-i18n="strong"></em>
                        </div>
                    </div>
                    <div class="reg_messlist clearfix">
                        <label  data-i18n="re_password"></label>
                        <input type="password" name="repwd" id="repwd" maxlength="20" placeholder="" data-i18n="placeholder_re_password">
                    </div>
                    <div class="reg_messlist clearfix">
                        <label data-i18n="inviter"></label>
                        <input type="text" name="invite" id="invite" maxlength="80" placeholder="" data-i18n="placeholder_inviter">
                    </div>
                    <div class="reg_messlist clearfix">
                        <label data-i18n="code"></label>
                        <input type="text" class="codeipt" name="captcha" id="captcha" placeholder="" data-i18n="placeholder_code">
                        <img src="http://trade.test.com/user/get_verify_img" onclick="this.src='http://trade.test.com/user/get_verify_img?'+Date.now();"  class="vercode" title="点击刷新">
                    </div>

                    <div class="reg_messlist clearfix">
                        <div class = "reg_agree">
                            <input type="checkbox" id="reg_agck" style="margin: 5px 7px 0 0;float: left;">
                            <span> <i data-i18n="text_terms1"></i><a target="_blank" href="http://trade.test.com/user/pTerms"  data-i18n="text_terms2"></a>.</span>
                        </div>
                    </div>
                    <div class="reg_messlist clearfix" style="margin-bottom: 10px;">
                        <p class="error_confirm" style="display:none;"></p>
                    </div>
                    <div class="reg_messlist clearfix">
                        <input type="button" value="" class="reg_sub not-click" id="reg_btn" data-i18n="value_register">
                    </div>
                </div>
            </form>

            <!--第二步：验证邮箱-->
            <div class="reset_box js-verify"  style="display: none;">
                <h2 data-i18n="verify_email"></h2>
                <div class="reset_info">
                    <img src="http://st.test.com/xnb_home/img/reset_email.png"  class="rest_icon">
                    <span><i data-i18n="verify_text1"></i><br><a href="javascript:;" id="activateBtn"></a></span>
                    <a href="javascript:;" class="reset_tesent" id="resent" style="display: block;margin-left:auto;margin-right: auto " data-i18n="verify_text2"></a>
                    <h5 data-i18n="verify_text3"></h5>
                    <p data-i18n="verify_text4"></p>
                </div>
            </div>
        </div>
    </div>

</div>

<script data-main="http://st.test.com/xnb_home/js/module/user/register_platform.js" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>

</body>
</html>