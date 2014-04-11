<?php
    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');
?>


</div>
</div>

<!-- ################################################
    FOOTER
################################################ -->
<div id="footer">
    <div id="inner-footer">
        <b><?php echo $this->_lahaina->config()->get('app')->get('title'); ?></b><br />
	<?php if ($this->_lahaina->getData('security')->getUser()) { ?>
		<b>Angemeldet als</b> <?php echo $this->_lahaina->getData('security')->getUser()->getUsername(); ?>
	    <?php } else { ?>
		<b>Nicht eingeloggt</b>
	    <?php } ?>
    </div>
    <div id="inner-footer-version">
        Seite in <?php echo round(microtime(true) - $this->_lahaina->getData('startTime'), 2) ?> Sekunden geladen<br />
        <b>Version</b> <?php echo $this->_lahaina->config()->get('app')->get('version'); ?>
    </div>
</div>

</body>
</html>

