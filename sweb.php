<?php
  include('db.php');
  BaseDatos('localhost','root','','cinestar');
  
  $parametros = $_SERVER['REQUEST_URI'];
  $parametros = str_replace("%20", " ", $parametros);
  $parametros = explode("/",$parametros);
  $parametros = array_slice($parametros,2);

  if ( isset( $parametros[0] ) )
    if ( $parametros[0] == "formatos" ) getFormatos();
    else if ( $parametros[0] == 'ciudades' ) getCiudades();
    else if ( $parametros[0] == 'cines' ) getCines();
    else if ( $parametros[0] == 'peliculas' ) getPeliculas();
    else if ( $parametros[0] == 'usuarios' ) getUsuarios();
  
  function getFormatos() {
    global $_SQL;
    $_SQL = "call sp_getFormatos()";

    $data = getRegistros();
		$success = count( $data ) > 0 ? true : false;
		$message = $success ? "Registros encontrados" : "Registros no encontrados";

    echo getJSON($success, $data, $message);
  }

  function getCiudades() {
    global $_SQL;
    $_SQL = "call sp_getCiudades()";

    $data = getRegistros();
		$success = count( $data ) > 0 ? true : false;
		$message = $success ? "Registros encontrados" : "Registros no encontrados";

    echo getJSON($success, $data, $message);
  }

  function getCines () {
    global $parametros;
    global $_SQL;

    if ( isset( $parametros[2] ) )
      $_SQL = $parametros[2] == "peliculas" ? "call sp_getCinePeliculas('$parametros[1]')" : "call sp_getCineTarifas('$parametros[1]')";
    else $_SQL = isset( $parametros[1] ) ? "call sp_getCine('$parametros[1]')" : "call sp_getCines()";

    $data = getRegistros();
		$success = count( $data ) > 0 ? true : false;
		$message = $success ? "Registros encontrados" : "Registros no encontrados";

		echo getJSON($success, $data, $message);
  }
    
  function getPeliculas () {
    global $parametros;  
    global $_SQL;

    if ( isset( $parametros[1] ) ) 
      if ( is_numeric( $parametros[1] ) ) $_SQL = "call sp_getPelicula('$parametros[1]')";
      else {
        $parametros[1] = $parametros[1] == "cartelera" ? 1 : ( $parametros[1] == "estrenos" ? 2 : 3 );
        $_SQL = "call sp_getPeliculas('$parametros[1]')";
    } else $_SQL = "call sp_getPeliculass()";

    $data = getRegistros();
		$success = count( $data ) > 0 ? true : false;
		$message = $success ? "Registros encontrados" : "Registros no encontrados";

		echo getJSON($success, $data, $message);
  }
  
  function getUsuarios() {
    global $parametros;
    
    if ( isset( $parametros[1] ) ) {
      if ( $parametros[1] == "guardar" ) echo getUsuarioGuardar();
      else if ( $parametros[1] == "login" ) echo getUsuarioLogin();
      else if ( $parametros[1] == "correo" ) echo getUsuarioCorreo();
      else if ( $parametros[1] == "passwordd" ) echo setUsuarioPasswordd();
    }
  }

  function getUsuarioGuardar() {
    global $_SQL;

    $usuario = json_decode( file_get_contents("php://input"), true );
    if ( isset( $usuario['nombres'] ) && isset( $usuario['apellidos'] ) && 
         isset( $usuario['dni'] ) && isset( $usuario['passwordd'] ) && 
         isset( $usuario['telefono'] ) && isset( $usuario['correo'] )  )  {
      $nombres = $usuario['nombres'];
      $apellidos = $usuario['apellidos'];
      $dni = $usuario['dni'];
      $passwordd = $usuario['passwordd'];
      $telefono = $usuario['telefono'];
      $correo = $usuario['correo'];
      $_SQL = "call sp_Usuario_Guardar( '$nombres','$apellidos','$dni','$passwordd','$telefono','$correo')";

      $data = ejecutarSQL();
		  $success = $data > 0 ? true : false;
		  $message = $success ? "Usuario registrado" : "Usuario no pudo registrarse";

      if ( $success ) {
        $_SQL = "call sp_getUsuario('$dni','$passwordd')";
        $data = getRegistros();
      }

		  return getJSON($success, $data, $message);
    } else return getJSON(false, null, "Parámetros inválidos");
  }

  function getUsuarioLogin() {
    global $_SQL;

    $usuario = json_decode( file_get_contents("php://input"), true );
    if ( isset( $usuario['dni'] ) && isset( $usuario['passwordd'] ) ) {
      $dni = $usuario['dni'];
      $passwordd = $usuario['passwordd'];
      $_SQL = "call sp_getUsuario('$dni','$passwordd')";

      $data = getRegistros();
		  $success = count( $data ) > 0 ? true : false;
		  $message = $success ? "Usuario encontrado" : "Dni y/o password inválidos";

		  return getJSON($success, $data, $message);
    } else return getJSON(false, null, "Parámetros inválidos");
  }

  function getUsuarioCorreo() {
    global $_SQL;

    $usuario = json_decode( file_get_contents("php://input"), true );
    if ( isset( $usuario['correo'] ) ) {
      $correo = $usuario['correo'];
      $_SQL = "call sp_getUsuario_Correo('$correo')";

      $data = getRegistros();
		  $success = count( $data ) > 0 ? true : false;
		  $message = $success ? "Usuario encontrado" : "Correo inválido";

		  return getJSON($success, $data, $message);
    } else return getJSON(false, null, "Parámetros inválidos");
  }

  function setUsuarioPasswordd() {
    global $_SQL;

    $usuario = json_decode( file_get_contents("php://input"), true );
    if ( isset( $usuario['correo'] ) && isset( $usuario['passwordd'] ) ) {
      $correo = $usuario['correo'];
      $passwordd = $usuario['passwordd']; 
      $_SQL = "call sp_setUsuario_Passwordd('$correo','$passwordd')";

      $data = ejecutarSQL();
		  $success = $data > 0 ? true : false;
		  $message = $success ? "Password actualizado" : "Correo inválido";

		  return getJSON($success, $data, $message);
    } else return getJSON(false, null, "Parámetros inválidos");
  }

  function getJSON( $success, $data, $message ) {
    $json = array("success" => $success, "data" => $success ? $data : null, "message" => $message );

		return json_encode( $json, JSON_UNESCAPED_UNICODE );
  }

?>