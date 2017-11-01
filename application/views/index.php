<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">

    <title data-i18n="title"></title>
    <!--css-->
    <link href="http://st.test.com/xnb_home/css/style-index.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
    <link href="http://st.test.com/xnb_home/js/lib/owl-carousel2/assets/owl.carousel.min.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/js/lib/owl-carousel2/assets/owl.theme.default.min.css" rel="stylesheet" type="text/css">
    <!--css-->
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
    <!-- 内容块一 -->
    <div class="index_main">
        <div class="banner-container">
            <div class="owl-carousel owl-theme">
                <?php echo $banner_pic_info;?>
            </div>
            <div class="owl-nav-container">
            </div>
        </div>


        <!--<div  id="typer">-->
        <!--<h3><span data-i18n="text1"></span>-->
        <!--<p><span data-i18n="text2"></span></p></h3>-->
        <!--<h3><span data-i18n="text3"></span></h3>-->
        <!--</div>-->

        <div>
            <table class="layui-table coin-table" lay-skin="nob" lay-even="" id="coin_table">
                <colgroup>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th data-i18n="market"></th>
                    <th data-i18n="last_price"></th>
                    <th data-i18n="buy_price"></th>
                    <th data-i18n="sell_price"></th>
                    <th data-i18n="volume"></th>
                    <th data-i18n="turnover"></th>
                    <th data-i18n="24hr_change"></th>
                    <th data-i18n="action"></th>
                </tr>
                </thead>
            </table>
        </div>

        <div class="notice-container">
            <div class="notice-title ">
                <a class="scroll js-notice-scroll" href="<?php echo $cms_title_url?>" target="_blank" >
                    <img class="notice-img" src="http://st.test.com/xnb_home/img/index/notice.png">
                    <?php echo $cms_title?>
                </a>
            </div>
        </div>

        <div class="ind_secttwo">
            <div class="secttwo_con">
                <div class="secttwo_box clearfix">
                    <div class="sectt_info sectt_infofir fl">
                        <img src="http://st.test.com/xnb_home/img/index/profession_icon.png" >
                        <h5 data-i18n="professional"></h5>
                        <p data-i18n="text4"></p>
                    </div>
                    <div class="sectt_info sectt_infosec fl">
                        <img src="http://st.test.com/xnb_home/img/index/instantly_icon.png" >
                        <h5 data-i18n="transact_instantly"></h5>
                        <p data-i18n="text5"></p>
                    </div>
                    <div class="sectt_info sectt_infothr fl">
                        <img src="http://st.test.com/xnb_home/img/index/security_icon.png">
                        <h5 data-i18n="security"></h5>
                        <p data-i18n="text6"></p>
                    </div>
                    <div class="sectt_info sectt_infofou fl">
                        <img src="http://st.test.com/xnb_home/img/index/sync_icon.png" >
                        <h5 data-i18n="synchronization"></h5>
                        <p data-i18n="text7"></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- 操作步骤 -->
        <div class="ind_secstep">
            <div class="ind_title_line"></div>
            <div class="ind_title" data-i18n="operating_procedures"></div>
            <div class="ind_steps">
                <div class="ind_step_item fl">
                    <div class="ind_step_num">1</div>
                    <img src="http://st.test.com/xnb_home/img/index/step1.png" class="ind_step_img ind_step_img1">
                    <div class="ind_step_tip ind_step_tip2" data-i18n="text8"></div>
                </div>
                <div class="ind_step_line fl"></div>
                <div class="ind_step_item fl">
                    <div class="ind_step_num">2</div>
                    <img src="http://st.test.com/xnb_home/img/index/step2.png" class="ind_step_img ind_step_img1">
                    <div class="ind_step_tip ind_step_tip2" data-i18n="text9"></div>
                </div>
                <div class="ind_step_line fl"></div>
                <div class="ind_step_item fl nomargin">
                    <div class="ind_step_num">3</div>
                    <img src="http://st.test.com/xnb_home/img/index/step3.png" class="ind_step_img ind_step_img1">
                    <div class="ind_step_tip ind_step_tip2" data-i18n="text10"></div>
                </div>
            </div>
        </div>

        <div class="ind_help">
            <div class="ind_title_line" style="background-color: #4f6577;"></div>
            <div class="ind_title" data-i18n="help_and_support"></div>

            <div class="ind_help_container clearfix">
                <div class="ind_help_content">
                    <a class="title clearfix" href="http://trade.test.com/cms/pNews"><i data-i18n="news"></i> <span data-i18n="more"></span></a>
                    <ul class="list-container" id="newsContainer">
                        <!--<li class="list clearfix" ><a target="_blank" href="#">期货技巧期货技巧期货技巧期货技巧期货技巧期货技巧期货技巧 </a><span>[16-09-09]</span></li>-->
                    </ul>
                </div>

                <div class="ind_help_content">
                    <a class="title clearfix" href="http://trade.test.com/cms/pNotice"><i data-i18n="notice"></i><span data-i18n="more"></span></a>
                    <ul class="list-container" id="noticeContainer">
                    </ul>
                </div>

                <!--<div class="ind_help_content">-->
                <!--<a class="title clearfix" href="./news/index.html">BTC新闻<span>更多</span></a>-->
                <!--<ul class="list-container" id="newsContainer">-->

                <!--</ul>-->
                <!--</div>-->
            </div>


        </div>

    </div>
    <!-- 风险提示 -->
    <!--     <div style="background: #fffee3;">
        <div class="warning">
            <img src="http://st.test.com/xnb_home/img/index/warning.png">
            <div class="warn-tips" data-i18n="warn_tip"></div>
        </div>
    </div> -->

</div>
<script data-main="http://st.test.com/xnb_home/js/module/index" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>
<!-- 脚部 -->

<!--脚部结束-->
</div>

</body>
</html>