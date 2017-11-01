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
                <li>
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
                <li  class="active">
                    <a href="http://trade.test.com/user/pSecurity" class="ucnav_sec"
                       data-i18n="security_cent"></a>
                </li>
                <li>
                    <a href="http://trade.test.com/user/pProfile" class="ucnav_ver"
                       data-i18n="verify"></a>
                </li>
            </ul>
        </div>
        <div class="uc_balance uc_security">
            <!-- 切换链接 -->
            <div class="ucbal_tab clearfix">
                <!-- <div class="ucbal_tabbtn active fl">
                    <a href="javascript:;">双重验证密码</a>
                    <b></b>
                </div> -->
                <div class="ucbal_tabbtn active fl">
                    <a href="javascript:;" data-i18n="modify_login_pwd"></a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;" data-i18n="modify_trade_pwd"></a>
                    <b></b>
                </div>
                <div class="ucbal_tabbtn fl">
                    <a href="javascript:;" data-i18n="forget_trade_pwd"></a>
                    <b></b>
                </div>
            </div>
            <!-- security  main -->
            <!-- <div class="security_box">
                <p class="sec_confirm">您的双重验证密码<strong>未启用</strong>。</p>
                <div class="sec_con">
                    <p class="sec_infop">为了您的账户安全，您可以启用双重验证密码（2FA）。</p>
                    <p class="sec_infop sec_infomar">要使用谷歌双重验证密码，您需要下载“Google Authenticator (身份验证器)”应用程序中，在“Google Authenticator (身份验证器)”中扫描下方的二维码，或者手动添加账户，并输入二维码下面的16位密匙。输入账户登录密码和“Google Authenticator (身份验证器)”中的6位数字，点击“启用2FA”。</p>
                    <p class="sec_infop sec_imgmar">
                        <img src="/img/qrimages">
                    </p>
                    <strong class="sce_str">16位密钥： <span>V6ZCNBTMGJCIPERZ</span></strong>
                    <p class="sec_infop">请备份此16位密钥</p>
                </div>
                <div class="sec_btmfm">
                    <form class="sec_fm" onsubmit="return false;">
                        <div class="secfm_list clearfix">
                            <label>邮箱：</label>
                            <span>2211975234@qq.com</span>
                        </div>
                        <div class="secfm_list clearfix">
                            <label>登录密码：</label>
                            <input type="password" id="pwd">
                        </div>
                        <div class="secfm_list clearfix">
                            <label>双重验证：</label>
                            <input type="text" id="hotp">
                        </div>
                        <div class="reg_messlist clearfix" style="margin-bottom: 10px;">
                            <p class="error_confirm" style="display:none;">asdf</p>
                        </div>
                        <p class="secfm_confirm"> <strong>在启用双重验证密码（2FA）之前，请写下并备份您的16位密钥并保存在安全的地方。</strong> 如果您的手机丢失或者被盗，可以用此密钥来找回您的账户。</p>
                        <div class="sec_agree clearfix">
                            <input type="checkbox" id="backup">
                            <span>我已经备份了密钥</span>
                        </div>
                        <input type="submit" value="启用2FA" class="sec_submit" onclick="createGA(&#39;V6ZCNBTMGJCIPERZ&#39;)">
                    </form>
                </div>
            </div> -->
            <div class="security_box">
                <h2 class="sec_title" data-i18n="modify_login_pwd"></h2>
                <form class="sec_mdfpsw" method="post" id="lg_sec_mdfpsw">
                    <div class="secpsw_list clearfix">
                        <label data-i18n="old_password"></label>
                        <input type="password" name="lgoldpwd" id="lgoldpwd" autofocus="">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="new_password"></label>
                        <input type="password" name="lgpwd" id="lgpwd" maxlength="20">
                    </div>
                    <div class="add_pswstrong clearfix">
                        <div class="pswstreng_box">
                            <span id="weak"></span>
                            <em id="add_wentips" data-i18n="weak"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="medium"></span>
                            <em id="add_wentips" data-i18n="middle"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="tough"></span>
                            <em id="add_wentips" data-i18n="strong"></em>
                        </div>
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="repeat_new_password"></label>
                        <input type="password" name="lgrepwd" id="lgrepwd" maxlength="20">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="email_verification_code"></label>
                        <input type="text" name="lgcode" id="lgcode">
                        <button class="send_verification" id="email_check1"
                                data-i18n="get_email_code"></button>
                    </div>
                    <div class="reg_messlist clearfix">
                        <p class="error_confirm" style="display:none;"></p>
                    </div>
                    <input type="submit" value="确定" class="sec_submit" id="lgsec_pswbtn" data-i18n="value_submit">
                </form>
            </div>
            <div class="security_box" style="display: none">
                <h2 class="sec_title" data-i18n="modify_trade_pwd"></h2>
                <form class="sec_mdfpsw" method="post" id="td_sec_mdfpsw">
                    <div class="secpsw_list clearfix">
                        <label data-i18n="old_password"></label>
                        <input type="password" name="tdoldpwd" id="tdoldpwd" autofocus="">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="new_password"></label>
                        <input type="password" name="tdpwd" id="tdpwd" maxlength="20">
                    </div>
                    <div class="add_pswstrong clearfix">
                        <div class="pswstreng_box">
                            <span id="weak"></span>
                            <em id="add_wentips" data-i18n="weak"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="medium"></span>
                            <em id="add_wentips" data-i18n="middle"></em>
                        </div>
                        <div class="pswstreng_box">
                            <span id="tough"></span>
                            <em id="add_wentips" data-i18n="strong"></em>
                        </div>
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="repeat_new_password"></label>
                        <input type="password" name="tdrepwd" id="tdrepwd" maxlength="20">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="email_verification_code"></label>
                        <input type="text" name="lgcode_2" id="lgcode_2">
                        <button class="send_verification" id="email_check2"
                                data-i18n="get_email_code"></button>
                    </div>
                    <div class="reg_messlist clearfix">
                        <p class="error_confirm" style="display:none;"></p>
                    </div>
                    <input type="submit" value="确定" class="sec_submit" id="sec_pswbtn" data-i18n="value_submit">
                </form>
            </div>
            <div class="security_box" style="display: none">
                <h2 class="sec_title" data-i18n="reset_trade_pwd"></h2>
                <form class="sec_mdfpsw" method="post" id="rs_sec_mdfpsw">
                    <div class="secpsw_list clearfix">
                        <label data-i18n="login_pwd"></label>
                        <input type="password" name="rsginpwd" id="rsginpwd">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="new_pwd"></label>
                        <input type="password" name="rspwd" id="rspwd" maxlength="20">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="repeat_new_pwd"></label>
                        <input type="password" name="rsrepwd" id="rsrepwd" maxlength="20">
                    </div>
                    <div class="secpsw_list clearfix">
                        <label data-i18n="email_verification_code"></label>
                        <input type="text" name="lgcode_3" id="lgcode_3">
                        <button class="send_verification" id="email_check3"
                                data-i18n="get_email_code"></button>
                    </div>
                    <!--                         <div class="secpsw_list clearfix">
                        <label>验证码：</label>
                        <input type="text" class="captcha" name="captcha" id="captcha">
                        <img src="/index/captcha?t=248601" alt="验证码" class="vercode" onclick="$(this).attr('src', '/index/captcha?t='+Math.random())">
                    </div> -->
                    <div class="reg_messlist clearfix">
                        <p class="error_confirm" style="display:none;"></p>
                    </div>
                    <input type="submit" value="确定" class="sec_submit" id="sec_pswbtn" data-i18n="value_submit">
                </form>
            </div>
        </div>
    </div>


</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js"
        data-main="http://st.test.com/xnb_home/js/module/personal/security_main"></script>
</body>

</html>
