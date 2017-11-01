function Modal() {
    var _this = this;
    // 对象对外提供的使用方法
    this.tip = function (str, title) {
        var time = _this._show(str, title, 'tip');
        $('.modal .js-modal-footer-' + time).on('click', '.btn', function (e) {
            _this._hide(time);
        });
    };

    // 对象对外提供使用的方法
    this.confirm = function (content, confirmcallback, cancelcallback) {
        var time;
        if (content && typeof content === 'object') {
            time = _this._show(content.content, content.title);
        } else {
            time = _this._show(content);
        }

        $('.modal .js-modal-footer-' + time).on('click', '.btn', function (e) {
            var _action = $(this).attr('data-action');
            _this._hide(time);
            if (_action === 'confirm' && typeof confirmcallback === 'function') {
                confirmcallback();
            }
            else if (_action === 'cancel' && typeof cancelcallback === 'function') {
                cancelcallback();
            }
        });
    };

    // 对象私有方法
    this._show = function (str, title, tip) {
        var time = _this._createModal(tip || '');
        $('#modalTip-' + time).html(str);
        if (title) {
            $('#title-' + time).html(title);
        }
        return time;
    };

    this._hide = function (time) {
        $('.js-modal-' + time).fadeOut(function () {
            $(this).remove()
        });
    };

    this._createModal = function (type) {
        var time = Date.now();
        var dom = '<div class="modal js-modal-' + time + '" >' +
            '<div class="modal-dialog modal-sm" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h4 id="title-' + time + '"><div class="text-center">提示信息</div></h4>' +
            '</div>' +
            '<div class="modal-body text-center" style="max-height: 450px;overflow:auto"  id="modalTip-' + time + '">' +
            '</div>' +
            '<div class="modal-footer js-modal-footer-' + time + '">';

        if (!type || type !== 'tip') {
            dom += '<button class="btn btn-default" data-action="cancel">取消</button>';
        }

        dom += '<button class="btn btn-primary" data-action="confirm">确定</button></div></div></div></div>';
        $('body').append(dom);
        $('.js-modal-' + time).fadeIn();
        return time;
    };

    return {
        tip: _this.tip,
        confirm: _this.confirm
    }
}
var MODAL = new Modal();
