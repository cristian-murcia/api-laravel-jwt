<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {
    /*
     * Metodo para el registro de usuarios
     */

    public function register(Request $request) {
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params) && !empty($params_array)) {
            //Limpiar los datos
            $params_array = array_map('trim', $params_array);

            //Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos no son validos',
                    'error' => $validate->errors()
                );
            } else {
                //Cifrar contraseña y guardar datos
                $pwd = hash('sha256', $params->password);

                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario ha sido creado',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los campos son requeridos',
                'error' => $validate->errors()
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para el ingreso de sesión de usuario
     */

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Usuario no identificado correctamente',
                'error' => $validate->errors()
            );
        } else {
            $pwd = hash('sha256', $params->password);
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    /*
     * Metodo para la actualización de datos de usuario
     */

    public function update(Request $request) {
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {

            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);

            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            $userNew = User::where('id', $user->sub)->update($params_array);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user old' => $user,
                'user new' => $params_array
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no esta identificado'
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para la subida de un avatar
     */

    public function upload(Request $request) {
        $image = $request->file('file0');

        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']); //->header('Content-Type', 'text/plain');
    }

    /*
     * Metodo para devolver una imagen de usuario
     */

    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Imagen no encontrada'
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para devolver la informacion de un usuario
     */

    public function details($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            );
        }

        return response()->json($data, $data['code']);
    }

}
