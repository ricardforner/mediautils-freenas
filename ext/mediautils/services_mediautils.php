#!/usr/local/bin/php
<?php
/**
 * Services_MediaUtils
 *
 * Pagina que contiene la ficha de herramientas del modulo MediaUtils
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package mediautils
 */

require_once("auth.inc");
require_once("guiconfig.inc");
require_once("ext/mediautils/class.mediautils.php");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "MediaUtils" ."|". gettext("Tools"));

$app = new MediaUtils();

if (!is_array($config['bittorrent'])) {
	$config['bittorrent'] = array();
}
$app->downloadedDir = $config['bittorrent']['downloaddir'];

if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	unset($do_action);

	if ("normalize" === $_POST['action']) {
		$errormsg[] = "Funci&oacute;n de 'Normalizar nombres' no implementada.";
	}
	
	if ((!$input_errors) || (!$errormsg)) {
		$do_action = true;
		$action = $_POST['action'];
	}	
}

if (!isset($do_action)) {
	$do_action = false;
}
?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="services_mediautils.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Tools");?></span></a></li>
				<li class="tabinact"><a href="services_mediautils_config.php"><span><?=gettext("Configuration");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
		<?php if ($input_errors) print_input_errors($input_errors);?>
		<form action="services_mediautils.php" method="post" name="iform" id="iform">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline("Acciones disponibles");?>
			<?php html_combobox("action", gettext("Command"), $action,
				array(
					"delivery" => "Distribuir contenido",
					"normalize" => "Normalizar nombres",
					"create" => "Crear base de datos",
					"drop" => "Borrar base de datos"
				), "", true);?>			
			</table>
			<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Execute");?>" />
			</div>

			<?php if(($do_action) && (!$errormsg)) {
				echo(sprintf("<div id='cmdoutput'>%s</div>", gettext("Command output:")));
				echo('<pre class="cmdoutput">');
				switch ($action) {
					case "create":
						echo("Creando base de datos...". "<br />");
						$app->doAction(MediaUtils::ACTION_TOOLS_DBCREATE);
						mwexec("logger -t mediautils-extension Base de datos creada");
					break;
					case "drop":
						echo("Borrando base de datos...". "<br />");
						$app->doAction(MediaUtils::ACTION_TOOLS_DBDROP);
						mwexec("logger -t mediautils-extension Base de datos borrada");
					break;
					case "delivery":
						echo("Distribuyendo contenido a directorios...". "<br />");
						$app->doAction(MediaUtils::ACTION_TOOLS_DELIVERY);
						mwexec("logger -t mediautils-extension Distribuido contenido a sus directorios finales");
					break;
					case "normalize":
						echo("Normalizando nombres...". "<br />");
						$app->doAction(MediaUtils::ACTION_TOOLS_NORMALIZE);
						mwexec("logger -t mediautils-extension Normalizacion de nombres");
					break;
				}
				echo (0 == $result) ? gettext("Done.") : gettext("Failed.");
				echo('</pre>');
			}?>
			<div id="remarks">
				<?php html_remark("note", gettext("Note"), "Detalles sobre las acciones disponibles:
				<div id='enumeration'><ul>
					<li><b>Distribuir contenido</b> mueve los ficheros descargados de <em>transmission</em> a su directorio definitivo.</li>
					<li><b>Normalizar nombres</b> <i>no est&aacute; implementada en la versi&oacute;n 0.1.x</i>.</li>
					<li><b>Crear base de datos</b> crea el fichero de base de datos. S&oacute;lo debe ejecutarse si no est&aacute; creada.</li>
					<li><b>Borrar base de datos</b> borra el fichero de base de datos si existe.</li>
				</ul></div>");?>
			</div>
		<?php include("formend.inc");?>
		</form>		
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
