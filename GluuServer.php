<?php
namespace Hybridauth\Provider;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Data;
use Hybridauth\User;
final class GluuServer extends OAuth2
{
 /**
 * Definición de parametros a intercambiar/solicitar del usuario
 */
 protected $scope = 'profile email openid clientinfo';
 /**
 * URI base para solicitudes con proveedor
 */
 protected $apiBaseUrl = 'https://open.cidla.org/oxauth/restv1/';
33
 /**
 * URI donde el usuario final es redireccionado para realizar el proceso de autenticación
 */
 protected $authorizeUrl = 'https://open.cidla.org/oxauth/restv1/authorize';
 /**
 * URI donde se obteniene o renueva el `Access Token`
 */
 protected $accessTokenUrl = 'https://open.cidla.org/oxauth/restv1/token';
 /* Opcionales: configuración de parámetros extra */
 protected function initialize()
 {
 parent::initialize();
 /** Opcional: define como será el intercambio de parámetros para la obtención del 
`Access Token`
 * IMPORTANTE: Debe coincidir con el definido en Gluu, para el caso se utilizó el método 
`Authorization Code`
 */
 $this->tokenExchangeParameters = [
 'client_id' => $this->clientId,
 'grant_type' => 'authorization_code',
34
 'redirect_uri' => $this->callback
 ];
 /**
 * Definición y paso de parámetros para conexión con provedor mediante keys
 */
 $this->tokenExchangeMethod = 'POST';
 $this->tokenExchangeHeaders = ['Authorization' => 'Basic ' . base64_encode($this-
>clientId . ':' . $this->clientSecret)];
 }
 function getUserProfile()
 {
 /**
 * Solicitud a API .../oxauth/restv1/userinfo
 */
 $response = $this->apiRequest('userinfo');
 $data = new Data\Collection($response);
 $userProfile = new User\Profile();
 # Verificación de obtención de la información del usuario
 if (!$data->exists('email')) {
 throw new UnexpectedValueException('Provider API returned an unexpected 
response.');
35
 }
 $userProfile->email = $data->get('email');
 $userProfile->displayName = $data->get('name');
 $userProfile->firstName = $data->get('nickname');
 $userProfile->identifier = $data->get('inum');
 return $userProfile;
 }
 /**
 * Solicitud a API .../oxauth/restv1/end_session
 */
 function logOut(){
 $response = $this->apiRequest('end_session');
 return $response;
 }
}
