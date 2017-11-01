<?php
/* Smarty version 3.1.29, created on 2017-10-02 16:58:46
  from "/usr/local/src/project/trade/application/views/test.html" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_59d1ffc6f17955_78069443',
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
function content_59d1ffc6f17955_78069443 ($_smarty_tpl) {
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
