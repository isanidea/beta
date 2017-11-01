$(function (e) {
    var get_data_url = 'data.json';
    var get_detail_url = 'user_info.json';
    var chang_state_url = 'user_info.json';
    var WAIT_PASS_STATE = 1;   // 待审核的状态
    var PASS_STATE = 2;        // 审核通过的状态
    var NO_PASS_STATE = 3;     // 审核不通过的状态


    var ajaxTableOptions = {
        url: get_data_url,
        data:{state:WAIT_PASS_STATE},
        type: 'get',
        dataType: 'json',
        columns: { //列对象
            successCode: {
                key: 'iRet',
                val: 0
            },
            data: {
                key: 'data',
                val: [
                    {
                        title: '会员id',
                        key: 'user_id'
                    },
                    {
                        title: '会员姓名',
                        key: 'true_name'
                    },
                    {
                        title: '邮箱',
                        key: 'email'
                    },
                    {
                        title: '手机',
                        key: 'tel'
                    },
                    {
                        title: '国家',
                        key: 'country'
                    },
                    {
                        title: '状态',
                        key: 'state',
                        filter: state
                    },
                    {
                        title: '操作',
                        key: 'user_id',
                        filter: caoZuo
                    }
                ]
            },
            total: 'total'
        },
        search: {
            show: false,
            key: 'keyword',
            val: '' //默认搜索条件
        }, // 是否开启搜索框， 默认开启
        pagination: {
            show: true,
            key: 'page', //表示当前请求的是第几页的字段名
            val: 1
        }, // 是否开启分页，默认开启
        pageSize: {
            show: true,
            key: 'num', // 表示当前页要显示多少条数据的字段名
            sizeArray: [10, 50, 100, 1000], // 可以选择的显示条数的数组
            selected: 10 // 当前选择的显示条数
        }, // 是否开启显示数据条数，默认开启
        loading: true, // 是否开启加载动画，默认开启
        callback: function () {} // 回调函数名，请求成功的callback
    };
    var _wait = $('.ajax-table-container').ajaxTable(ajaxTableOptions);

    _wait.on('click', '.js-detail', function (e) {
        var _this = $(this);
        var id = _this.attr('data-id');
        getData(id, get_detail_url,'#memberDetail'); //获取数据 填到#memberDetail里面并且显示出来

    });

    $('#memberDetail').on('click','.js-close',function (e) {
        $('#memberDetail').fadeOut();
    }).find('.js-action').on('click',function (e) {
        var detailModal = $('#memberDetail');
        var action = $(this).attr('data-action');
        var id = $(this).attr('data-id');
        if(action === 'pass'){
            MODAL.confirm('<i class="fa fa-check-circle text-green"></i> 确定让id为'+id+'的会员通过验证？',function () {
                console.log('通过confirm');
                changeState(chang_state_url,id,PASS_STATE,function () {
                    detailModal.fadeOut();
                    _wait.refresh();
                });
            },function () {
                console.log('通过cancel');
            });
        }
        else if(action === 'nopass'){
            MODAL.confirm('<i class="fa  fa-times-circle text-red"></i> 确定不让id为'+id+'的会员通过验证？',function () {
                console.log('不通过confirm');
                changeState(chang_state_url,id,NO_PASS_STATE,function () {
                    detailModal.fadeOut();
                    _wait.refresh();
                });

            },function () {
                console.log('不通过cancel');
            });
        }
    });

    function changeState(url,id,state,cb) {
        $.ajax({
            url:url,
            type:'get',
            dataType:'json',
            timeout:1000,
            data:{state:state,user_id:id},
            success: function (result) {
                if(result.iRet == 0){
                    cb && cb();
                }else {
                    MODAL.tip(result.sMsg || '操作失败！请稍后再试');
                }
            },
            error: function () {
                MODAL.tip('操作失败！请稍后再试');
            }
        });
    }

    function getData(id, url, renderEl) {
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            timeout: 1000,
            data: {user_id: id},
            success: function (result) {
                if (result.iRet == 0) {
                    var data = result.data;
                    renderModal(data, renderEl);
                } else {
                    MODAL.tip(result.sMsg || '获取详情数据失败！稍后再试');
                }
            },
            error: function () {
                MODAL.tip('获取详情数据失败！稍后再试');
            }
        });

        function renderModal(data, el) {
            var el = $(el);
            var dom = '';
            if (el) {
                dom +=
                    '<div class="form-horizontal">' +

                    '<h4 class="bg-green  pad">3级验证</h4>' +
                    '<div class="row">' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">会员id:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['user_id']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">会员姓名:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['true_name']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">出生日期:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['birthday']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">身份证号:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['id_number']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="row">' +
                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">身份证图片:</label>' +
                    '<div class="col-sm-8">' +
                    '<img class="img-responsive" src="' + xssEscape(data['id_card_pic']) + '" >' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">手持身份证图片:</label>' +
                    '<div class="col-sm-8">' +
                    '<img class=" img-responsive" src="' + xssEscape(data['id_card_pic2']) + '" >' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                el.find('.modal-body').html(dom);
                el.find('.js-action').attr('data-id',xssEscape(data.user_id));
                el.fadeIn();
            }
        }

    }

    function state(state) {
        var state = +state;
        var text = '';
        switch (state) {
            case 0:
                text = '<label class="badge bg-gray">初始值</label>';
                break;
            case 1:
                text = '<label class="badge bg-orange">1级验证</label>';
                break;
            case 2:
                text = '<label class="badge bg-light-blue">2级验证</label>';
                break;
            case 3:
                text = '<label class="badge bg-green">3级验证</label>';
                break;
            case 4:
                text = '<label class="badge bg-red-gradient">邮箱未验证</label>';
                break;
            case 100:
                text = '<label class="badge bg-red">禁止登录</label>';
                break;
            case 1000:
                text = '<label class="badge bg-red">已删除</label>';
                break;
            default:
                break;
        }
        return text;
    }

    function caoZuo(key) {
        var dom = '';
        dom +=
            '<button class="btn btn-xs btn-primary js-detail" data-id="' + key + '" >' +
            '<i class="fa fa-eye"></i> 详情</button> ';
        return dom;
    }

});