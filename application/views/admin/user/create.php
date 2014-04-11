<?php
    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\message\Helper as MessageHelper;
use lahaina\libraries\validation\Helper as ValidationHelper;

$m_helper = new MessageHelper();
    $m_helper->renderMessage($this->_lahaina);

    $v_helper = new ValidationHelper();

    $user = $this->_lahaina->getData('user');
?>

<form method="post" action="<?php echo URL ?>/admin/user/save">

    <table class="form">
        <tr class="<?php echo $v_helper->checkValidationError('username', $this->_lahaina) ?>">
            <td><label for="username">Benutzername</label></td>
            <td><input maxlength="9" name="user[username]" id="username" value="<?php echo $user->get('username') ?>" /></td>
        </tr>
        <tr class="<?php echo $v_helper->checkValidationError('email', $this->_lahaina) ?>">
            <td><label for="email">E-Mailadresse</label></td>
            <td><input name="user[email]" id="email" value="<?php echo $user->get('email') ?>" /></td>
        </tr>
        <tr class="<?php echo $v_helper->checkValidationError('password', $this->_lahaina) ?>">
            <td><label for="password">Passwort</label></td>
            <td><input name="user[password]" id="email" type="password" value="<?php echo $user->get('password') ?>" /></td>
        </tr>
        <tr class="<?php echo $v_helper->checkValidationError('password2', $this->_lahaina) ?>">
            <td><label for="password2">Passwort (wiederholen)</label></td>
            <td><input name="user[password2]" id="email" type="password" value="<?php echo $user->get('password2') ?>" /></td>
        </tr>
        <tr class="<?php echo $v_helper->checkValidationError('role_id', $this->_lahaina) ?>">
            <td><label for="roles">Rolle</label></td>
            <td><select name="user[role_id]" id="roles">
                    <option value=""><!-- empy --></option>
		    <?php
			$roles = $this->_lahaina->getData('roles');
			foreach ($roles as $role) {
			    echo '<option ' . ($user->get('role_id') == $role->get('role_id') ? 'selected="selected"' : '') . ' value="' . $role->get('role_id') . '">' . $role->get('name') . '</option>';
			}
		    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><!-- empy --></td>
            <td><input type="submit" value="Speichern" /><a class="button" href="<?php echo URL ?>/admin/user">Zurück zur Übersicht</a></td>
        </tr>
    </table>

</form>