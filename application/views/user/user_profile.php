<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <title data-i18n="title">个人中心-实名认证</title>
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
                <li>
                    <a href="http://trade.test.com/user/pSecurity" class="ucnav_sec"
                       data-i18n="security_cent"></a>
                </li>
                <li class="active">
                    <a href="http://trade.test.com/user/pProfile" class="ucnav_ver"
                       data-i18n="verify"></a>
                </li>
            </ul>
        </div>
        <div class="uc_profile">
            <!-- 切换链接 -->
            <div class="ucbal_tab clearfix">
                <div class="ucbal_tabbtn active fl">
                    <a href="http://trade.test.com/user/pProfile" data-i18n="verify"></a>
                    <b></b>
                </div>
            </div>
            <!-- My Profile main -->
            <div class="level_tip" id="level_tip"></div>
            <ul class="pcenter_tabs">
                <li class="active" data-i18n="level_1"></li>
                <li class="" data-i18n="level_2"></li>
                <li class="" data-i18n="level_3"></li>
            </ul>
            <div class="pcenter_main level_1" style="display: block;">
                <form class="ucprofile_fm" method="post" id="one_validate">
                    <div class="pcenter_tip" data-i18n="center_tip"></div>
                    <div class="pcenter_item">
                        <label for="user_email" data-i18n="email"></label>
                        <input type="text" name="" value="" disabled="true" id="user_email">
                        <br>
                    </div>
                    <div class="pcenter_item">
                        <label for="user_pw" data-i18n="pwd"></label>
                        <input type="password" id="user_pw" name="user_pw">
                        <input type="text" id="user_pw_topic" class="dn" disabled="" data-i18n="placeholder_pwd" placeholder="">
                        <button class="dn send_verification" id="up_pw_btn" data-i18n="modify"></button>
                    </div>
                    <div class="pcenter_item">
                        <label for="user_person" data-i18n="confirm_pwd"></label>
                        <input type="password" id="user_person" name="user_person">
                    </div>
                    <div class="pcenter_item">
                        <label for="user_phone" data-i18n="bind_tel"></label>
                        <input type="text" id="user_phone" name="user_phone">
                    </div>
                    <div class="pcenter_item">
                        <label for="check_code" data-i18n="code"></label>
                        <input type="text" id="check_code" name="check_code">
                        <button class="send_verification" id="lg_yzm" data-i18n="get_email_code"></button>
                    </div>
                    <div class="pcenter_sub">
                        <div class="level_agree clearfix">
                            <input type="checkbox" id="agreeBox" name="agreeBox">
                            <span>
                                    <i data-i18n="text1"></i>
                                    <a href="javascript:;" target="_blank" class="js-xieyi" data-i18n="terms"></a>
                                </span>
                        </div>
                        <div class="reg_messlist clearfix">
                            <p class="error_confirm js-ec" style="display:none;"></p>
                        </div>
                        <input type="submit" value="保存提交" class="sec_submit bgcf" id="one_spro_savebtn" disabled="" data-i18n="value_submit">
                    </div>
                </form>
            </div>
            <div class="pcenter_main level_2" style="display: none;">
                <form method="post" id="two_validate">
                    <div class="pcenter_tip" data-i18n="center_tip"></div>
                    <div class="pcenter_item">
                        <label for="user_country" data-i18n="user_country"></label>
                        <select id="user_country"></select>
                    </div>
                    <div class="pcenter_item">
                        <label for="s_province" data-i18n="province"></label>
                        <input id="s_province" name="s_province">
                    </div>
                    <div class="pcenter_item">
                        <label for="s_city" data-i18n="city"></label>
                        <input id="s_city" name="s_city">
                    </div>
                    <div class="pcenter_item">
                        <label for="s_county" data-i18n="s_county"></label>
                        <input id="s_county" name="s_county">
                    </div>
                    <div class="pcenter_item">
                        <label for="addr_detail_id" data-i18n="address_detail"></label>
                        <input type="text" id="addr_detail_id" name="addr_detail_id">
                    </div>
                    <div class="pcenter_sub">
                        <div class="level_agree clearfix">
                            <input type="checkbox" id="agreeBox2" name="agreeBox2">
                            <span > <i data-i18n="text1"></i> <a href="javascript:;" target="_blank" class="js-xieyi" data-i18n="terms"></a></span>
                        </div>
                        <div class="reg_messlist clearfix">
                            <p class="error_confirm js-ec2" style="display:none;"></p>
                        </div>
                        <input type="submit" value="保存提交" class="sec_submit bgcf" id="two_spro_savebtn" disabled="" data-i18n="value_submit">
                    </div>
                </form>
            </div>


            <div class="pcenter_main level_3" style="display: none;">
                <form method="post" id="three_validate">
                    <div class="pcenter_tip" data-i18n="center_tip"></div>
                    <div class="pcenter_reason pcenter_review dn" id="review_id" data-i18n="review_id"></div>
                    <div class="pcenter_item">
                        <label for="user_name" data-i18n="user_name"></label>
                        <input type="text" value="" id="user_name" name="user_name">
                    </div>
                    <div class="pcenter_item">
                        <label for="passport" data-i18n="passport"></label>
                        <input type="text" name="passport" id="passport" value="">
                    </div>
                    <div class="pcenter_item">
                        <label for="photo_upload1" style="vertical-align: top;" data-i18n="passport_photo"></label>
                        <div class="pcenter_upload" id="photo_upload1">
                            <span class="js-upload-text" data-i18n="click_upload"></span>
                            <input type="file" name="photo" id="photo">
                            <input type="hidden" id="photoHidden" value="">
                            <a href="javascript:;" target="_blank" class="photoA" id="photo_show">
                                <img src="" alt="">
                            </a>
                            <div class="upload_bottom" id="repeat_photo" data-i18n="re_upload"></div>
                        </div>
                        <div class="upload_demo">
                            <label data-i18n="example"></label>
                            <div class="demo"></div>
                        </div>
                    </div>
                    <div class="pcenter_item">
                        <label for="photo_upload2" style="vertical-align: top;" data-i18n="photo_upload2"></label>
                        <div class="pcenter_upload" id="photo_upload2">
                            <span class="js-upload-text" data-i18n="click_upload"></span>
                            <input type="file" name="holdphoto" id="holdphoto">
                            <input type="hidden" id="holdphotoHidden" value="">
                            <a href="javascript:;" target="_blank" class="holdphotoA" id="holdphoto_show">
                                <img src="" alt="">
                            </a>
                            <div class="upload_bottom" id="repeat_handphoto" data-i18n="re_upload"></div>
                        </div>
                        <div class="upload_demo">
                            <label></label>
                            <div class="demo handdemo"></div>
                        </div>
                    </div>
                    <div class="upload_rule ">
                        <h4 class="c_orange" data-i18n="photo_requirements"></h4>
                        <p style="color: #666" data-i18n="text2"></p>
                    </div>
                    <div class="pcenter_sub">
                        <div class="level_agree clearfix">
                            <input type="checkbox" id="agreeBox3" name="agreeBox3">
                            <span>
                                    <i data-i18n="text1"></i>
                                    <a href="javascript:;" target="_blank" class="js-xieyi" data-i18n="terms"></a>
                                </span>
                        </div>
                        <div class="reg_messlist clearfix">
                            <p class="error_confirm js-ec3" style="display:none;"></p>
                        </div>
                        <input type="submit" value="保存提交" class="sec_submit bgcf" id="three_spro_savebtn" disabled="" data-i18n="value_submit">
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>
<script src="http://st.test.com/xnb_home/js/lib/requirejs.js" data-main="http://st.test.com/xnb_home/js/module/personal/profile_main"></script>
</body>

</html>