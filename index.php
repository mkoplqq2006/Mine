<?php
	ini_set('display_errors', 0);
	$action = !empty($_REQUEST["action"]) ? $_REQUEST["action"] :"";
	$method = $_SERVER ? $_SERVER['REQUEST_METHOD'] : "";
	$data   = json_decode(file_get_contents("php://input"));
	// Read		GET
	// Create	POST
	// Update	PUT
	// Delete	DELETE
	switch ($action) {
		//数据库
		case 'connecttype':
			ConnectType();
			break;
		//测试连接
		case 'connectest':
			ConnectTest($data);
			break;
		//连接池
		case 'connect':
			Connect($method, $data);
			break;
		// 数据库
		case 'databases':
			Databases($method, $data);
			break;
		// 表
		case 'tables':
			Tables($method, $data);
			break;
		// 字段与注释
		case 'field':
			Field($method, $data);
			break;
		default:
			echo "{\"result\": false}";
			break;
	}
	exit();

	//数据库
	function ConnectType() {
		$types = array(
			"Oracle", 
            "MySQL", 
            "Microsoft SQL Server", 
            "PostgreSQL",
            "MariaDB", 
            "MongoDB",
            "Microsoft Access",
            "SQLite",
            "dBASE"
        );
		echo "{\"result\": true, \"types\": ".json_encode($types)."}";
	}

	//测试连接
	function ConnectTest($data) {
		$type = $data->type;
		$result = false;
		$msg = "";
		switch ($type) {
			case "Oracle":
				break;
			case "MySQL":
			case "MariaDB":
				try {
					$option = array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES => false,
						PDO::ATTR_STRINGIFY_FETCHES => false
					);
					$pdo = new PDO("mysql:host=".$data->ip.":".$data->port,
						$data->user, 
						$data->pwd,
						$option
					);
					$result = true;
					$msg = "Successfully made the $type connection";
				} catch (Exception $e) {
					$result = false;
					$msg = "Failed to Connect to $type";
				}
				break;
			case "Microsoft SQL Server":
				break;
			case "PostgreSQL":
				try {
					$pdo = new PDO('pgsql:host='.$data->ip." port=".$data->port, $data->user, $data->pwd);
					$result = true;
					$msg = "Successfully made the $type connection";
				} catch (Exception $e) {
					$result = false;
					$msg = "Failed to Connect to $type";
				}
				break;
			case "MongoDB":
				break;
			case "SQLite":
				$result = is_file($data->path);
				$msg = $result ? "Successfully made the $type connection" : "Failed to Connect to $type";
				break;
			case "Microsoft Access":
				$result = is_file($data->path);
				$msg = $result ? "Successfully made the $type connection" : "Failed to Connect to $type";
				break;
			case "dBASE":
				$result = is_file($data->path);
				$msg = $result ? "Successfully made the $type connection" : "Failed to Connect to $type";
				break;
		}
		echo $result ? "{\"result\": true, \"msg\": \"".$msg."\"}" : "{\"result\": false, \"msg\": \"".$msg."\"}";
	}

	//数据库Icon
	function DatabaseIcon($type){
		switch ($type) {
            case "Oracle":
                return "icon-oracle";
                break;
            case "MySQL":
                return "icon-mysql";
                break;
            case "Microsoft SQL Server":
                return "icon-sqlserver";
                break;
            case "PostgreSQL":
                return "icon-postgresql";
                break;
            case "MongoDB":
                return "icon-mongodb";
                break;
            case "MariaDB":
            	return "icon-mariadb";
                break;
            case "SQLite":
                return "icon-sqlite";
                break;
            case "Microsoft Access":
                return "icon-access";
                break;
            case "dBASE":
            	return "icon-dbase";
                break;
        }
	}

	//连接池
	function Connect($method, $data) {
		$db = new sysSqlite();
		switch ($method) {
			case 'GET':
				$sql = "SELECT id,name,type,path,ip,port,user,pwd FROM z_connect where 1=1";
				if (!empty($_REQUEST["id"])) {
					$sql .= " and id=".$_REQUEST["id"];
				}
				if (!empty($_REQUEST["key"])) {
					$sql .= " and name like '%".$_REQUEST["key"]."%'";
				}
				$result = $db->query($sql." order by id desc");
				$info   =   array();
				while ($row = $result->fetchArray()) {
					unset($row[0]);
					unset($row[1]);
					unset($row[2]);
					unset($row[3]);
					unset($row[4]);
					unset($row[5]);
					unset($row[6]);
					unset($row[7]);
					$row["icon"] = DatabaseIcon($row["type"]);
					$info[] = $row;
				}
				echo $result ? "{\"result\": true, \"msg\": \"\", \"rows\":".json_encode($info)."}" : "{\"result\": false, \"msg\": \"".$db->lastErrorMsg()."\"}";
				$db->close();
				break;
				break;
			case 'POST':
				$sql = $db->addSql("connect", array(
					'name'=> $data->name,
					'type'=> $data->type,
					'path'=> $data->path,
					'ip'=> $data->ip,
					'port'=> $data->port,
					'user'=> $data->user,
					'pwd'=> $data->pwd, //加密
					'createtime'=> date('Y年m月d日 H時i分s秒')
				));
				$result = $db->exec($sql);
				echo $result ? "{\"result\": true, \"msg\": \"\"}" : "{\"result\": false, \"msg\": \"".$db->lastErrorMsg()."\"}";
				$db->close();
				break;
			case 'PUT':
				$sql = $db->updateSql("connect", array(
					'name'=> $data->name,
					'type'=> $data->type,
					'path'=> $data->path,
					'ip'=> $data->ip,
					'port'=> $data->port,
					'user'=> $data->user,
					'pwd'=> $data->pwd, //加密
					'createtime'=> date('Y年m月d日 H時i分s秒')
				));
				$sql .= " WHERE id=".$data->id;
				$result = $db->exec($sql);
				echo $result ? "{\"result\": true, \"msg\": \"\"}" : "{\"result\": false, \"msg\": \"".$db->lastErrorMsg()."\"}";
				$db->close();
				break;
			case 'DELETE':
				$id = $_REQUEST["id"];
				$sql = "DELETE FROM z_connect WHERE id=".$id;
				$result = $db->exec($sql);
				echo $result ? "{\"result\": true, \"msg\": \"\"}" : "{\"result\": false, \"msg\": \"".$db->lastErrorMsg()."\"}";
				$db->close();
				break;
			default:
				# code...
				break;
		}
	}

	//数据库
	function Databases($method, $data) {
		$db = new sysSqlite();
		switch ($method) {
			case 'GET':
				$id = $_REQUEST["id"];
				$key = $_REQUEST["key"];
				$result = $db->query("SELECT type,path,ip,port,user,pwd FROM z_connect where id=$id");
				$row = $result->fetchArray();

				$type = $row["type"];
				$path = $row["path"];
				$ip   = $row["ip"];
				$port = $row["port"];
				$user = $row["user"];
				$pwd  = $row["pwd"];

				switch ($type) {
					case 'Oracle':
						// $dbh = new PDO("oci:dbname=test;", 'username', 'password');
						break;
					case 'MySQL':
					case 'MariaDB':
						try {
							$conInfo = "mysql:host=$ip;port=$port";
							$option = array(
								PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
								PDO::ATTR_EMULATE_PREPARES => FALSE,
								PDO::ATTR_STRINGIFY_FETCHES => FALSE
							);
							$pdo = new PDO($conInfo, $user, $pwd, $option);
							$result = $pdo->errorInfo();
							if ($result[0] != 00000)
							{
								echo "{\"result\": false, \"msg\": \"".json_encode($result)."\"}";
								exit();
							}
							$sql = "SHOW DATABASES ";
							if (!empty($key)) {
								$sql .= "WHERE `Database` like '%$key%';";
							}
							$result = $pdo->query($sql);
							echo "{\"result\": true, \"rows\": ".json_encode($result->fetchAll(PDO::FETCH_ASSOC)).", \"type\": \"".$type."\"}";
						} catch (Exception $e) {
							echo "{\"result\": false, \"rows\": []}";
						}
						break;
					case 'Microsoft SQL Server':
						break;
					case 'PostgreSQL':
						try {
							$pdo = new PDO("pgsql:dbname=postgres host=$ip port=$port", $user, $pwd);
							$sql = "SELECT datname FROM pg_database WHERE datistemplate=false ";
							if (!empty($key)) {
								$sql .= "AND datname like '%$key%';";
							}
							$result = $pdo->query($sql);
							$rows = array();
							foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
								$rows[]["Database"] = $value["datname"];
							}
							echo "{\"result\": true, \"rows\": ".json_encode($rows).", \"type\": \"".$type."\"}";
						} catch (Exception $e) {
							echo "{\"result\": false, \"rows\": []}";
						}
						break;
					case 'MongoDB':
						break;
					case 'SQLite':
						if (!empty($path)) {
							echo "{\"result\": true, \"rows\": [{ \"Database\": \"".str_replace('\\', '/', $path)."\"}], \"type\": \"".$type."\"}";
						}else{
							echo "{\"result\": false, \"rows\": []}";
						}
						break;
					case 'Microsoft Access':
						if (!empty($path)) {
							echo "{\"result\": true, \"rows\": [{ \"Database\": \"".str_replace('\\', '/', $path)."\"}], \"type\": \"".$type."\"}";
						}else{
							echo "{\"result\": false, \"rows\": []}";
						}
						break;
					case 'dBASE':
						if (!empty($path)) {
							echo "{\"result\": true, \"rows\": [{ \"Database\": \"".str_replace('\\', '/', $path)."\"}], \"type\": \"".$type."\"}";
						}else{
							echo "{\"result\": false, \"rows\": []}";
						}
						break;
				}
				break;
			case 'POST':
				break;
			case 'PUT':
				break;
			case 'DELETE':
				break;
			default:
				# code...
				break;
		}
	}

	//表
	function Tables($method, $data) {
		$db = new sysSqlite();
		switch ($method) {
			case 'GET':
				$id = $_REQUEST["id"];
				$key = $_REQUEST["key"];//关键字
				$name = $_REQUEST["name"];//数据库名称
				$result = $db->query("SELECT type,path,ip,port,user,pwd FROM z_connect where id=$id");
				$row = $result->fetchArray();

				$type = $row["type"];
				$path = $row["path"];
				$ip   = $row["ip"];
				$port = $row["port"];
				$user = $row["user"];
				$pwd  = $row["pwd"];

				switch ($type) {
					case 'Oracle':
						// $conn = new PDO("oci:dbname=",$user,$pwd);
						break;
					case 'MySQL':
					case 'MariaDB':
						$conInfo = "mysql:host=$ip;port=$port;dbname=$name;charset=utf8";
						$option = array(
							PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
							PDO::ATTR_EMULATE_PREPARES => FALSE,
							PDO::ATTR_STRINGIFY_FETCHES => FALSE
						);
						$pdo = new PDO($conInfo, $user, $pwd, $option);
						$result = $pdo->errorInfo();
						if ($result[0] != 00000)
						{
							echo "{\"result\": false, \"msg\": \"".json_encode($result)."\"}";
							exit();
						}

						$sql = "SELECT table_name,table_comment FROM information_schema.TABLES WHERE table_schema='$name' AND table_comment!='VIEW'";
						$sql2 = "SELECT table_name,table_comment FROM information_schema.TABLES WHERE table_schema='$name' AND table_comment='VIEW'";
						if (!empty($key)) {
							$sql .= " AND (table_name like '%$key%' or table_comment like '%$key%') ";
							$sql2 .= " AND (table_name like '%$key%' or table_comment like '%$key%') ";
						}
						
						$result = $pdo->query($sql);
						$rows = $result->fetchAll(PDO::FETCH_ASSOC);
						$result2 = $pdo->query($sql2);
						$rows2 = $result2->fetchAll(PDO::FETCH_ASSOC);

						echo "{\"result\": true, \"rows\": ".json_encode($rows).", \"rows2\": ".json_encode($rows2)."}";
						break;
					case 'Microsoft SQL Server':
						break;
					case 'PostgreSQL':
						try {
							$pdo = new PDO("pgsql:dbname=$name host=$ip port=$port", $user, $pwd);
							$sql = "SELECT a.tablename as table_name,(SELECT cast(OBJ_DESCRIPTION(b.relfilenode,'pg_class') as VARCHAR) FROM pg_class b WHERE b.relname=a.tablename) as table_comment FROM pg_tables a WHERE a.schemaname='public' ";
							if (!empty($key)) {
								$sql .= "AND a.tablename like '%$key%'";
							}
							$result = $pdo->query($sql);
							echo "{\"result\": true, \"rows\": ".json_encode($result->fetchAll(PDO::FETCH_ASSOC)).", \"rows2\": []}";
						} catch (Exception $e) {
							echo "{\"result\": false, \"msg\": \"\"}";
						}
						break;
					case 'MongoDB':
						break;
					case 'SQLite':
						$pdo = new PDO("sqlite:".$path);
						$result = $pdo->query("select name as table_name,'' as table_comment from sqlite_master");
						$rows = $result->fetchAll(PDO::FETCH_ASSOC);
						echo "{\"result\": true, \"rows\": ".json_encode($rows).", \"rows2\": []}";
						break;
					case 'Microsoft Access':
						$uname = explode(" ",php_uname());
						$os = $uname[0];
						switch ($os){
						  case 'Windows':
						    $driver = '{Microsoft Access Driver (*.mdb, *.accdb)}';
						    break;
						  case 'Linux':
						    $driver = 'MDBTools';
						    break;
						}
						try {
							$path = str_replace('\\', '\\\\', $path);
							$pdo = new PDO("odbc:DRIVER=$driver; DBQ=$path; Uid=; Pwd=;");
						} catch (Exception $e) {
							var_dump($e);
							exit();
						}
						$result = $pdo->query("SELECT * FROM MSysObjects");
						$rows = $result->fetchAll();
						echo "{\"result\": true, \"rows\": ".json_encode($rows).", \"rows2\": []}";
						break;
					case 'dBASE':
						//打开数据库，其中第二参数为打开方式：0只读；1只写；2可读写
						$db = dbase_open($path, 0);
						if ($db) {
							$record_numbers = dbase_numrecords($db);
							$json = "";
							for ($i = 1; $i <= $record_numbers; $i++) {
								$data = dbase_get_record_with_names($db, $i);
								$name = trim($data['NAME']);
								if ($i < $record_numbers) {
									$json .= "{\"table_name\":\"$name\",\"table_comment\":\"\"},";
								}else{
									$json .= "{\"table_name\":\"$name\",\"table_comment\":\"\"}";
								}
							}
							dbase_close($path);
							echo "{\"result\": true, \"rows\": [$json], \"rows2\": []}";
						} else {
							echo "{\"result\": false, \"msg\": \"\"}";
						}
						break;
				}
				break;
			case 'POST':
				break;
			case 'PUT':
				break;
			case 'DELETE':
				break;
			default:
				# code...
				break;
		}
	}

	//字段与注释
	function Field($method, $data) {
		$db = new sysSqlite();
		switch ($method) {
			case 'GET':
				$id = $_REQUEST["id"];
				$name = $_REQUEST["name"];//数据库名称
				$name2 = $_REQUEST["name2"];//表名称
				$result = $db->query("SELECT type,path,ip,port,user,pwd FROM z_connect where id=$id");
				$row = $result->fetchArray();

				$type = $row["type"];
				$path = $row["path"];
				$ip   = $row["ip"];
				$port = $row["port"];
				$user = $row["user"];
				$pwd  = $row["pwd"];

				switch ($type) {
					case 'Oracle':
						break;
					case 'MySQL':
					case 'MariaDB':
						$conInfo = "mysql:host=$ip;port=$port;dbname=$name;charset=utf8";
						$option = array(
							PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
							PDO::ATTR_EMULATE_PREPARES => FALSE,
							PDO::ATTR_STRINGIFY_FETCHES => FALSE
						);
						$pdo = new PDO($conInfo, $user, $pwd, $option);
						$result = $pdo->errorInfo();
						if ($result[0] != 00000)
						{
							echo "{\"result\": false, \"msg\": \"".json_encode($result)."\"}";
							exit();
						}
						$sql = "show full columns from ".$name2;
						$result = $pdo->query($sql);
						$rows = $result->fetchAll(PDO::FETCH_ASSOC);

						$sql2 = "show create table ".$name2;
						$result2 = $pdo->query($sql2);
						$rows2 = $result2->fetchAll(PDO::FETCH_ASSOC);

						echo "{\"result\": true, \"rows\": ".json_encode($rows).", \"sql\": ".json_encode($rows2)."}";
						break;
					case 'Microsoft SQL Server':
						break;
					case 'PostgreSQL':
						try {
							$pdo = new PDO("pgsql:dbname=$name host=$ip port=$port", $user, $pwd);
							$sql = "SELECT a.attname AS field, t.typname AS type, a.attlen AS length, a.atttypmod AS lengthvar
							    , a.attnotnull AS notnull, b.description AS comment
							FROM pg_class c, pg_attribute a
							    LEFT JOIN pg_description b
							    ON a.attrelid = b.objoid
							        AND a.attnum = b.objsubid, pg_type t
							WHERE c.relname = '$name2'
							    AND a.attnum > 0
							    AND a.attrelid = c.oid
							    AND a.atttypid = t.oid
							ORDER BY a.attnum";
							// if (!empty($key)) {
							// 	$sql .= "AND tablename like '%$key%'";
							// }
							$result = $pdo->query($sql);
							echo "{\"result\": true, \"rows\": ".json_encode($result->fetchAll(PDO::FETCH_ASSOC)).", \"rows2\": []}";
						} catch (Exception $e) {
							echo "{\"result\": false, \"msg\": \"\"}";
						}
						break;
					case 'MongoDB':
						break;
					case 'SQLite':
						$conInfo = "sqlite:".$path;
						$pdo = new PDO($conInfo);
						$result = $pdo->query("SELECT `sql` as `Create Table` FROM sqlite_master WHERE name='".$name2."'");
						$rows = $result->fetchAll(PDO::FETCH_ASSOC);
						echo "{\"result\": true, \"rows\": [], \"sql\": ".json_encode($rows)."}";
						break;
					case 'Microsoft Access':
						break;
					case 'dBASE':
						$db = dbase_open($path, 0);
						if ($db) {
							$record_numbers = dbase_numrecords($db);
							$json = "";
							for ($i = 1; $i <= $record_numbers; $i++) {
								$data = dbase_get_record_with_names($db, $i);
								$name = trim($data['NAME']);
								if ($i < $record_numbers) {
									$json .= "{\"table_name\":\"$name\",\"table_comment\":\"\"},";
								}else{
									$json .= "{\"table_name\":\"$name\",\"table_comment\":\"\"}";
								}
							}
							dbase_close($path);
							echo "{\"result\": true, \"rows\": [$json], \"rows2\": []}";
						}else{
							echo "{\"result\": false, \"msg\": \"\"}";
						}
						break;
				}
				break;
			case 'POST':
				break;
			case 'PUT':
				break;
			case 'DELETE':
				break;
			default:
				# code...
				break;
		}
	}

	// sysSqlite
	class sysSqlite extends SQLite3
	{
		function __construct()
		{
			$this->open('./sqlite/db.php');
		}

		function addSql($name, $array){
		    $field = '';
		    $value = '';
		    $i = 1;
		    $count = count($array);
		    foreach($array as $key=>$val){
		        if($i < $count){
		            $field.="`$key`,";
		            $value.="'$val',";
		        }else{
		            $field.="`$key`";
		            $value.="'$val'";
		        }
		        $i++;
		    }
		    return "INSERT INTO `z_$name` ($field) VALUES ($value);";
		}

		function updateSql($name, $array){
		    $field = '';
		    $i = 1;
		    $count = count($array);
		    foreach($array as $key=>$val){
		        if($i < $count){
		            $field.="`$key`='$val',";
		        }else{
		            $field.="`$key`='$val'";
		        }
		        $i++;
		    }
		    return "UPDATE `z_$name` SET $field";
		}
	}
?>
