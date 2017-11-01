<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>login</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon shortcut" href="http://st.test.com/xnb_home/img/favicon.ico">
    <meta name="viewport"
          content="width=device-width, initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
    <link href="http://st.test.com/vue_xnb_admin/login/css/reset.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d1a44;
        }

        .login-wrapper {
            position: absolute;
            padding: 50px;
            top: 50%;
            left: 50%;
            border-radius: 200px;
            transform: translate3d(-50%, -50%, 0);
            background-image: linear-gradient(180deg, #0d1a44 13%, #3c4f91 56%, #5fc1e4);
            color: #fff;
            opacity: .5;
            transition: all 500ms;
        }

        .login-wrapper:hover {
            opacity: 1;
            background-image: linear-gradient(180deg, #f55a44 13%, #3c4f91 56%, #eee);
        }

        .login-wrapper .title {
            font-size: 26px;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            width: 300px;
            margin: 20px auto;
        }

        .form-group .form-group-input {
            display: block;
            width: 100%;
            height: 40px;
            padding: 8px 15px;
            line-height: 24px;
            font-size: 14px;
            background-color: #fff;
            border-radius: 20px;
            color: #666;
            outline: none;
            border: none;
        }

        .form-group.form-group-code {
            position: relative;
            padding-right: 100px;
        }

        .form-group-code .code {
            position: absolute;
            cursor: pointer;
            display: block;
            width: 90px;
            height: 40px;
            right: 0;
            top: 0;
        }

        .form-group .form-group-input.submit {
            background-color: #fb371b;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 5px 2px;
            transition: all 500ms;
        }

        .form-group .form-group-input.submit:hover {
            background-color: #e26756;
        }

        .form-group .form-group-input.submit:active {
            box-shadow: 0 0 0;
        }
    </style>
</head>
<body>
<div id="app">
    <div class="login-wrapper">
        <h1 class="title"> CoinComing Admin</h1>
        <div class="form-group">
            <input type="text" v-model="name" class="form-group-input username" placeholder="username"
                   maxlength="30">
        </div>
        <div class="form-group">
            <input type="password" v-model="password" class="form-group-input password"
                   placeholder="password" maxlength="30">
        </div>
        <div class="form-group form-group-code">
            <input type="text" v-model="img_code" class="form-group-input" placeholder="verification code"
                   maxlength="6">
            <img class="code" :src="img_code_src" @click="refreshImgCode">
        </div>
        <div class="form-group">
            <input type="button" class="form-group-input submit" value="submit" @click="submit">
        </div>
    </div>
</div>

<script src="http://st.test.com/vue_xnb_admin/login/js/vue.min.js"></script>
<script src="http://st.test.com/vue_xnb_admin/login/js/vue-resource.min.js"></script>
<script src="http://st.test.com/vue_xnb_admin/login/js/md5.min.js"></script>
<script>
    /* eslint-disable no-new */
    new Vue({
        el: '#app',
        data: function () {
            return {
                name: '',
                password: '',
                img_code: '',
                default_src: 'http://trade.test.com/user/get_verify_img',
                img_code_src: 'http://trade.test.com/user/get_verify_img',
                ADMIN_INDEX: 'http://trade.test.com/admin/admin_page/home'
            }
        },
        methods: {
            submit: function () {
                var validate = this._checkForm()
                if (!validate) {
                    return false
                }
                var requestdata = {
                    params: {
                        name: this.name,
                        password: md5(this.password),
                        img_code: this.img_code.toLowerCase()
                    }
                }
                var url = 'http://trade.test.com/admin/api/admin_login'
                this.$http.jsonp(url, requestdata).then(function (result) {
                    var res = result.body
                    var iRet = res.iRet
                    if (iRet !== 0) {
                        alert(res.sMsg || this.errorInfo(iRet))
                        this.img_code = ''
                        this.refreshImgCode()
                    } else {
                        window.location.href = this.ADMIN_INDEX
                    }
                }, function (err) {
                    console.log(err)
                })
            },
            refreshImgCode: function () {
                this.img_code_src = this.default_src + '?' + Date.now()
            },
            _checkForm: function () {
                var name = this.name
                var password = this.password
                var code = this.img_code
                if (name.length < 2) {
                    alert('请输入正确的用户名')
                    return false
                }
                if (password.length < 6) {
                    alert('请输入至少6位数的密码')
                    return false
                }
                if (code.length !== 4) {
                    alert('请输入正确的验证码')
                    return false
                }
                return true
            },
            errorInfo: function (iRet, info) {
                if (iRet) {
                    var errObj = {
                        '536936465': '账号或密码错误',
                        '536870912': '参数错误',
                        '536870913': '图片验证码错误',
                        '536870914': '图片验证码错误',
                        '536870915': '图片验证码错误'
                    }
                    var key = iRet + ''
                    return errObj[key] ? errObj[key] : info || '系统错误，请稍后再试。'
                } else {
                    return '系统错误，请稍后再试。'
                }
            }
        }
    })

</script>
</body>
</html>
