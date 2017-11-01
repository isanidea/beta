<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<title>404</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link href="http://st.test.com/xnb_home/img/favicon.ico" rel="shortcut icon">
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
	<style>
		* {
			padding: 0;
			margin: 0
		}

		html, body {
			position: relative;
			height: 100%;
			background: #67ace4;

		}

		.container {
			position: absolute;
			min-width: 410px;
			left: 50%;
			top: 50%;
			-webkit-transform: translate3d(-50%, -50%, 0);
			transform: translate3d(-50%, -50%, 0)
		}

		.tip {
			font-size: 200px;
			color: #fff;
			text-align: center
		}

		.text-box {
			padding-top: 20px;
			color: #fff;
			font-size: 0
		}

		.info {
			display: inline-block;
			vertical-align: top;
			font-size: 14px;
			line-height: 20px
		}

		.en {
			border-bottom: 1px solid #fff
		}

		.zh {
			letter-spacing: 2px
		}

		.btn-index, .btn-last {
			display: inline-block;
			margin-top: 4px;
			margin-left: 10px;
			padding: 10px 20px;
			vertical-align: middle;
			font-size: 14px;
			line-height: 14px;
			color: #fff;
			text-decoration: none;
			border: 1px solid #fff;
			border-radius: 30px
		}

		.btn-index:hover, .btn-last:hover {
			background: #fff;
			color: #67ace4
		}
		.set-time {
			padding: 10px 0;
			color: #FFF;
			text-align: center;
		}
		#time{
			color: red;
		}

		@media screen and (max-width: 767px) {
			.container {
				width: 200px;
				min-width: auto;
			}

			.tip {
				font-size: 100px
			}

			.info, .btn-index, .btn-last {
				display: block;
				text-align: center;
				margin: 0 auto 10px
			}
		}</style>
</head>
<body>
<div class="container">
	<h1 class="tip">404</h1>
	<div class="text-box">
		<div class="info">
			<div class="en">This page no longer exists</div>
		</div>
		<a href="http://trade.test.com/" class="btn-index" id="returnHome">Home</a>
		<a href="javascript:history.go(-1);" class="btn-last" id="returnPrev">Previous</a>
	</div>
	<p class="set-time"> <span id="time">5 </span> Seconds later retuen home page !</p>
</div>
<script>
	(function () {
		var t = setInterval(function () {
			var time = document.getElementById('time');
			var text = time.innerHTML;

			if( text == 0 ){
				clearInterval(t);
				return window.location.href = 'http://trade.test.com/';
			}
			time.innerHTML = text - 1;

		},1000);
	})();
</script>
</body>
</html>