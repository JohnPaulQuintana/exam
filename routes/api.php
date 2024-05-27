<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Profile\ProfileController;
// use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// routes for create accounts
Route::match(['get','post'], '/register', [AuthController::class, 'register'])->name('register');
Route::match(['get','post'], '/login', [AuthController::class, 'login'])->name('login');

// this route should be accessible only when the user is authenticated only
//prefix for user, as user
Route::group(['prefix'=>'user', 'middleware'=>['auth:sanctum'], 'as'=>'user'], function(){

    // profile information update
    Route::match(['get', 'post'], 'profile', [ProfileController::class, 'profile'])->name('profile');

    //display blogs and create blogs
    Route::match(['get','post'], 'blog', [BlogController::class, 'blog'])->name('blog');
    Route::post('update/blog', [BlogController::class, 'update'])->name('update');
    Route::delete('delete/blog', [BlogController::class, 'delete'])->name('delete');
    
});