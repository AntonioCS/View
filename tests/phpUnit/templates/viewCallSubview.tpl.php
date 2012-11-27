<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $this->title ?></title>
</head>
<body>
<?php echo $this->subView('subView', array('items' => array('item1','item2','item3'))) ?>
</body>
</html>
