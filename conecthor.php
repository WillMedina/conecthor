<?php

include 'settings.php';

class Conecthor {

    private $mysql;
    public $error = array();
    private static $env = "production";

    public function __construct(array $dataConection) {
        $this->mysql = mysqli_connect(
                $dataConection["host"], $dataConection["user"], $dataConection["password"], $dataConection["name"]
        );

        if (!$this->mysql) {
            $now = new datetime();

            $dataerror = array(
                "mensaje" => 'Error en la conexión a la Base de datos',
                "errno" => mysqli_connect_errno(),
                "error" => mysqli_connect_error()
            );
            $this->error($dataerror);
            die();
        }
        $this->error["message"] = $this->mysql->host_info;
    }

    private function error(array $dataerror) {
        switch (self::$env) {
            case "production":
                echo '[' . date('d-m-Y H:i:s') . '] ' . $dataerror["mensaje"] . PHP_EOL;
                break;
            case "debug":
                echo PHP_EOL;
                echo '[' . date('d-m-Y H:i:s') . '] ' . ' ' . $dataerror["mensaje"] . PHP_EOL;
                echo $dataerror["errno"] . ' > ' . $dataerror["error"];
                echo PHP_EOL;
                break;
            default:
                echo "Ha ocurrido un error inesperado y desconocido en la operación";
                break;
        }
    }

    public function SELECTN(array $query) {
        
    }

    public function SELECT1(array $query) {
        
    }
    
    

}
