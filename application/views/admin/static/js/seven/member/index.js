$(function (e) {
    var get_data_url = 'data.json';
    var get_detail_url = 'user_info.json';

    var ajaxTableOptions = {
        url: get_data_url,
        type: 'get',
        dataType: 'json',
        addButton: { // 跳到其它页面的链接按钮
            show: false,
            url: 'add.html',
            text: ' 添加问题'
        }, // 添加按钮

        columns: { //列对象
            successCode: {
                key: 'iRet',
                val: 0
            },
            data: {
                key: 'data',
                val: [
                    // {
                    //     title: '<label style="display: block;margin-bottom: 0" for="checkbox"> <input type="checkbox" name="all" id="checkbox"></label>',
                    //     key: 'user_id',
                    //     filter: function (key) {
                    //         return '<label for="checkbox"> <input type="checkbox" data-id="' + key + '" ></label>';
                    //     }
                    // },
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
        callback: function () {
        } // 回调函数名，请求成功的callback
    };
    var a = $('.ajax-table-container').ajaxTable(ajaxTableOptions);


    a.on('click', '.js-detail', function (e) {
        var _this = $(this);
        var id = _this.attr('data-id');
        getData(id, get_detail_url, '#memberDetail'); //获取数据 填到#memberDetail里面并且显示出来
    });

    $('.js-search').on('click', function (e) {
        e.preventDefault();
        var arr = $('.js-form').serializeArray();
        var options = ajaxTableOptions;
        options.data = {};
        arr.forEach(function (val){
            options.data[val.name] = xssEscape(val.value);
        });
        console.log(options.data);
        a.refresh(options);
    });

    $('#memberDetail').on('click', '.js-close', function (e) {
        $('#memberDetail').fadeOut();
    });


    function getData(id, url, renderEl) {
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            timeout: 1000,
            data: {user_id: id},
            success: function (result) {
                console.log({user_id: id});
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
                    '<h4 class="bg-orange pad">1级验证</h4>' +
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
                    '<label class="col-sm-4 control-label">邮箱:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['email']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">手机:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['tel']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +


                    '<h4 class="bg-blue pad">2级验证</h4>' +
                    '<div class="row">' +
                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">国家:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['country']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">省:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['province']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">市:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['city']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">区:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['area']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">地址:</label>' +
                    '<div class="col-sm-8">' +
                    '<textarea class="form-control" disabled>' + xssEscape(data['street']) + '</textarea>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="col-sm-6">' +
                    '<div class="form-group ">' +
                    '<label class="col-sm-4 control-label">邮政编码:</label>' +
                    '<div class="col-sm-8">' +
                    '<input type="text" class="form-control" disabled value="' + xssEscape(data['postcode']) + '">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +


                    '<h4 class="bg-green  pad">3级验证</h4>' +
                    '<div class="row">' +
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