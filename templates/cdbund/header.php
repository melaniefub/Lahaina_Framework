<?php
    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\navigation\Navigation;
use lahaina\libraries\navigation\ActionItem;
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content=""  />
        <meta name="keywords" content="" />

        <link rel="shortcut icon" href="<?php echo TEMPLATE_URL; ?>/images/favicon.ico" type="image/x-icon" /> 

        <title><?php echo $this->_lahaina->config()->get('app')->get('title') . ' - ' . $this->getTitle(); ?></title>

        <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_URL; ?>/css/template.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_URL; ?>/css/style.css" media="screen"  />

        <link rel="stylesheet" type="text/css" href="<?php echo LIBRARY_URL; ?>/message/template/css/style.css" media="screen"  />
        <link rel="stylesheet" type="text/css" href="<?php echo LIBRARY_URL; ?>/validation/template/css/style.css" media="screen"  />

        <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_URL; ?>/js/jquery-ui/themes/cdbund/jquery-ui-1.10.2.cdbund.css" media="screen"  />

        <script type="text/javascript" src="<?php echo TEMPLATE_URL; ?>/js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="<?php echo TEMPLATE_URL; ?>/js/jquery-ui/jquery-ui-1.10.2.cdbund.min.js"></script>

        <!-- jQuery-UI Datepicker localization (http://docs.jquery.com/UI/Datepicker/Localization) -->
        <script type="text/javascript" src="<?php echo TEMPLATE_URL; ?>/js/jquery-ui/jquery-ui.datepicker-de.js"></script>
        <!-- 
        <script type="text/javascript" src="<?php echo TEMPLATE_URL; ?>/js/jquery-ui/jquery-ui.datepicker-fr.js"></script>
        <script type="text/javascript" src="<?php echo TEMPLATE_URL; ?>/js/jquery-ui/jquery-ui.datepicker-it.js"></script> -->

        <script type="text/javascript">

	    $(function() {

		$('.list .icon').parent().addClass('action');

		// Adding cssClass to different input types
		$("input:text:not(.datepicker), input:password").addClass("");
		$("input:radio").addClass("radio");
		$("input:checkbox").addClass("checkbox");

		// Initialize jQuery-UI buttons
		$("input:submit, input:button, a.button").button();

		// Initialize jQuery-UI calendar inputs (if the fields are not disabled)
		$(".datepicker:not(:disabled)").datepicker({
		    regional: "de",
		    dateFormat: "dd.mm.yy",
		    showOn: "button",
		    minDate: 0,
		    buttonImage: "'. TEMPLATE_URL .'images/icons/calendar.png",
		    buttonImageOnly: true});

		// Initialize jQuery-UI tabs
		$(".tabs").tabs();


		$(".dialog-confirm").dialog({
		    autoOpen: false,
		    resizable: false,
		    modal: true,
		    buttons: {
			"OK": function() {
			    if (typeof currentForm != "undefined") {
				currentForm.submit();
			    }
			    if (typeof currentHref != "undefined") {
				window.location.href = currentHref;
			    }
			    $(this).parents("form").submit();
			    $(this).dialog("close");
			},
			"Abbrechen": function() {
			    $(this).dialog("close");
			}
		    }
		});

		$("form.confirm").submit(function() {
		    currentForm = this;
		    return false;
		});

		$("a.confirm").click(function() {
		    currentHref = $(this).attr("href");
		    return false;
		});

	    });
        </script>

    </head>
    <body>

        <!-- ################################################
            HEADER
        ################################################ -->
        <div id="logo"></div>
        <div id="header">
            <div id="admin-ch"> 
                <a href="http://www.admin.ch/" title="Link zur Bundesverwaltung" class="admin-chlink">Bundesverwaltung admin.ch</a>
            </div>
            <div id="department-name">
                <a href="http://www.vbs.admin.ch/" title="Link zum Eidgenössisches Departement für Verteidigung, Bevölkerungsschutz und Sport" class="department-link">Eidgenössisches Departement für Verteidigung, Bevölkerungsschutz und Sport</a>
            </div>
            <div id="application-name">
		<?php echo $this->_lahaina->config()->get('app')->get('title'); ?><br />
                <small><i>Version <?php echo $this->_lahaina->config()->get('app')->get('version'); ?></i></small>
            </div>
        </div>

        <!-- ################################################
            SMALL NAVIGATION (SERVICE & LANGUAGE)
        ################################################ -->
        <div id="small-navigation">
            <div id="service-navigation">
                <ul>
                    <li><a href="<?php echo URL; ?>">Startseite</a></li>
                    <li><a href="http://intranet.fub.admin.ch">FUB Intranetseite</a></li>
                </ul>
            </div>
            <div id="language-navigation">
                &nbsp;
            </div>
            <div style="clear:left"></div>
        </div>

        <!-- ################################################
            TOP NAVIGATION
        ################################################ -->
        <div id="top-navigation">
	    <?php
		$security = $this->_lahaina->getData('security');

		$nav = new Navigation();

		if ($security->checkUserRole('ADMIN')) {
		    $nav->add(new ActionItem('Benutzerverwaltung', 'admin/user'));
		}

		if ($security instanceof lahaina\libraries\security\LoginSecurity) {
		    // Login buttons
		    if ($security->getUser()) {
			$nav->add(new ActionItem('Logout', 'Login', 'logout'));
		    } else {
			$nav->add(new ActionItem('Login', 'Login'));
		    }
		}

		$nav->render($this->_lahaina);
	    ?>
        </div>


        <!-- ################################################
            MAIN NAVIGATION & CONTENT
        ################################################ -->
        <div class="main-wrapper">
            <div id="main-mavigation">

		<?php
		    $nav = new Navigation(array());

		    $security = $this->_lahaina->getData('security');

		    if ($this->_lahaina->hasCurrentController('admin\user') && $security->checkUserRole('ADMIN')) {
			$nav = new Navigation(array(
			    new ActionItem('Übersicht', 'admin/user', 'index'),
			    new ActionItem('Benutzer erstellen', 'admin/user', 'create'),
			));
		    }

		    $nav->render($this->_lahaina);
		?>

            </div>
            <div id="content">

		<?php
		    if ($this->getTitle() != '') {
			echo '<h1>' . $this->getTitle() . '</h1>';
		    }
		?>