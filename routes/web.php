<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/users/login', [UsersController::class, 'login']); 

Route::get("/albums", [AlbumController::class, "index"])->name('albums.index');
Route::get("/albums/{id}", [AlbumController::class, "show"])->name('albums.show');
Route::post("/albums/{id}", [AlbumController::class, "edit"])->name('albums.edit');
Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
Route::put("/albums/{id}", [AlbumController::class, "update"])->name('albums.update');
Route::delete("/albums/{id}", [AlbumController::class, "destroy"])->name('albums.destroy');

Route::get("/artists", [ArtistController::class, "index"])->name('artists.index');
Route::get("/artists/{id}", [ArtistController::class, "show"])->name('artists.show');
Route::post("/artists/{id}", [ArtistController::class, "edit"])->name('artists.edit');
Route::post('/artists', [ArtistController::class, 'store'])->name('artists.store');
Route::put("/artists/{id}", [ArtistController::class, "update"])->name('artists.update');
Route::delete("/artists/{id}", [ArtistController::class, "destroy"])->name('artists.destroy');

Route::get("/members", [MemberController::class, "index"])->name('members.index');
Route::get("/members/{id}", [MemberController::class, "show"])->name('members.show');
Route::post("/members/{id}", [MemberController::class, "edit"])->name('members.edit');
Route::post('/members', [MemberController::class, 'store'])->name('members.store');
Route::put("/members/{id}", [MemberController::class, "update"])->name('members.update');
Route::delete("/members/{id}", [MemberController::class, "destroy"])->name('members.destroy');

Route::get("/songs", [SongController::class, "index"])->name('songs.index');
Route::get("/songs/{id}", [SongController::class, "show"])->name('songs.show');
Route::post("/songs/{id}", [SongController::class, "edit"])->name('songs.edit');
Route::post('/songs', [SongController::class, 'store'])->name('songs.store');
Route::put("/songs/{id}", [SongController::class, "update"])->name('songs.update');
Route::delete("/songs/{id}", [SongController::class, "destroy"])->name('songs.destroy');

require __DIR__.'/auth.php';
