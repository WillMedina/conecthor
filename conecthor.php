<?php

class conecthor {

    private $mysql;
    private static $env = "debug";
    private static $collation = "utf8";

    public function __construct($dataConection) {
        $this->mysql = mysqli_connect($dataConection["host"], $dataConection["user"], $dataConection["password"], $dataConection["name"]);
        if (!$this->mysql) {
            $dataError = array(
                'mensaje' => 'Error en la conexion a la Base de Datos',
                'errno' => mysqli_connect_errno(),
                'error' => mysqli_connect_error()
            );
            $this->error($dataError);
            die();
        }
        mysqli_set_charset($this->mysql, self::$collation);
    }

    /**
     * Funcion que formatea los errores que se producen respecto a MySQL o de la 
     * libreria en si.
     * @param array $dataError Un array con los datos explicitos que ha producido 
     * el error mas algunos datos del controlador. Estos ultimos se muestran si
     * self::$env es igual a "debug".
     */
    private function error(array $dataError) {
        $hora = Date('d-m-Y H:i:s');
        switch (self::$env) {
            case 'production':
                echo PHP_EOL;
                echo '[' . $hora . '] ' . $dataError["mensaje"];
                echo PHP_EOL;
                break;
            case 'debug':
                echo PHP_EOL;
                echo '[' . $hora . '] ' . $dataError["mensaje"] . PHP_EOL;
                echo $dataError["errno"] . ' > ' . $dataError["error"];
                echo PHP_EOL;
                break;

            default:
                echo PHP_EOL;
                echo '[' . $hora . '] Error desconocido.';
                echo PHP_EOL;
                break;
        }
    }

    /**
     * Funcion que cierra la conexion a MySQL
     * @return 
     */
    function CERRAR() {
        return mysqli_close($this->mysql);
    }

    /**
     * Funcion para intentar sanitizar los ingresos/inputs a la base de datos.
     * De preferencia es preferible que se aplique en el ingreso mismo de datos 
     * que a que se aplique en la operacion interna, ya que la operacion interna
     * trata de implodar los datos array.
     * @param String $str Cadena String a Sanitizar
     * @return String Cadena sanitizada 
     */
    function escaparSTR($str) {
        return mysqli_real_escape_string($this->mysql, $str);
    }

    private function ResultToArray($r) {
        $arrayDevuelto = array();
        if (is_object($r)) {
            $i = 0;
            while ($row = $r->fetch_assoc()) {
                foreach ($row as $key => $value) {
                    $arrayDevuelto[$key][] = $value;
                }
            }
        } else {
            $dataError = array(
                "mensaje" => "Hay un error transformando valores a datos legibles",
                "errno" => "0",
                "error" => "Error transformando un mysql_result a array"
            );
            $this->error($dataError);
        }
        return $arrayDevuelto;
    }

    /**
     * Funcion query de SELECT para N registros 
     * @param array $query El Query estructurado array en formato del tipo:<br/>
     * <pre>$query = array(
     *    "campos" => array( "c1" ,"c2", "c3"),
     *    "tabla" => "tabla",
     *    "where" => array("condicion1", "condicion2")
     * );</pre>
     * @return Array Retorna un array asociativo multidimensional con los datos extraidos,
     * o un array vacio en caso de que la consulta no haya salido bien o
     * devuelva 0 columnas (en caso de error se genera la funcion error() facilmente 
     * extraible para debug)
     */
    function SELECTN(array $query) {
        $arrayFinal = array();
        if (is_array($query)) {
            $campos = implode(' , ', $query["campos"]);
            if (array_key_exists("where", $query)) {
                $wheres = implode(' AND ', $query["where"]);
                $sentencia = 'SELECT ' . $campos . ' FROM ' . $query["tabla"] . ' WHERE ' . $wheres;
            } else {
                $sentencia = 'SELECT ' . $campos . ' FROM ' . $query["tabla"];
            }

            $result = mysqli_query($this->mysql, $sentencia);
            if ($result === false) {
                $dataError = array(
                    "mensaje" => "Hay un error en la consulta, probablemente de sintaxis",
                    "errno" => "0",
                    "error" => "La consulta ha devuelto un error de sintaxis o datos incorrectos." . PHP_EOL . "La sentencia fue: " . $sentencia
                );
                $this->error($dataError);
            } else {
                $arrayFinal = $this->ResultToArray($result);
            }
        } else {
            $dataError = array(
                "mensaje" => "El formato ingresado para consulta no cumple los requisitos",
                "errno" => "0",
                "error" => "El formato ingresado para consulta en SELECTN debe ser un array."
            );
            $this->error($dataError);
        }
        return $arrayFinal;
    }

    /**
     * Funcion que inserta una fila de datos en una determinada tabla. No es util 
     * para operaciones en lote.
     * @param array $data Array que representa los datos a ingresar, debe tener 
     * este formato:
     * <pre>$insert = array(
     *   "tabla" => "tabla",
     *   "data" => array(
     *       "col1" => "val1",
     *       "col2" => "val2"
     *    )
     * );</pre>
     * @return boolean retorna TRUE en caso la consulta se haya realizado correctamente 
     * y FALSE (ademas de un error de libreria derivado del error MySQL) en caso 
     * de que la consulta no haya ingresado la fila de datos correctamente.
     */
    function INSERT1(array $data) {
        $retorno = false;
        if (is_array($data)) {
            $arrK = array();
            $arrV = array();

            foreach ($data["data"] as $key => $value) {
                $arrK[] = $key;
                if ($value == "null" or $value == "NULL") {
                    $arrV[] = "NULL";
                } else {
                    $arrV[] = '\'' . $value . '\'';
                }
            }

            $campos = implode(', ', $arrK);
            $valores = implode(", ", $arrV);
            $sentencia = 'INSERT INTO ' . $data["tabla"] . '(' . $campos . ') VALUES(' . $valores . ') ';
            $r = mysqli_query($this->mysql, $sentencia);
            if ($r === false) {
                $dataError = array(
                    "mensaje" => "Error ingresando datos",
                    "errno" => $this->mysql->errno,
                    "error" => $this->mysql->error
                );
                $this->error($dataError);
            } else {
                $retorno = true;
            }
        } else {
            $dataError = array(
                "mensaje" => "El formato ingresado para ingreso de datos no cumple los requisitos",
                "errno" => "0",
                "error" => "El formato ingresado para consulta en INSERT1 debe ser un array."
            );
            $this->error($dataError);
        }
        return $retorno;
    }

    /**
     * Funcion que sirve para actualizar datos existentes.
     * @param array $data Arreglo con los datos a actualizar. Debe tener el formato 
     * <pre>$data = array(
     *     "tabla" => "tabla",
     *     "data" => array(
     *                  "col1" => "val1"
     *               )
     *      "where" => array("condicion")
     * );</pre>
     * @return boolean Devuelve TRUE si se ha actualizado correctamente
     */
    function UPDATE1(array $data) {
        $retorno = false;
        $sets_A = array();
        if (is_array($data)) {
            $sets = "";
            foreach ($data["data"] as $key => $value) {
                if ($value != "NULL") {
                    $sets_A[] = $key . '= \'' . $value . '\'';
                } else {
                    //Evita el problema del NULL entrecomillado
                    $sets_A[] = $key . '=NULL';
                }
            }
            $wheres = implode(" AND ", $data["where"]);
            $sets = implode(",", $sets_A[]);
            $sentencia = 'UPDATE ' . $data["tabla"] . ' SET ' . $sets . ' WHERE ' . $wheres;
            $r = mysqli_query($this->mysql, $sentencia);
            if ($r === true) {
                $retorno = true;
            } else {
                $dataError = array(
                    "mensaje" => "UPDATE no se ha podido ejecutar",
                    "errno" => $this->mysql->errno,
                    "error" => $this->mysql->error
                );
                $this->error($dataError);
                $retorno = false;
            }
        } else {
            $dataError = array(
                "mensaje" => "El formato ingresado para ingreso de datos no cumple los requisitos",
                "errno" => "0",
                "error" => "El formato ingresado para consulta en INSERT1 debe ser un array."
            );
            $this->error($dataError);
        }
        return $retorno;
    }

}
