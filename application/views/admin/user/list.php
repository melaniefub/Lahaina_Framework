<?php
    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\message\Helper as MessageHelper;

$m_helper = new MessageHelper();
    $m_helper->renderMessage($this->_lahaina);

    $tablelist = $this->_lahaina->getData('tablelist');
    $tablelist->render();
?>
<br />
<a class="button" href="<?php echo URL ?>/admin/user/create">Benutzer erstellen</a>

<div class="dialog-confirm delete-user" title="Benutzer löschen?">
    <p><span class="ui-icon ui-icon-alert">!</span>Wollen Sie den Benutzer wirklich löschen?</p>
</div>		
