<?php $this->expands('index2') ?>


<?php $this->blockStart('scripts',true) ?>
<script type="text/javascript" src="example2.js"></script>
<?php $this->blockEnd() ?>


<?php $this->blockStart('body') ?>
Hello <?php echo $this->word ?>

And more stuff!!
<?php $this->blockEnd() ?>