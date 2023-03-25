<?php
//configuracion de cors
require_once('../config/cors.php');
// dependencias
require_once('../domain/activo.php');
require_once('../domain/funcionario.php');
require_once('../data/consultas_activo.php');
require_once('../data/consultas_funcionario.php');

//recibir objetos tipo json desde react
$input = file_get_contents('php://input');
$data = json_decode($input, true);


function InsertarFuncionario($data ){

  $consultaFuncionario = new ConsultaFuncionario();

  $funcionario = $consultaFuncionario->buscarFuncionario($data["dni_funcionario"]);

  if($funcionario == null){
    // debo insertar el funcionario
    $registro = new Funcionario(
      $data["dni_funcionario"],
      $data["nombre_funcionario"],
      1
    );
    $id_funcionario = $consultaFuncionario->insertarFuncionario($registro);
    return $id_funcionario;
  }else{
    return $funcionario["id_funcionario"];
  
  }
  
}


if (isset($_GET["consulta"])) {
  
  $consultaActivo = new ConsultaActivo();
  $consultaFuncionario = new ConsultaFuncionario();

  //identificar consulta
  switch ($_GET["consulta"]) {

    case 1: //listar

        //Ejecucion de procedimiento
        $consultaActivo->listarActivos();

      break;

    case 2: //insertar 

      //revisar que data traiga datos y que exista!

      if ( isset($data) && !empty($data)){
        

        //recorrer el array de objetos para la insersion de activos
        foreach ($data as $key => $value) {
        //*Ejecucion de procedimiento en caso de que todos los funcionarios esten registrados no hace nada,
        //* en caso de que no esten registrados los inserta
        $id_funcionario = InsertarFuncionario($data);

          
        // $consulta activo = va y busca por etiqueta
          $activo_por_etiqueta = $consultaActivo->buscarActivo($value["n_etiqueta"]);
          
          if($activo_por_etiqueta ==  ""){
            
            //inserta activo
            $activo = new Activo( $value["n_etiqueta"], $value["marca"],$value["modelo"],$value["serie"],
            $value["descripcion"],"", "",$value["valor_libros"],$value["condicion"],$value["clase_activo"],$id_funcionario,$value['nombre_funcionario'] );
                    
           $consultaActivo->insertarActivo($activo);

          }

        }

      } else {
        // respuesta si no envia parametros
       // $json["n_etiqueta"] = $data["n_etiqueta"];
        $json["mensaje"] = "Error una o varias entradas estan vacias!";
        echo json_encode($json);
        
      }

      break;

    case 3: //editar

      if (isset($data["n_etiqueta"]) && !empty($data["n_etiqueta"])
      && isset($data["marca"]) && !empty($data["marca"]) 
      && isset($data["modelo"]) && !empty($data["modelo"])
      && isset($data["serie"]) && !empty($data["serie"])
      && isset($data["descripcion"]) && !empty($data["descripcion"])
      && isset($data["id_ubicacion"]) && !empty($data["id_ubicacion"])
      && isset($data["valor_libro"]) && !empty($data["valor_libro"])
      && isset($data["condicion"]) && !empty($data["condicion"])
      && isset($data["clase_activo"]) && !empty($data["clase_activo"])
      && isset($data["id_funcionario"]) && !empty($data["id_funcionario"])
      ) {
        //Ejecucion de procedimiento
        $activo = new Activo( $data["n_etiqueta"], $data["marca"],$data["modelo"],$data["serie"],$data["descripcion"],$data["id_ubicacion"], "",$data["valor_libro"],$data["condicion"],$data["clase_activo"],$data["id_funcionario"], "");
        
        $consultaActivo->actualizarActivo($activo);
        
      } else {
        // respuesta si no envia parametros
        $json["n_etiqueta"] = $data["n_etiqueta"];
        $json["marca"] = $data["marca"];
        $json["modelo"] = $data["modelo"];
        $json["serie"] = $data["serie"];
        $json["descripcion"] = $data["descripcion"];
        $json["id_ubicacion"] = $data["id_ubicacion"];
        $json["valor_libro"] = $data["valor_libro"];
        $json["condicion"] = $data["condicion"];
        $json["clase_activo"] = $data["clase_activo"];
        $json["id_funcionario"] = $data["id_funcionario"];
        
        $json["mensaje"] = "Error una o varias entradas estan vacias!";
        echo json_encode($json);
        
      }

      break;

    case 4: //eliminar


      if (isset($data["n_etiqueta"]) && !empty($data["n_etiqueta"]) ) {
        //Ejecucion de procedimiento
        $consultaActivo->eliminarActivo($data["n_etiqueta"]);
        
      } else {
        // respuesta si no envia parametros
        $json["n_etiqueta"] = $data["n_etiqueta"];
        $json["mensaje"] = "Error una o varias entradas estan vacias!";
        echo json_encode($json);
        
      }
      
      break;

  }
} else {

  $respuesta["consulta"] = $_GET["consulta"];
  $respuesta["mensaje"] = "Debe ingresar la opcion de consulta!";
  $json = $respuesta;

  echo json_encode($json);
}