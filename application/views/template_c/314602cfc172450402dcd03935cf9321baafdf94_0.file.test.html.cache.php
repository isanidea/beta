<?php
/* Smarty version 3.1.29, created on 2017-10-02 16:48:59
  from "/usr/local/src/project/trade/application/views/test.html" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_59d1fd7b3ac8d3_32791581',
  'file_dependency' => 
  array (
    '314602cfc172450402dcd03935cf9321baafdf94' => 
    array (
      0 => '/usr/local/src/project/trade/application/views/test.html',
      1 => 1506928850,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_59d1fd7b3ac8d3_32791581 ($_smarty_tpl) {
$_smarty_tpl->compiled->nocache_hash = '88785250159d1fd7b390fb6_80480210';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{ $test.title}</title> // 原文是 <title><?php echo $_smarty_tpl->tpl_vars['test']->value['title'];?>
</title>，是错误的写法，也有可能是Smarty版本的原因

    <style type="text/css">
    </style>
</head>
<body>
<?php echo $_smarty_tpl->tpl_vars['test']->value;?>

<?php echo md5($_smarty_tpl->tpl_vars['test']->value['num']);?>
 // 原文这里也写错了
<br>
<?php echo $_smarty_tpl->tpl_vars['tmp']->value;?>

</body>
</html><?php }
}
