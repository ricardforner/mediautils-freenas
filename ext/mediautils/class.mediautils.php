<?php
/**
 * Clase MediaUtils
 *
 * Esta clase facilita la capa de abstraccion a la aplicacion MediaUtils
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package mediautils
 *
 * @link http://code.google.com/p/mediautils-freenas/
 */

require_once('class.mediautilsbase.php');

class MediaUtils extends MediaUtilsBase {

	public function __construct() {
		$this->dsn = "sqlite:/mnt/cfinterno/usr/www/bbdd/media_utils.sdb";
	}
	
	
} // fin de la classe

?>
