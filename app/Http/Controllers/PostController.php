<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index',
                'show',
                'getImage',
                'getPostByCategory',
                'getPostByUser'
        ]]);
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

            $user = $this->getIdentity($request);

            $postOld = Post::find($id);
            $where = [
                'id' => $id,
                'user_id' => $user->sub
            ];
            $post = Post::updateOrCreate($where, $params_array);

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

    /*
     * Metodo para la subida de imagenes
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
                'message' => 'La imagen no es permitida',
                'error' => $validate->errors()
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'La imagen ha sido cargada',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Traida de imagen de post
     */

    public function getImage($filename) {

        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getPostByCategory($id) {
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

    public function getPostByUser($id) {
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

}
