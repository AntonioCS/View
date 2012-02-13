<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $this->title ?></title>
<?php $this->blockStart('scripts',true,5) ?>
<script type="text/javascript" src="example1.js"></script>
<?php $this->blockEnd() ?>
</head>
<body>
<?php $this->blockStart('body',true) ?>
Other contents
<?php $this->blockEnd() ?>
</body>
</html>
