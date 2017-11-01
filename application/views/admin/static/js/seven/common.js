/**
 * Created by admin on 2017/6/15.
 */
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
            }[a]
        }
    }) : '';
}

function loadingStart() {
    $('.overlay-wrapper').removeClass('hidden');  // 开启加载动画
}

function loadingEnd() {
    $('.overlay-wrapper').addClass('hidden');  // 关闭加载动画
}