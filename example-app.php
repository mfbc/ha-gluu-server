<?php
session_start();
36
# Carga de dependencias necesarias
include '../src/autoload.php';
/**
* 1) Parámetros de configuración:
* callback, es la dirección a la cual el usuario será redireccionado posterior a iniciar sesión.
* keys, arreglo que contiene los parámetros de acceso para la interacción con la API de Gluu
* opcionales debug, habilita el modo debug para obtención y revisión de logs
*/
$config = [
 'callback' => 'http://localhost/hybridauth/examples/example_01.php', // or 
Hybridauth\HttpClient\Util::getCurrentUrl()
 'keys' => ['id' => '59568434-0554-44bb-b603-d62a9d2a7c9a', 'secret' =>
'2UZQ62RxF6S2ovDmU2CyIT7hnOKMeeZcR99HFhg0'],
 'debug_mode' => true,
 'debug_file' => 'error.log',
];
# Inicializar el proveedor, en este caso Gluu Server
$gluu = new Hybridauth\Provider\GluuServer($config);
/**
* 2) Autenticación de usuarios
* `authenticate()` redireccionará al usuario a la pagina de inicio de sesión de Gluu
37
* Se solicitará permiso a realizar acciones con la cuenta, luego el usuario es redirigido
* al `Authorization callback URL`(en este caso, a este mismo script).
*
* Nota: La solicitud de acceso y permiso de actuación sobre la cuenta del usuario se presenta
* únicamente la primera vez que se accede con la cuenta.
*/
$gluu->authenticate();
/**
* 3) Obtención de datos de usuario
*
* `getUserProfile` retorna una instancia de la clase Hybridauth\User\Profile que contiene la 
información
* del perfil del usuario conectado de una forma simple y estructurada.
*/
# Muestra en página los datos obtenidos
try {
 $userProfile = $gluu->getUserProfile();
 echo 'Email: ' . $userProfile->email;
 echo '<br>';
 echo 'Display Name: ' . $userProfile->displayName;
 echo '<br>';
38
 echo 'First Name: ' . $userProfile->firstName;
 echo '<br>';
 echo 'id: ' . $userProfile->identifier;
}
/**
* 4) Obtención de errores de Curl
* La lista completa de errores detallados aquí: http://curl.haxx.se/libcurl/c/libcurlerrors.html
*/
catch (Hybridauth\Exception\HttpClientFailureException $e) {
 echo 'Curl text error message : ' . $gluu->getHttpClient()->getResponseClientError();
}
/**
* 5) Obtención de errores al solicitar a la API
*
* Casos de error:
* - URI equivocada o mala solicitud http.
* - Recurso protegido sin proveer de un Access Token válido.
*/
39
catch (Hybridauth\Exception\HttpRequestFailedException $e) {
 echo 'Raw API Response: ' . $gluu->getHttpClient()->getResponseBody();
}
/**
* 6) Obtención de errores PHP, mostrará cualquier otro tipo de error fuera de las categorias 
anteriores.
*/
catch (\Exception $e) {
 echo 'Oops! We ran into an unknown issue: ' . $e->getMessage();
}
/**
* 7) Fin de sesión y desconexión del proveedor
*/
if(isset($_POST['logout'])) {
 $gluu->logOut();
}
echo '<form method="post">
 <input type="submit" name="logout" value="Logout"/>
 </form>';
