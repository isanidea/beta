$(function (e) {
    var add_data_url = 'data.json'; //增加公告接口
    var update_data_url = 'data.json'; //更新公告接口
    var get_data_url = 'data2.json';  // 获取公告接口
    var type = '1001'; //1代表系统公告

    var id = window.location.search || '';
    // 编辑页面
    if (id && id.split('id=')[1] && !isNaN(id.split('id=')[1])) {
        id = id.split('id=')[1];
        // 改变一些dom显示
        $('.js-ae').text('编辑公告');
        $('#submitBtn').text('更新公告');
        $('.js-state').removeClass('hidden');
        // 拉数据填好
        getData(id, get_data_url);

        //更新按钮
        $('#submitBtn').click(function (e) {
            e.preventDefault();
            var title = xssEscape($.trim($('#title').val()));
            var desc = xssEscape($.trim($('#desc').val()));
            var content = xssEscape($.trim($('#content').val()));
            var state = xssEscape($.trim($('#state').val()));
            var id = xssEscape($.trim($('#id').val()));
            if(!title){
                return MODAL.tip('请填写公告标题！');
            }
            if(!desc){
                return MODAL.tip('请填写公告简介！');
            }
            if(!content){
                return MODAL.tip('请填写公告内容！');
            }
            if(state !== '1' && state !== '0'){
                return MODAL.tip('请选择公告状态！');
            }
            if(!id){
                return MODAL.tip('没有更新的id！');
            }
            loadingStart();
            $.ajax({
                url: update_data_url,
                type:'post',
                dataType:'json',
                data:{'title':title,'desc':desc,'content':content,'state':state,'id':id},
                success:function (result) {
                    if(result.iRet == 0){
                        MODAL.confirm('更新成功！',function () {
                            window.location.href = './index.html';
                        });

                    }else {
                        MODAL.tip(result.sMsg || '更新失败！请稍后再试。');
                    }
                    loadingEnd();
                },
                error:function () {
                    MODAL.tip('系统错误！');
                    loadingEnd();
                }
            });

        });

    }else {
        // 添加页面
        $('#content').wysihtml5();

        $('#submitBtn').click(function (e) {
            e.preventDefault();
            var title = xssEscape($.trim($('#title').val()));
            var desc = xssEscape($.trim($('#desc').val()));
            var content = xssEscape($.trim($('#content').val()));
            if(!title){
                return MODAL.tip('请填写公告标题！');
            }
            if(!desc){
                return MODAL.tip('请填写公告简介！');
            }
            if(!content){
                return MODAL.tip('请填写公告内容！');
            }
            loadingStart();
            $.ajax({
                url: add_data_url,
                type:'post',
                dataType:'json',
                data:{'title':title,'desc':desc,'content':content,'typeId':type},
                success:function (result) {
                    if(result.iRet == 0){
                        MODAL.confirm('发布成功！',function () {
                            window.location.href = './index.html';
                        });

                    }else {
                        MODAL.tip(result.sMsg || '发布失败！请稍后再试。');
                    }
                    loadingEnd();
                },
                error:function () {
                    MODAL.tip('系统错误！');
                    loadingEnd();
                }
            });

        });
    }

    function getData(id,url) {
        loadingStart();
        $.ajax({
            url:url,
            type:'get',
            dataType:'json',
            data:{id:id},
            success: function (result) {
                if(result.iRet == 0){
                    var data = result.data;
                    $('#title').val(xssEscape(data.title));
                    $('#desc').val(xssEscape(data.desc));
                    $('#content').val(data.content).wysihtml5();
                    $('#state').val(xssEscape(data.state));
                    $('#id').val(xssEscape(data.id));
                }else {
                    MODAL.tip(result.sMsg || '拉数据失败！');
                }
                loadingEnd();
            },
            error: function () {
                MODAL.tip('系统错误！拉数据失败');
                loadingEnd();
            }
        });
    }

});