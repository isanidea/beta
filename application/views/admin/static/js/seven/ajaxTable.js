;(function ($) {
    $.fn.ajaxTable = function (obj) {
        var obj = obj;
        var _this = $(this);
        var baseDom = createBaseDom(obj);
        _this.html(baseDom); //先生成基本的dom结构，再去请求数据
        ajaxGetData(obj);

        // 绑定分页点击事件
        if(obj.pagination && obj.pagination.show === true){
            _this.find('#pagination').on('click','li',function () {
                if($(this).hasClass('active')) return;
                var _page = $(this).attr('data-page');
                if(_page){
                    //改变当前对象中的当前页
                    obj.pagination.val = _page;
                    //发起ajax请求
                    ajaxGetData(obj);
                }

            });
        }


        // 绑定搜索事件
        if(obj.search && obj.search.show === true){
            _this.find('#searchBtn').on('click',function () {
                //改变当前对象中的关键词
                obj.search.val = xssEscape($.trim(_this.find('#keyword').val()));
                //发起ajax请求
                ajaxGetData(obj);
            });
            //搜索框变化
            _this.find('#keyword').change(function () {
                var val = $(this).val();
                obj.search.val = xssEscape($.trim(val));
            });
        }

        // 绑定切换显示条数事件
        if(obj.pageSize && obj.pageSize.show === true){
            var _page_size = _this.find('#pageSize');
            _page_size.on('change',function () {
                //改变当前对象中的选中的显示条数
                obj.pageSize.selected = xssEscape(_page_size.val());
                //改变当前对象中的起始页
                obj.pagination.val = 1;
                //发起ajax请求
                ajaxGetData(obj);
            });
        }

        // 注册一个refresh方法，返回，以便外部调用
        _this.refresh = function (newobj) {

            var newobj = newobj || obj;
            newobj.pagination.val = 1;
            ajaxGetData(newobj);

        };
        return _this;


        // 创建基础的dom结构
        function createBaseDom(obj) {
            var boxHeader = '';
            boxHeader +=
                '<div class="box-header with-border">' +
                '<h3 class="box-title">' +
                addButton(obj.addButton) +
                '</h3>' +
                addSearch(obj.search) +
                '</div>';

            var boxBody = '';
            boxBody +=
                '<div class="box-body table-responsive">' +
                addLoading(obj.loading) +
                '<table class="table table-hover " id="table">' +
                '<thead  class="bg-light-blue color-palette">' +
                '<tr>' +
                addTheadTh(obj) +
                '</tr>' +
                '</thead>' +
                '<tbody id="tbody">' +
                '</tbody>' +
                '</table>' +
                '</div>';

            var boxFooter = '';
            boxFooter +=
                '<div class="box-footer clearfix">' +
                addPageSize(obj.pageSize) +
                addPagination(obj.pagination) +
                '</div>';

            return '<div class="box">'+ boxHeader + boxBody + boxFooter + '</div>';


            function addButton(addButton) {
                var div = '';
                if (addButton && addButton.show === true) {
                    div +=
                        '<a href="' + addButton.url + '" class="btn btn-primary">' +
                        '<i class="fa fa-plus"></i>' + addButton.text +
                        '</a>';
                }
                return div;
            }

            function addSearch(search) {
                var div = '';
                if (search && search.show === true) {
                    div +=
                        '<div class="pull-right">'+
                        '<div class="input-group input-group-sm" style="max-width: 150px">' +
                        '<input type="text" id="keyword" name="table_search" class="form-control pull-right" maxlength="100" placeholder="搜索">' +
                        '<div class="input-group-btn">' +
                        '<button type="submit" id="searchBtn" class="btn btn-primary"><i class="fa fa-search"></i></button></div> </div> </div>';
                }
                return div;
            }

            function addLoading(loading) {
                var div = '';
                if (loading && loading === true) {
                    div +=
                        '<div class="overlay-wrapper hidden">' +
                        '<div class="overlay">' +
                        '<i class="fa fa-refresh fa-spin"></i>' +
                        '</div>' +
                        '</div>';
                }
                return div;
            }

            function addTheadTh(obj) {
                var th = '';
                var arr = obj.columns.data.val || '';
                if (arr) {
                    arr.forEach(function (item) {
                        th += '<th>' + item['title'] + '</th>';
                    });
                }
                return th;
            }

            function addPagination(pagination) {
                var div = '';
                if (pagination && pagination.show === true) {
                    div += '<ul class="pagination pagination-sm no-margin pull-right" id="pagination"></ul>';
                }
                return div;
            }

            function addPageSize(pageSize) {
                var div = '';
                if (pageSize && pageSize.show === true) {
                    div +=
                        '<div class="col-xs-4" style="padding-left: 0" >' +
                        '<div class="input-group input-group-sm">' +
                        '<span class="input-group-addon bg-primary">每页显示记录</span>' +
                        '<select class="form-control" name="pageSize" id="pageSize" style="max-width: 120px;">';
                    for(var i = 0; i< pageSize.sizeArray.length;i++){
                        div+=  '<option value="'+pageSize.sizeArray[i]+'">'+pageSize.sizeArray[i]+'条</option>' ;
                    }
                    div+=
                        '</select>' +
                        '</div>' +
                        '</div>';
                }
                return div;
            }
        }

        // ajax 获取数据
        function ajaxGetData(obj) {
            var data = obj.data || {};
            data[obj['pagination']['key']] = xssEscape(obj['pagination']['val']);
            data[obj['pageSize']['key']] = xssEscape(obj['pageSize']['selected']);
            if(obj.search && obj.search.show === true){
                data[obj['search']['key']] = xssEscape(obj['search']['val']);
            }

            if(obj.loading && obj.loading === true){
                loadingStart();// 开启loading
            }
            $.ajax({
                url:obj.url,
                data: data,
                dataType: obj.dataType,
                type:obj.type,
                success:function (result) {
                    if (result[obj.columns.successCode.key] == obj.columns.successCode.val) {
                        var data = result[obj.columns.data.key];//数组
                        var tr = createTrDom(data,obj);
                        var total = result[obj.columns.total];
                        var li = createPagination(obj['pagination']['val'], obj['pageSize']['selected'], total);
                        _this.find('#tbody').html(tr);
                        _this.find('#pagination').html(li);
                    } else {
                        _this.find('#tbody').html('<tr ><td colspan="'+obj.columns.data.val.length+'"><p class="text-center text-red">暂无相关信息！</p></td></tr>');
                    }
                    if(obj.loading && obj.loading === true){
                        loadingEnd();// 关闭loading
                    }
                },
                error: function () {
                    alert('地址错误！');
                    if(obj.loading  && obj.loading === true){
                        loadingEnd();// 关闭loading
                    }
                }
            });

            function createTrDom(data,obj) {
                var tr = '';
                var eachData = obj.columns.data.val;
                for (var i = 0; i < data.length; i++) {
                    tr+='<tr>';
                    for(var j = 0 ; j < eachData.length; j++){
                        var eachDataKey = eachData[j]['key'];
                        if(typeof eachData[j]['filter'] === 'function'){
                            var key;
                            if(Array.isArray(eachDataKey)){
                                key = [];
                                for(var x = 0; x < eachDataKey.length; x++){
                                    key.push(data[i][eachDataKey[x]]);
                                }
                            }else {
                                key = data[i][eachDataKey];
                            }
                            var fn = eachData[j]['filter'];
                            var val = fn && fn(key);
                            tr += '<td>'+val+'</td>';
                        }else {
                            tr += '<td>'+data[i][eachDataKey]+'</td>'
                        }
                    }
                    tr+= '</tr>';
                }
                return tr;
            }

            function createPagination(page, pageSize, total) {
                var activePage = +page;
                var prevPage = activePage === 1 ? '' : activePage - 1;
                var nextPage = (pageSize * activePage) >= +total ? '' : activePage + 1;
                var first = 1;
                if(activePage ===1){
                    first = '';
                }

                var last = Math.ceil(+total / +pageSize);
                if(activePage ===last){
                    last = '';
                }
                var li = '<li data-page="'+first+'"><a href="javascript:;">首页</a></li>';

                li += '<li data-page="' + prevPage + '"><a href="javascript:;">&laquo;</a></li>';
                if (prevPage) {
                    if ((prevPage - 1) > 0) {
                        li += '<li  data-page="' + (prevPage - 1) + '"><a href="javascript:;">' + (prevPage - 1) + '</a></li>';
                    }
                    li += '<li  data-page="' + prevPage + '"><a href="javascript:;">' + prevPage + '</a></li>';
                }

                li += '<li class="active"><a href="javascript:;">' + activePage + '</a></li>';

                if (nextPage) {
                    li += '<li data-page="' + nextPage + '"><a href="javascript:;" >' + nextPage + '</a></li>';
                    if (total > pageSize * (activePage + 1)) {
                        li += '<li data-page="' + (nextPage + 1) + '"><a href="javascript:;" >' + (nextPage + 1) + '</a></li>';
                    }
                }
                li += '<li data-page="' + nextPage + '"><a href="javascript:;" >&raquo;</a></li>';
                li += '<li data-page="'+last+'"><a href="javascript:;">尾页</a></li>';
                return li;
            }
        }
        // 转义函数
        function xssEscape(str, reg) {
            if(str){
                str = str+'';
            }
            return str ? str.replace(reg || /[&<">'/](?:(amp|lt|quot|gt|#39|nbsp|#\d+);)?/g, function (a, b) {
                if (b) {
                    return a;
                } else {
                    return {
                        '<': '&lt;',
                        '&': '&amp;',
                        '"': '&quot;',
                        '>': '&gt;',
                        "'": '&#x27;',
                        "/": '&#x2F;'
                    }[a];
                }
            }) : '';
        }
        //开启加载
        function loadingStart() {
            _this.find('.overlay-wrapper').removeClass('hidden');  // 开启加载动画
        }
        //关闭加载
        function loadingEnd() {
            _this.find('.overlay-wrapper').addClass('hidden');  // 关闭加载动画
        }
    }
})(jQuery);