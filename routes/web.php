<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [TaskController::class, 'index']);
Route::post('/store', [TaskController::class, 'store']);

Route::get('/tasks/datatable', [TaskController::class, 'getTasksDataList']);


Route::post('/task',[TaskController::class,'completeTask']);
Route::delete('/deleteTask', [TaskController::class, 'destroy']);

