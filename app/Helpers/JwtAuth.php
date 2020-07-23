<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {

    public $key;

    public function __construct() {
        $this->key = 'esto_es_una_clave_super-secreta--457845';
    }

    public function signup($email, $password, $getToken = null) {
        //Buscar credenciales de usuario
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();
        //Comprobar si es correcto la información
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        //Generar un token con los datos de usuario identificado
        if ($signup) {
            $token = array(
                'sub' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'password' => $user->password,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            //Devolver los datos descodificados o el token
            if (is_null($getToken)) {
                $data = $jwt;
            } else { 
                $data = $decode;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }

        return $data;
    }

    /*
     * Metodo para la verificación del token
     */

    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }

}
