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
            $token = $request->header('Authorization', null);
            $jwtAuth = new \App\Helpers\JwtAuth();
            $user = $jwtAuth->checkToken($token, true);

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
    
    

}
