<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;

Route::get('/', [CommentController::class, 'index']);
Route::post('/comment', [CommentController::class, 'store'])->name('comment.store');
Route::put('/comment/{id}', [CommentController::class, 'update'])->name('comment.update');
Route::delete('/comment/{id}', [CommentController::class, 'destroy'])->name('comment.delete');
Route::post('/comment/{id}/react', [CommentController::class, 'react'])->name('comment.react');