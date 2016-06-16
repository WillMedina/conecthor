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
                echo $dataError["errno"] . '>' . $dataError["error"];
                echo PHP_EOL;
                break;

            default:
                echo PHP_EOL;
                echo '[' . $hora . '] Error desconocido.';
                echo PHP_EOL;
                break;
        }
    }

    function CERRAR() {
        return mysqli_close($this->mysql);
    }

    function escaparSTR($str) {
        return mysqli_real_escape_string($this->mysql, $str);
    }

    private function ResultToArray($r) {
        $arrayDevuelto = array();
        $arr_keys = array();
        $arr_values = array();
        if (is_object($r)) {
            //$arrayDevuelto = $r->fetch_assoc();
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
     * SELECTN
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
            $campos = mysqli_real_escape_string($this->mysql, implode(' , ', $query["campos"]));

            if (array_key_exists("where", $query)) {
                $wheres = mysqli_real_escape_string($this->mysql, implode(' AND ', $query["where"]));
                $sentencia = 'SELECT ' . $campos . ' FROM ' .
                        mysqli_real_escape_string($this->mysql, $query["tabla"]) .
                        ' WHERE ' . $wheres;
            } else {
                $sentencia = 'SELECT ' . $campos . ' FROM ' .
                        mysqli_real_escape_string($this->mysql, $query["tabla"]);
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
}