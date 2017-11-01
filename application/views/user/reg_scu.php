<!DOCTYPE html>
<html>
<head>
    <title>successful</title>
    <meta charset="utf-8">
</head>
<body>
<p>Activation successful！ <span id='num'>3</span>seconds later Browser automatically jump web site home page!</p>

<script type="text/javascript">
    setInterval(function(){
        var num = document.getElementById("num").innerText;
        num --;
        document.getElementById("num").innerText = num;
    },1000)
    setTimeout(function(){
        window.location.href = "http://trade.test.com";
    }, 3000 )
</script>

</body>
</html>