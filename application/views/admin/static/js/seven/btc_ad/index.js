$(function () {
    var get_data_url = 'data.json';     // 获取list数据 传
    var get_detail_url = 'data1.json';  // 根据id获取详情
    var chang_state_url = 'data2.json'; // 改变状态 传id和状态值
    var WAIT_PASS_STATE = 1;   // 待审核的状态
    var PASS_STATE = 2;        // 审核通过的状态
    var NO_PASS_STATE = 3;     // 审核不通过的状态

    var waitAjaxTableOptions ={
        url: get_data_url,
        type: 'get',
        data:{state:WAIT_PASS_STATE}, //1待审核 2审核通过 3审核不通过
        dataType: 'json',

        columns: {
            successCode:{
                key: 'iRet',
                val: 0
            },
            data: {
                key:'data',
                val:[
                    {
                        title:'广告id',
                        key: 'id'
                    },
                    {
                        title:'交易类型',
                        key: 'trade_type',
                        filter:tradeType
                    },
                    {
                        title:'支付方式',
                        key: 'pay',
                        filter:  pay // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title:'价格',
                        key: 'price'
                    },
                    {
                        title:'数量',
                        key: 'amount'

                    },
                    {
                        title:'交易日期时间',
                        key: ['start_date','end_date','start_time','end_time'],
                        filter: dateTime
                    },
                    {
                        title:'交易限额',
                        key: ['min_price','max_price'],
                        filter:minMax
                    },
                    {
                        title:'广告发布时间',
                        key:'addtime'
                    },
                    {
                        title:'发布者',
                        key:'username'
                    },
                    {
                        title:'操作',
                        key: 'id',
                        filter: caoZuo// 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    }
                ]
            },
            total: 'total'
        },
        search: {
            show: false,
            key:'keyword',
            val: '' //默认搜索条件
        }, // 是否开启搜索框， 默认开启
        pagination:{
            show: true,
            key:'page', //表示当前请求的是第几页
            val:1
        }, // 是否开启分页，默认开启
        pageSize: {
            show: true, // 是否开启显示数据条数，默认开启
            key:'num', // 表示当前页要显示多少条数据的字段名
            sizeArray:[10,50,100,1000], // 可以选择的显示条数的数组
            selected:10 // 当前选择的显示条数
        },
        loading: true, // 是否开启加载动画，默认开启
        callback: function () { } // 回调函数名，请求成功的callback
    };
    var _wait = $('#tab_1').ajaxTable(waitAjaxTableOptions); //待审核


    // 审核通过
    var passAjaxTableOptions ={
        url: get_data_url,
        type: 'get',
        data:{state:PASS_STATE}, //1待审核 2审核通过 3审核不通过
        dataType: 'json',

        columns: {
            successCode:{
                key: 'iRet',
                val: 0
            },
            data: {
                key:'data',
                val:[
                    {
                        title:'广告id',
                        key: 'id'
                    },
                    {
                        title:'交易类型',
                        key: 'trade_type',
                        filter:tradeType
                    },
                    {
                        title:'支付方式',
                        key: 'pay',
                        filter:  pay // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title:'价格',
                        key: 'price'
                    },
                    {
                        title:'数量',
                        key: 'amount'

                    },
                    {
                        title:'交易日期时间',
                        key: ['start_date','end_date','start_time','end_time'],
                        filter: dateTime
                    },
                    {
                        title:'交易限额',
                        key: ['min_price','max_price'],
                        filter:minMax
                    },
                    {
                        title:'广告发布时间',
                        key:'addtime'
                    },
                    {
                        title:'发布者',
                        key:'username'
                    },
                    {
                        title:'操作',
                        key: 'id',
                        filter: caoZuo// 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    }
                ]
            },
            total: 'total'
        },

        pagination:{
            show: true,
            key:'page', //表示当前请求的是第几页
            val:1
        }, // 是否开启分页，默认开启
        pageSize: {
            show: true, // 是否开启显示数据条数，默认开启
            key:'num', // 表示当前页要显示多少条数据的字段名
            sizeArray:[10,50,100,1000], // 可以选择的显示条数的数组
            selected:10 // 当前选择的显示条数
        },
        loading: true, // 是否开启加载动画，默认开启
        callback: function () { } // 回调函数名，请求成功的callback
    };
    var _pass = $('#tab_2').ajaxTable(passAjaxTableOptions);  // 已通过审核


    // 未通过审核
    var noPassAjaxTableOptions ={
        url: get_data_url,
        type: 'get',
        data:{state:NO_PASS_STATE}, //1待审核 2审核通过 3审核不通过
        dataType: 'json',

        columns: {
            successCode:{
                key: 'iRet',
                val: 0
            },
            data: {
                key:'data',
                val:[
                    {
                        title:'广告id',
                        key: 'id'
                    },
                    {
                        title:'交易类型',
                        key: 'trade_type',
                        filter:tradeType
                    },
                    {
                        title:'支付方式',
                        key: 'pay',
                        filter:  pay // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title:'价格',
                        key: 'price'
                    },
                    {
                        title:'数量',
                        key: 'amount'

                    },
                    {
                        title:'交易日期时间',
                        key: ['start_date','end_date','start_time','end_time'],
                        filter: dateTime
                    },
                    {
                        title:'交易限额',
                        key: ['min_price','max_price'],
                        filter:minMax
                    },
                    {
                        title:'广告发布时间',
                        key:'addtime'
                    },
                    {
                        title:'发布者',
                        key:'username'
                    },
                    {
                        title:'操作',
                        key: 'id',
                        filter: caoZuo// 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    }
                ]
            },
            total: 'total'
        },
        pagination:{
            show: true,
            key:'page', //表示当前请求的是第几页
            val:1
        }, // 是否开启分页，默认开启
        pageSize: {
            show: true, // 是否开启显示数据条数，默认开启
            key:'num', // 表示当前页要显示多少条数据的字段名
            sizeArray:[10,50,100,1000], // 可以选择的显示条数的数组
            selected:10 // 当前选择的显示条数
        },
        loading: true, // 是否开启加载动画，默认开启
        callback: function () { } // 回调函数名，请求成功的callback
    };
    var _no_pass = $('#tab_3').ajaxTable(noPassAjaxTableOptions);  // 未通过审核


    //点击详情操作
    $(document).on('click','.js-detail',function () {
        var detailModal = $('#btcAdDetail');
        var _this = $(this);
        //1根据id去拿详情来数据渲染
        var id = _this.attr('data-id');
        getData(id,get_detail_url);

        // 关闭按钮
        detailModal.find('.js-close').off('click').on('click',function (e) {
            detailModal.fadeOut();
        });

        //2 绑定通过不通过事件
        detailModal.find('.js-action').off('click').on('click',function (e) {
            var action = $(this).attr('data-action');
            if(action === 'pass'){
                MODAL.confirm('<i class="fa fa-check-circle text-green"></i> 确定让id为'+id+'的广告通过？',function () {
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
                MODAL.confirm('<i class="fa  fa-times-circle text-red"></i> 确定不让id为'+id+'的广告通过？',function () {
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


        function getData(id,url) {
            $.ajax({
                url:url,
                type:'get',
                dataType:'json',
                timeout:1000,
                data:{id:id},
                success: function (result) {
                    if(result.iRet == 0){
                        var data = result.data;
                        renderModal(data,'#btcAdDetail');
                    }else {
                        MODAL.tip(result.sMsg || '获取详情数据失败！稍后再试');
                    }
                },
                error: function () {
                    MODAL.tip('获取详情数据失败！稍后再试');
                }
            });


            function renderModal(data,el) {
                var el = $(el);
                var state = data.state;
                if(el){
                    el.find('#id').text(data.id);
                    el.find('#tradeType').html(data.trade_type == '1' ? '<label class="label bg-green">购买</label>' : '<label class="label bg-red-active">出售</label>');
                    el.find('#price').text(data.price);
                    el.find('#amount').text(data.amount);
                    el.find('#pay').text(pay(data.pay));
                    el.find('#minToMax').text(data.min_price+' - '+data.max_price);
                    el.find('#username').text(data.username);
                    el.find('#addTime').text(data.addtime);
                    el.find('#dateAndTime').text(data.start_date+' 至 '+data.end_date+' 每天 '+data.start_time + '到' +data.end_time);
                    el.find('#remark').text(data.remark);
                    if(state != '1'){
                        el.find('.js-daishenhe').addClass('hidden');
                        el.find('.js-yishenhe').html(text(state));

                        function text(state){
                            if(state == PASS_STATE){
                                return '<button type="button" class="btn btn-success js-close" ><i class="fa fa-check"></i>审核已通过</button>';
                            }else if(state == NO_PASS_STATE) {
                                return '<button type="button" class="btn btn-danger js-close" ><i class="fa fa-close"></i>审核不通过</button>';
                            }
                        }

                    }else{
                        el.find('.js-yishenhe').html('');
                        el.find('.js-daishenhe').removeClass('hidden');
                    }
                    el.fadeIn();
                }
            }
        }
        function changeState(url,id,state,cb) {
            $.ajax({
                url:url,
                type:'get',
                dataType:'json',
                timeout:1000,
                data:{state:state,id:id},
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
    });



    // 支付类型1、2、3、4
    function pay(key) {
        if(!key)return;
        key = key+'';
        var type;
        switch (key){
            case  '1':
                type = '支付宝';
                break;
            case '2':
                type = '微信';
                break;

            case '3':
                type = '国内银行转账';
                break;
            case '4':
                type = '现金存款';
                break;
            default:
                type = '-';
                break;
        }
        return type;
    }

    function dateTime(arr) {
        var start_date = arr[0];
        var end_date = arr[1];
        var start_time = arr[2];
        var end_time = arr[3];
        return start_date + ' 至 '+ end_date + ' | '+ start_time + '点到 ' + end_time + ' 点';
    }

    function minMax(arr) {
        return arr[0] + ' - '+ arr[1];
    }

    function caoZuo(key) {
        var dom = '';
        dom +=
            '<button class="btn btn-xs btn-primary js-detail" data-id="' + key + '" >' +
            '<i class="fa fa-eye"></i> 详情</button> ' ;
        return dom;
    }

    function tradeType(type) {
        if(!type) return '-';
        return type == '1' ? '<label class="label bg-red-active">出售</label>' : '<label class="label bg-green-gradient">购买</label>'
    }

});
