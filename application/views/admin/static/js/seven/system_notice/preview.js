$(function () {
    var get_system_notice_detail_url = 'data2.json'; //获取公告详情信息

    loadingStart();
    //根据id发ajax请求数据
    $.ajax({
        url: get_system_notice_detail_url,
        type:'get',
        dataType:'json',
        data:{id:id}, //id在head 标签的redirect.js声明了
        success: function (result) {
            if(result.iRet == 0){
                $('#title').text(result.data.title);
                $('#username').text(result.data.username);
                $('#time').text(result.data.addtime);
                $('#content').html(result.data.content);
            }else{
                MODAL.tip(result.sMsg || '加载相关信息失败！');
            }
            loadingEnd();
        },
        error: function () {
            loadingEnd();
            return MODAL.tip('系统错误！请稍后再试。');
        }

    });
});