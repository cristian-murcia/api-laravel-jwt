<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Post;

class PruebasController extends Controller {
    
    public function testOrm(){
        /*
        $posts = Post::all();
        foreach ($posts as $post){
            echo '<h1>'.$post->title.'</h1>';
            echo '<h1>'.$post->user->name.'</h1>';
            echo '<h1>'.$post->category->name.'</h1>';
        }*/
        
        $categories = Category::all();
        foreach ($categories as $category){
            echo '<h1>'.$category->name.'</h1>';
            
            foreach ($category->posts as $post){
                echo '<span>'.$post->title .'</span>';
            }
        }
        
        die();
    }
}
