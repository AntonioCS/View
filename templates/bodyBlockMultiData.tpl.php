<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $this->title ?></title>
</head>
<body>
<ul id="menu">
    <?php $this->blockStart('menu_body') ?>
        <li><?php echo $this->__value() ?></li>
    <?php $this->blockEnd() ?>
</ul>
<?php echo $this->contents ?></body>
</html>

