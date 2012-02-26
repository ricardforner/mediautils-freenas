#!/usr/local/bin/php
<?php
/**
 * Services_MediaUtils_Edit
 *
 * Pagina que contiene la ficha de edicion del modulo MediaUtils
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package mediautils
 */

require("auth.inc");
require("guiconfig.inc");

$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$type = $_GET['type'];
if (isset($_POST['tipo']))
	$type = $_POST['tipo'];

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "MediaUtils" ."|". gettext("Configuration"), isset($uuid) ? gettext("Edit") : gettext("Add"));

include 'class.mediautils.php';
$app = new MediaUtils();
$pItem = array();

if (isset($uuid) && (FALSE !== ($cnid = $app->getItem("uuid", $uuid)))) {
	$pItem['uuid'] = $cnid[0]['uuid'];
	$pItem['nombre'] = $cnid[0]['nombre'];
	$pItem['tipo'] = $cnid[0]['tipo'];
	$pItem['campo1'] = $cnid[0]['campo1'];
	$type = (isset($type)) ? $type : $cnid[0]['tipo'];
	if (MediaUtils::TYPE_NORMALIZE==$type) {
		$pItem['campo2'] = $cnid[0]['campo2'];
	} else if (MediaUtils::TYPE_DELIVERY==$type) {
		$pItem['campo2'] = ("Si"==$cnid[0]['campo2']);
	}
} else {
	$pItem['uuid'] = -1;
	$pItem['nombre'] = "";
	$pItem['tipo'] = $type;
	$pItem['campo1'] = "";
	if (MediaUtils::TYPE_NORMALIZE==$type) {
		$pItem['campo2'] = "";
	} else if (MediaUtils::TYPE_DELIVERY==$type) {
		$pItem['campo2'] = true;
	}
}

if (MediaUtils::TYPE_NORMALIZE==$type) {
	$optionsTipo = array( MediaUtils::TYPE_NORMALIZE => "Normalizar nombre" );
} else if (MediaUtils::TYPE_DELIVERY==$type) {
	$optionsTipo = array( MediaUtils::TYPE_DELIVERY => "Distribuir contenido" );
}

if ($_POST) {
	unset($input_errors);
	$pItem = $_POST;

	if ($_POST['Cancel']) {
		header("Location: services_mediautils_config.php");
		exit;
	}
	
	$reqdfields = array();
	$reqdfieldsn = array();
	$reqdfieldst = array();
	if (MediaUtils::TYPE_NORMALIZE==$type) {
		$reqdfields = explode(" ", "nombre campo1");
		$reqdfieldsn = array("Patr&oacute;n", "Texto original");	
		$reqdfieldst = explode(" ", "string string");
	} else if (MediaUtils::TYPE_DELIVERY==$type) {
		$reqdfields = explode(" ", "nombre campo1");
		$reqdfieldsn = array("Patr&oacute;n", "Ruta destino");	
		$reqdfieldst = explode(" ", "string string");
	}
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
			
	if (($_POST['nombre'] && !is_string($_POST['nombre']))) {
		$input_errors[] = gettext("El campo 'Nombre' contiene caracteres inv&aacute;lidos.");
	}
	
	if (!$input_errors) {
		$item = array();
		$item['uuid'] = $_POST['uuid'];
		$item['nombre'] = $_POST['nombre'];
		$item['tipo'] = $_POST['tipo'];
		$item['campo1'] = $_POST['campo1'];
		if (MediaUtils::TYPE_NORMALIZE==$type) {
			$item['campo2'] = $_POST['campo2'];
		} else if (MediaUtils::TYPE_DELIVERY==$type) {
			$item['campo2'] = isset($_POST['campo2'])?"Si":"No";
		}

		if (isset($uuid) && (FALSE !== $cnid) && ($uuid!=-1)) {
			$mode = MediaUtils::ACTION_MODIFY_UPDATE;
		} else {
			$mode = MediaUtils::ACTION_MODIFY_ADD;
		}
		$app->doAction($mode, $item);
		
		header("Location: services_mediautils_config.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_mediautils_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">

				<?php
				if (MediaUtils::TYPE_NORMALIZE==$type) {
					html_titleline("Normalizar texto");
					html_inputbox("nombre", "Patr&oacute;n", $pItem['nombre'], "Patr&oacute;n de b&uacute;squeda.<br/><b>Nota:</b> El valor * sirve como comod&iacute;n para todos los elementos procesados.", true, 40);
					html_combobox("tipo", "Tipo", $pItem['tipo'], $optionsTipo, "", true);
					html_inputbox("campo1", "Texto original", $pItem['campo1'], "Entra el texto que va ser buscado para ser reemplazado.", true, 40);
					html_inputbox("campo2", "Texto nuevo", $pItem['campo2'], "Entra el nuevo texto o d&eacute;jalo en blanco para que el texto encontrado sea eliminado.", false, 40);
				} else if (MediaUtils::TYPE_DELIVERY==$type) {
					html_titleline("Distribuir contenido");
					html_inputbox("nombre", "Patr&oacute;n", $pItem['nombre'], "Patr&oacute;n de b&uacute;squeda.", true, 40);
					html_combobox("tipo", "Tipo", $pItem['tipo'], $optionsTipo, "", true);
					html_filechooser("campo1", "Ruta destino", $pItem['campo1'], "Entra el directorio destino donde los ficheros que cumplan el patr&oacute;n ser&aacute;n movidos.", $g['media_path'], true, 60);
					html_checkbox("campo2", "Vigente", $pItem['campo2'], "<br/>Activar la casilla para que el patr&oacute;n este vigente cuando se ejecute la acci&oacute;n de <em>Distribuir contenido</em>.", false);
				}
				?>
				
				</table>
						<div id="submit">
							<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gettext("Save") : gettext("Add")?>" />
							<input name="Cancel" type="submit" class="formbtn" value="<?=gettext("Cancel");?>" />
							<input name="uuid" type="hidden" value="<?=$pItem['uuid'];?>" />
						</div>
						<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
