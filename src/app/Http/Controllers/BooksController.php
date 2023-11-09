<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Book;

/**
 * Handles the books listing page and all functions related to books management.
 */ 
class BooksController extends BaseController
{
    /**
     * Displays the books listing page. Also handles filter and order form parameters.
     */ 
    public function index(Request $request)
    {
        // Handling filter and display order parameters
        $order_field = $request->input('order_field') ? $request->input('order_field') : 'title';
        $order_direction = $request->input('order_direction') ? $request->input('order_direction') : 'asc';

        $books = Book::when($request->input('title'), function($query) use ($request){
                return $query->where('title', 'like', '%'.$request->input('title').'%');
            })
            ->when($request->input('author'), function($query) use ($request){
                return $query->where('author', 'like', '%'.$request->input('author').'%');
            })
            ->when($order_field, function($query) use ($request, $order_field, $order_direction){
                return $query->orderBy($order_field, $order_direction);
            })
            ->paginate(10);

        $filters['title'] = $request->input('title');
        $filters['author'] = $request->input('author');

        // This page's metadata
        $meta = [
            "title" => "Books | ".config('app.name'),
            "description" => "Book management at ".config('app.name'),
            "url" => config('app.url')."/books",
        ];

        return view('contents.listing.books', [
            'meta' => $meta,
            'filters' => $filters,
            'books' => $books,
            'message' => $request->session()->get('return-message'),
            'order_field' => $order_field,
            'order_direction' => $order_direction,
        ]);
    }

    /**
     * Deletes the book with the provided id and redirects to the books listing page.
     *
     * @param integer $id     The id of the book to be deleted
     */ 
    public function deleteBook(Request $request, $id)
    {
        $book = Book::find($id);
        if ($book)
        {
            $book->delete();
            $request->session()->flash('return-message', ['success', 'The book was deleted successfully!']);
        }
        else
        {
            $request->session()->flash('return-message', ['error', 'There aren\'t any books with the provided id']);
        }
        

        return redirect()->route('books');
    }
}
