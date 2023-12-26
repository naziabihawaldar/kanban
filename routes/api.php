<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-projects', [ApiController::class, 'getProjects']);
    Route::get('/get-tasks', [ApiController::class, 'getTasks']);   
    Route::post('/store-task', [ApiController::class, 'storeTask']);   
    Route::post('/update-task', [ApiController::class, 'updateTask']);
    Route::post('/store-project', [ApiController::class, 'storeProject']);
    Route::post('/update-column', [ApiController::class, 'updateColumn']);
    Route::get('/get-project-assignees', [ApiController::class, 'getProjectAssignees']);
    Route::get('/get-reports', [ApiController::class, 'getReports']);
});