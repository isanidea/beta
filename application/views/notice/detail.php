<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="description" content="是一个全球数字货币在线交易平台，支持比特币交易">
    <title data-i18n="notice_detail_title"></title>

    <link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
    <link href="http://st.test.com/xnb_home/css/style-noctice.css" rel="stylesheet" type="text/css">
    <link href="http://st.test.com/xnb_home/css/ql-editor.css" rel="stylesheet" type="text/css">

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

    <!-- 新闻列表主内容 -->
    <div class="news_top">
        <div class="newstop_wen">
            <h2 data-i18n="notice_center"></h2>
            <p data-i18n="notice_text1"></p>
            <p data-i18n="notice_text2"></p>
        </div>
    </div>
    <div class="post_content clearfix ql-snow" >
        <h2 id="title"><?php echo $title; ?></h2>
        <div class="postdetail_date clearfix">
            <span id="time"><?php echo $addtime; ?></span>
        </div>
        <div class="postdetail_desc ql-editor" id="content">
            <?php echo $content; ?>
        </div>

        <div class="share-container">
        </div>
    </div>

    <!-- 脚部 -->
    <!--脚部结束-->
</div>

<script data-main="http://st.test.com/xnb_home/js/module/notice/detail" src="http://st.test.com/xnb_home/js/lib/requirejs.js"></script>

</body>
</html>