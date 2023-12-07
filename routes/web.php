<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\ColumnCardUpdateController;
use App\Http\Controllers\ColumnCardDestroyController;
use App\Http\Controllers\ColumnCardCreateController;
use App\Http\Controllers\CardsReorderUpdateController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnDestroyController;
use App\Http\Controllers\BoardColumnCreateController;
use App\Http\Controllers\ReportController;

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

Route::get('/', function () {
    return redirect('/login');
    // return Inertia::render('Login');
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/boards/{board?}', [BoardController::class, 'show'])->name('boards');
    Route::post('/add-board', [BoardController::class, 'store'])->name('addBoard');
    Route::post('get-cards-based-on-filters', [BoardController::class,'getDetailsByFilter']);
    Route::post('update-card-details', [BoardController::class,'updateCardDetails']);
    Route::post('update-board-users', [BoardController::class,'updateBoardUsers']);

    Route::post('/boards/{board}/columns', BoardColumnCreateController::class)
        ->name('boards.columns.store');

    Route::delete('/columns/{column}', ColumnDestroyController::class)
        ->name('columns.destroy');

    Route::post('/columns/{column}/cards', ColumnCardCreateController::class)
        ->name('columns.cards.store');

    Route::post('/columns/{column}/cards/{card}', [ColumnCardUpdateController::class, 'updateCard'])
        ->scopeBindings()->name('columns.cards.update');
    // ->scopeBindings();

    Route::delete('/columns/{column}/cards/{card}', ColumnCardDestroyController::class)
        ->scopeBindings()->name('columns.cards.destroy');

    // Route::put('/cards/reorder', CardsReorderUpdateController::class)
    //     ->name('cards.reorder');
    Route::put('/cards/reorder', [CardsReorderUpdateController::class,'cardReorder'])
        ->name('cards.reorder');

    Route::get('/boards-list', [BoardController::class, 'index'])->name('boards.list');
    Route::post('assign-card', [CardController::class, 'assignCardToUser'])->name('assign.card');

    Route::post('upload-board', [CardController::class, 'importBoardData'])->name('uploadBoard');
    Route::post('/add-comments', [CommentController::class, 'storeComment'])->name('storeComment');
    Route::post('/get-card-details', [CardController::class, 'getCardDetails']);
    Route::get('/get-columns', [BoardController::class, 'getColumns']);
    Route::post('/get-activities', [CardController::class, 'getActivities']);

    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/get-reports', [ReportController::class, 'getReportsData']);
    
    Route::post('/reports/export', [ReportController::class, 'export']);

    Route::post('/delete-file', [BoardController::class, 'deleteBulkFile']);
    Route::post('/update-column', [BoardController::class, 'updateColumn']);
    Route::get('/get-import-details', [BoardController::class, 'getFileDetails']);

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('search-user', [ProfileController::class, 'searchUser']);
    Route::post('/user/logout', [ProfileController::class, 'logout'])->name('logout');
});

require __DIR__ . '/auth.php';
