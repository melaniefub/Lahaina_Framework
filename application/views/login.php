<?php
    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\message\Helper as MessageHelper;

$m_helper = new MessageHelper();
    $m_helper->renderMessage($this->_lahaina);
?>

<form method="post" action="<?php echo URL ?>/login/auth">

    <table class="form">
        <tr>
            <td><label for="username">Benutzername</label></td>
            <td><input maxlength="9" name="username" id="username" value="" /></td>
        </tr>
        <tr>
            <td><label for="password">Passwort</label></td>
            <td><input name="password" type="password" id="password" value="" /></td>
        </tr>
        <tr>
            <td><!-- empy --></td>
            <td><input type="submit" value="Speichern" /></td>
        </tr>
    </table>

</form>

