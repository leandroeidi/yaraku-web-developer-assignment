<?php

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
    return redirect()->route('books');
});

Route::get('/books', 'BooksController@index')->name('books');
Route::post('/books', 'BooksController@index');
Route::get('/books/new', 'BooksController@newBook');
Route::get('/books/{id}/edit', 'BooksController@editBook');
Route::get('/books/{id}/delete', 'BooksController@deleteBook');
Route::get('/books/save', 'BooksController@noMethod');
Route::post('/books/save', 'BooksController@saveBook');
Route::get('/books/export', 'BooksController@noMethod');
Route::post('/books/export', 'BooksController@exportFile');
