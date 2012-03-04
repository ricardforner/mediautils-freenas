<?php
/**
 * Clase MediaUtilsBase
 *
 * Esta clase facilita la capa de negocio y abstraccion a la base de datos
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package mediautils
 */

require_once('class.crud.php');

class MediaUtilsBase extends crud {

	public $downloadedDir;
	
	const ACTION_MODIFY_UPDATE	= 'upd';
	const ACTION_MODIFY_DELETE	= 'del';
	const ACTION_MODIFY_ADD		= 'add';
	const ACTION_TOOLS_DBCREATE	= 'create';
	const ACTION_TOOLS_DBDROP	= 'drop';
	const ACTION_TOOLS_DELIVERY	= 'delivery';
	const ACTION_TOOLS_NORMALIZE= 'normalize';
	const TYPE_DELIVERY			= 'd';
	const TYPE_NORMALIZE		= 'n';
	
	public function doAction($mode, $item=null) {
		switch ($mode) {
			
			// Accion de crear bbdd
			case self::ACTION_TOOLS_DBCREATE:
				$this->doCreateDatabase();
			break;
			
			// Accion de borra bbdd
			case self::ACTION_TOOLS_DBDROP:
				$this->doDropDatabase();
			break;
			
			// Accion de mover contenido
			case self::ACTION_TOOLS_DELIVERY:
				$this->doActionDelivery();
			break;
			
			// Accion de normalizar nombres
			case self::ACTION_TOOLS_NORMALIZE:
			break;
			
			// Accion de borrar contenido
			case self::ACTION_MODIFY_ADD:
			case self::ACTION_MODIFY_DELETE:
			case self::ACTION_MODIFY_UPDATE:
				$this->doActionCRUD($mode, $item);
			break;

		}
	}
	
	public function doCreateDatabase() {
		// Tabla de delivery
		$sql = "CREATE TABLE IF NOT EXISTS tbConfig (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				tipo VARCHAR(1),
				nombre VARCHAR(100),
				campo1 VARCHAR(255),
				campo2 VARCHAR(255)
			)";
		$this->rawQuery($sql);
	}
	
	public function doDropDatabase() {
		$sql = "DROP TABLE IF EXISTS tbConfig;";
		$this->rawQuery($sql);
	}
	
	public function getList($tipo) {
		switch ($tipo) {
			case self::TYPE_DELIVERY:
				$ret = $this->rawSelect("SELECT * FROM tbConfig WHERE tipo='$tipo' ORDER BY nombre");
			break;
			case self::TYPE_NORMALIZE:
				$ret = $this->rawSelect("SELECT * FROM tbConfig WHERE tipo='$tipo' ORDER BY nombre");
			break;
		}
		return $ret;
	}

	public function getItem($fieldname, $id) {
		return $this->dbSelect('tbConfig', $fieldname, $id);
	}
	
	protected function doActionCRUD($mode, $param) {
		switch ($mode) {
			
			// Accion de borrar registro
			case self::ACTION_MODIFY_DELETE:
				$this->dbDelete('tbConfig', 'uuid', $param["uuid"]);
			break;

			// Accion de insertar registro
			case self::ACTION_MODIFY_ADD:
				$dbItem = array(
					'nombre'=>$param["nombre"],
					'tipo'=>$param["tipo"],
					'campo1'=>$param["campo1"],
					'campo2'=>$param["campo2"]
				);
				$this->dbInsert('tbConfig', array($dbItem));
			break;

			// Accion de modificar registro
			case self::ACTION_MODIFY_UPDATE:
				$dbItem = array(
					'nombre'=>$param["nombre"],
					'tipo'=>$param["tipo"],
					'campo1'=>$param["campo1"],
					'campo2'=>$param["campo2"]
				);
				if ($param["uuid"]=="-1") {
					$this->dbInsert('tbConfig', array($dbItem));
				} else {
					$this->dbUpdate('tbConfig', null, $dbItem, 'uuid', $param["uuid"]);
				}
			break;

		}
	}
	
	private function sdir( $path='.', $mask='*', $nocache=0 ){
		static $dir = array(); // cache result in memory
		if ( !isset($dir[$path]) || $nocache) {
			$dir[$path] = is_dir($path) ? scandir($path) : $path;
		}
		if (is_dir($path)) {
			foreach ($dir[$path] as $i=>$entry) {
				if ($entry!='.' && $entry!='..' && !fnmatch($mask, $entry) ) {
					$sdir[] = array("path"=>$path, "element"=>$entry, "isFile"=>(int)is_file($path."/".$entry) );
				}
			}
		} else {
			$sdir[] = -1;
		}
		return ($sdir);
	}
	
	private function doActionDelivery() {
		echo "Escaneando directorio $this->downloadedDir\n";
		$dirSource = $this->sdir($this->downloadedDir, "*.part");

		// Filtro de delivery
		$records = $this->getList(self::TYPE_DELIVERY);
		$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
		$filterList = array();
		foreach($rows as $row) {
			if ("Si"==$row['campo2']) {
				$filterList[$row['nombre']] = $row['campo1'];
			}
		}
		// Recorrer ficheros de transmission
		foreach ($dirSource as $item) {
			if (1==$item["isFile"]) {
				// 00. Es un fichero
				$filename = $item["element"];
				echo "\n$filename\n"; 
				// 01. Parar torrent
				// 02. Filtro palabras clave
				if ($this->isFound($filterList, $filename, $targetDir)) {
					// 03. Normalizar
					$filename_new = $this->normalize($filename);
					// 04. 
					$sourceFilename = $this->downloadedDir.$filename;
					$targetFilename = $targetDir.$filename_new;
					// 05. Mover
					echo "\tMover de $sourceFilename\n\t\ta $targetFilename \n";
					rename($sourceFilename, $targetFilename);
				}
				
			}
		}
		echo "\n";
	}
	
	private function isFound($arrInput, $search_value, &$retValue) {
		if (count($arrInput)==0) {
			return false;
		}
		$keys = array_keys($arrInput);
		foreach ($keys as $k) {
			if (stripos($search_value, $k) !== FALSE) {
				$retValue = $arrInput[$k];
				return true;
			}
		}
		return false;
	}
	
	private function normalize($filename) {
		$records = $this->getList(self::TYPE_NORMALIZE);
		$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
		$filename_new = $filename;
		//echo "\t\tOriginal: $filename_new\n";
		foreach($rows as $row) {
			// Cambios genericos
			if ("*"==$row['nombre']) {
				$filename_new = str_replace($row['campo1'], $row['campo2'], $filename_new);
				//echo "\t\tCambio 1: $filename_new\n";
			// Cambios especificos que cumplan patron
			} elseif (stripos($filename, $row['nombre']) !== FALSE) {
				$filename_new = str_replace($row['campo1'], $row['campo2'], $filename_new);
				//echo "\t\tCambio 2: $filename_new\n";
			}
		}
		// Eliminacion de los puntos, manteniendo extension
		if (strripos($filename_new, ".") !== FALSE) {
			$filename_new = str_replace(".", " ", substr($filename_new, 0, strripos($filename_new, ".")))
							. substr($filename_new, strripos($filename_new, "."));
			//echo "\t\tCambio 3: $filename_new\n";
		}
		
		return $filename_new;
	}	

} // fin de la classe

?>
