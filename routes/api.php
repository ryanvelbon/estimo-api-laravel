<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;


/*
| Public Routes
|--------------------------------------------------------------------------
| Here is where you can register public routes.
|
*/
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');


// `/users/me` conflicts with `/users/{id}` and thus needs to be defined before it
Route::middleware('auth:sanctum')->get('/users/me', [UserController::class, 'me']);
Route::get('/users/{id}', [UserController::class, 'show']);

/*
| Protected Routes
|--------------------------------------------------------------------------
| Here is where you can register protected routes.
|
*/
Route::group(['middleware' => ['auth:sanctum']], function () {
    // auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::patch('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // tasks
    Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});


/*
| Test Routes
|--------------------------------------------------------------------------
| Here is where you can register routes which are not part of the final API
| design but just for testing.
*/
Route::get('/some-public-route', function (Request $request) {return "Apple!";});
Route::middleware('auth:sanctum')->get('/some-protected-route', function (Request $request) {return "Orange!";});