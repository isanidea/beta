$(function (e) {
    var get_data_list_url = 'data.json'; // 获取列表

    var ajaxTableOptions = {
        url: get_data_list_url, //请求接口
        type: 'get',
        dataType: 'json',
        addButton: { // 跳到其它页面的链接按钮
            show: true,
            url: 'ae.html',
            text: ' 添加公告'
        }, // 添加按钮

        columns: { //列对象
            successCode: {
                key: 'iRet',
                val: 0
            },
            data: {
                key: 'data',
                val: [
                    {
                        title: 'id',
                        key: 'id',
                        filter: ''  // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title: '公告标题',
                        key: 'title',
                        filter: ''
                    },
                    {
                        title: '发布时间',
                        key: 'addtime',
                        filter: ''  // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title: '发布人',
                        key: 'username',
                        filter: ''  // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title: '状态',
                        key: 'state',
                        filter: function (status) {
                            if (status == 1) {
                                return '<label class="label bg-gray">关闭</label>';
                            } else if (status == 0) {
                                return '<label class="label bg-green">正常</label>';
                            }
                        }  // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    },
                    {
                        title: '操作',
                        key: 'id',
                        filter: function (id) {
                            var dom = '';

                            dom +=
                                '<a href="preview.html?id=' + id + '">' +
                                '<button class="btn btn-xs btn-primary js-action" data-id="' + id + '" >' +
                                '<i class="fa fa-eye"></i> 预览</button> </a> ' +
                                '<a href="ae.html?id=' + id + '">' +
                                '<button class="btn btn-xs btn-success js-action" data-id="' + id + '" data-action="edit">' +
                                '<i class="fa fa-edit"></i> 修改 </button> </a> ';
                            return dom;
                        }  // 可以是匿名函数: 如果不为空就执行这个匿名函数，默认参数是key
                    }
                ]
            },
            total: 'total'
        },
        search: {
            show: true,
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
        callback: function () {} // 回调函数，请求成功的callback
    };
    $('.ajax-table-container').ajaxTable(ajaxTableOptions);


});