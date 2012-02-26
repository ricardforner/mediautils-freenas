#!/usr/local/bin/php
<?php
/**
 * Services_MediaUtils_Config
 *
 * Pagina que contiene el modulo de configuracion de MediaUtils
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package mediautils
 */

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "MediaUtils" ."|". gettext("Configuration"));

include 'class.mediautils.php';
$app = new MediaUtils();

unset($errormsg);
try {
	$records = $app->getList(MediaUtils::TYPE_DELIVERY);
	$recordsNorm = $app->getList(MediaUtils::TYPE_NORMALIZE);
} catch (PDOException $e) {
	$errormsg[] = "La 'Base de datos' no est&aacute; creada. Puede crearla en el men&uacute; de Herramientas.";
}

if ($_GET['act'] === "del") {
	$item = array();
	$item['uuid'] = $_GET['uuid'];

	$mode = MediaUtils::ACTION_MODIFY_DELETE;
	$app->doAction($mode, $item);
	header("Location: services_mediautils_config.php");
	exit;
}
?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_mediautils.php"><span><?=gettext("Tools");?></span></a></li>
				<li class="tabact"><a href="services_mediautils_config.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Configuration");?></span></a></li>
			</ul>
		</td>
	</tr>

	<tr>
		<td class="tabcont">

			<form action="services_mediautils_config.php" method="post">

				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<tr>
					<td colspan="2" valign="top" class="listtopic"><?=gettext("Configuration");?></td>
				</tr>
<?php
// BLOQUE DE NORMALIZACION -----------------------------------------------------------------------------------------
?>
				<tr>
					<td width="22%" valign="top" class="vncell">Normalizar contenido</td>
					<td width="78%" class="vtable">

										<table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td width="22%" class="listhdrlr">Patr&oacute;n</td>
                                                <td width="35%" class="listhdrr">Texto original</td>
                                                <td width="33%" class="listhdrr">Texto nuevo</td>
                                                <td width="10%" class="list"></td>
                                        </tr>

<?php										
$rows = (isset($recordsNorm))?$recordsNorm->fetchAll(PDO::FETCH_ASSOC):array();
foreach($rows as $row) {
?>
										<tr>
											<td class="listlr"><?=htmlspecialchars($row["nombre"])?>&nbsp;</td>
											<td class="listr"><?=htmlspecialchars($row["campo1"])?>&nbsp;</td>
											<td class="listr"><?=$row["campo2"]?>&nbsp;</td>
											<td valign="middle" nowrap="nowrap" class="list">
												<a href="services_mediautils_edit.php?uuid=<?=$row['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit");?>" border="0" alt="<?=gettext("Edit");?>" /></a>&nbsp;
												<a href="services_mediautils_config.php?act=del&amp;uuid=<?=$row['uuid'];?>" onclick="return confirm('&iquest;Est&aacute;s seguro de borrar el registro?')"><img src="x.gif" title="<?=gettext("Delete");?>" border="0" alt="<?=gettext("Delete");?>" /></a>
											</td>
											</tr>
<?php
}
?>										
                                        <tr>
												<td class="list" colspan="3"></td>
												<td class="list">
													<a href="services_mediautils_edit.php?type=<?=MediaUtils::TYPE_NORMALIZE?>"><img src="plus.gif" title="<?=gettext("Add");?>" border="0" alt="<?=gettext("Add");?>" /></a>
												</td>
										</tr>
										</table>
						Permite la normalizaci&oacute;n de los nombres de los ficheros.
					</td>
				</tr>
<?php
// BLOQUE DE DISTRIBUCION -----------------------------------------------------------------------------------------
?>
				<tr>
					<td width="22%" valign="top" class="vncell">Distribuir contenido</td>
					<td width="78%" class="vtable">

										<table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td width="22%" class="listhdrlr">Patr&oacute;n</td>
                                                <td width="60%" class="listhdrr">Ruta destino</td>
                                                <td width="8%" class="listhdrr">Vigente</td>
                                                <td width="10%" class="list"></td>
                                        </tr>

<?php										
$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
foreach($rows as $row) {
?>
										<tr>
											<td class="listlr"><?=htmlspecialchars($row["nombre"])?>&nbsp;</td>
											<td class="listr"><?=htmlspecialchars($row["campo1"])?>&nbsp;</td>
											<td class="listbg"><?=$row["campo2"]?>&nbsp;</td>
											<td valign="middle" nowrap="nowrap" class="list">
												<a href="services_mediautils_edit.php?uuid=<?=$row['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit");?>" border="0" alt="<?=gettext("Edit");?>" /></a>&nbsp;
												<a href="services_mediautils_config.php?act=del&amp;uuid=<?=$row['uuid'];?>" onclick="return confirm('&iquest;Est&aacute;s seguro de borrar el registro?')"><img src="x.gif" title="<?=gettext("Delete");?>" border="0" alt="<?=gettext("Delete");?>" /></a>
											</td>
											</tr>
<?php
}
?>										
                                        <tr>
												<td class="list" colspan="3"></td>
												<td class="list">
													<a href="services_mediautils_edit.php?type=<?=MediaUtils::TYPE_DELIVERY?>"><img src="plus.gif" title="<?=gettext("Add");?>" border="0" alt="<?=gettext("Add");?>" /></a>
												</td>
										</tr>
										</table>
						Permite la distribuci&oacute;n de los ficheros que cumplan el patr&oacute;n hacia la Ruta destino.
					</td>
				</tr>
</table>

			<?php include("formend.inc");?>
			</form>		

			<div id="remarks">
				<?php html_remark("note", gettext("Note"), "Esta p&aacute;gina permite la configuraci&oacute;n del sistema de renombre y copia de los ficheros descargados desde <em>transmission</em> a su carpeta definitiva.");?>
			</div>

		</td>
	</tr>
</table>

<?php include("fend.inc");?>
