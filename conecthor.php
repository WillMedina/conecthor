<?php

class conecthor {

    private $mysql;
    private static $env = "debug";

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

}
