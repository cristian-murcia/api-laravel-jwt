<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    /*
     * Metodo para la traida de todos los post
     */

    public function index() {
        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $posts
        ]);
    }

    /*
     * Metodo para la traida de post por id
     */

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Post no encontrado'
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para guardar un post
     */

    public function store(Request $request) {

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $user = $this->getIdentity($request);

            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Datos incorrectos',
                    'error' => $validate->errors()
                );
            } else {
                $post = new Post();

                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El post ha sido guardado',
                    'post' => $params_array
                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha guardado el post'
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para actualizar un post
     */

    public function update($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        );

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            $postOld = Post::find($id);
            $post = Post::where('id', $id)->update($params_array);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El post ha sido actualizado',
                'postOld' => $postOld,
                'postNew' => $params_array
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para borrar un post
     */

    public function destroy($id, Request $request) {

        $user = $this->getIdentity($request);
        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

        if (is_object($post)) {
            $post->delete();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El post ha sido eliminado'
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no se ha encontrado'
            );
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        $token = $request->header('Authorization', null);
        $jwtAuth = new \App\Helpers\JwtAuth();
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

}
