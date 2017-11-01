/**
 * Created by Administrator on 2017\7\7 0007.
 * 公用方法
 */

var comMethod = ( function() {

     //获取订单id
     function urlarg(){
        var s =  window.location.search.split('=')[1];
        return s;
    }
     //生成弹出
     function dialogPop(title, con, param, formfield) {
         console.log(param);
         var nodeCon = '<div class="modal fade"  id="approve" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">'+
                            '<div class="modal-dialog">'+
                                '<div class="modal-content">'+
                                    '<form id="pop_form" action="">'+
                                        '<div class="modal-header">'+
                                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                                                '<span aria-hidden="true">×</span>'+
                                            '</button>'+
                                             '<h4 class="modal-title" id="approvetitle">'+ title +'</h4>'+
                                        '</div>'+
                                        '<div class="modal-body">'+
                                            '<div class="box-body" id="approvecon">'+ con +'</div>'+
                                        '</div>'+
                                        '<div class="modal-footer text-right">'+
                                            '<input type="submit" class="btn btn-default" id="changebtn" value="确认">'+
                                            '<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>'+
                                        '</div>'+
                                    '</form>'+
                                '</div>'+
                            '</div>'+
                       '</div>';
         $('body').append(nodeCon);
         $('#approve').modal({
             keyboard: false,
             backdrop: "static"
         });
         $('#approve').on('hidden.bs.modal', function () {
             $(this).remove();
         });
         if(formfield){
             var inputfield = formfield['inputfield'];
             if(inputfield && inputfield.length > 0){
                 var rul = {};
                 var mes = {};
                 $.each(inputfield, function () {
                     var self = this;
                     rul[self.value] = {};
                     mes[self.value] = {};
                     $.each(this.check, function () {
                         $.map(this, function (i, n) {
                             rul[self.value][n] = true;
                             if(i) mes[self.value][n] = i;
                         })
                     })
                 })
                 formValidata(param.data, inputfield, rul,mes);
             }
         }else{
             $("#changebtn").on('click', function () {
                 ajaxpack(param.url, param.type, param.data, param.fun);
             })
         }
     }
     
     function ajaxpack(url, type, param, callback) {
         $.ajax({
              type: type,
              url: url,
              data: param,
              dataType: 'json',
              success: function(data){
                  callback(data);
              },
              error: function(){
                 console.log("审核失败");
              }
         });
     }

     function formValidata(param, formfield, rul, mes) {
         var checkparam = {
             focusInvalid: false, //当为false时，验证无效时，没有焦点响应
             onkeyup: false,
             submitHandler: function(form){   //表单提交句柄,为一回调函数，带一个参数：form
                 $.each(formfield, function () {
                     param[this.name] = $('#' + this.value).val();
                 });
                 console.log(param);
             },
             rules:{},
             messages:{}
         }
         checkparam['rules'] = rul;
         checkparam['messages'] = mes;
         var validate = $("#pop_form").validate(checkparam);
     }

     function moneySplit(num) {
         if(num && num != null){
             var source = num.replace(/,/g,'').split('.');
             source[0] = source[0].replace(new RegExp('(\\d)(?=(\\d{3})+$)','ig'),"$1,");
             return source.join(".");
         }else{
             return num;
         }
     }

    //获取参数的方法 name需要获取的字段
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    }

     return {
         urlarg: urlarg,
         dialogPop: function(title, con, param, formfield){
             return new dialogPop(title, con, param, formfield)
         },
         ajaxpack: function (url, type, param, callback) {
             ajaxpack(url, type, param, callback);
         },
         moneySplit: moneySplit,
         getQueryString: getQueryString
     }
} )();
