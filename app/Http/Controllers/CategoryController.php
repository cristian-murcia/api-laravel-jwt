<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    /*
     * Metodo que me retorna todas las categorias
     */

    public function index() {
        $categories = Category::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
        ]);
    }

    /*
     * Metodo que me retorna una categoria por id
     */

    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Categoria no encontrada'
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para guardar una categoria nueva
     */

    public function store(request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
                    'name' => 'required|unique:categories'
        ]);

        if (empty($params_array) || $validate->fails()) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha guardado la categoria'
            );
        } else {
            $category = new Category();
            $category->name = $params_array['name'];
            $category->save();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'La categoria ha sido guardada',
                'category' => $category
            );
        }

        return response()->json($data, $data['code']);
    }

    /*
     * Metodo para actualizar una categoria
     */

    public function update($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                        'name' => 'required|unique:categories'
            ]);

            unset($params_array['id']);
            unset($params_array['created_at']);

            $category = Category::where('id', $id)->update($params_array);

            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Se ha actualizado la categoria',
                'category' => $params_array
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha actualizado la categoria'
            );
        }

        return response()->json($data, $data['code']);
    }

}
