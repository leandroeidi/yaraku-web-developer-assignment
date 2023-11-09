<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;

/**
 * Handles the books listing page and all functions related to books management.
 * 
 * Functions:
 * - index
 * - deleteBook
 * - error
 * - newBook
 * - editBook
 * - saveBook
 * - noMethod
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
            'title' => 'Books | '.config('app.name'),
            'description' => 'Book management at '.config('app.name'),
            'url' => config('app.url').'/'.\Request::path(),
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
            try
            {
                $book->delete();
            }
            catch (\Exception $e)
            {
                return $this->error($request);
            }

            $request->session()->flash('return-message', ['success', 'The book was deleted successfully!']);
        }
        else
        {
            return $this->error($request, 'NO_ID');
        }
        

        return redirect()->route('books');
    }

    /**
     * Redirects to books listing page after error with message
     */ 
    private function error(Request $request, $error_type = null)
    {
        $error_message = 'An unexpected error occurred.';
        if ($error_type == 'NO_ID')
            $error_message = 'There aren\'t any books with the provided id.';
        else if ($error_type == 'WRONG_METHOD')
            $error_message = 'The URL can\'t be accessed through that method.';

        $request->session()->flash('return-message', ['error', $error_message]);
        return redirect()->route('books');
    }

    /**
     * Redirects the flow to editBook indicating it's a new book. 
     */ 
    public function newBook(Request $request)
    {
        return $this->editBook($request, 0);
    }

    /**
     * Handles the page for creating and editing a book.
     *
     * @param integer $id     The id of the book to be edited
     */ 
    public function editBook(Request $request, $id)
    {
        // Gets the book through its id, or creates a new one.
        if (!$id)
            $book = new Book();
        else
        {
            $book = Book::find($id);
            if (!$book)
                return $this->error($request, 'NO_ID');
        }

        // This page's metadata
        $meta = [
            'title' => 'Book edit | '.config('app.name'),
            'description' => 'Editing book information at '.config('app.name'),
            'url' => config('app.url').'/'.\Request::path(),
        ];

        return view('contents.form.book-edit', [
            'meta' => $meta,
            'book' => $book,
        ]);
    }

    /**
     * Creates or edits a book.
     */ 
    public function saveBook(Request $request)
    {
        // Validates book data
        $validator = Validator::make($request->all(), [
            'title' => ['required_without:id','max:255'],
            'author' => ['required','max:255'],
        ]);

        $niceNames = array(
            'title' => 'title',
            'author' => 'author',
        );

        $validator->setAttributeNames($niceNames)->validate();

        // Gets book from id (or creates a new one) and fill the fields
        if ($request->input('id'))
        {
            $book = Book::find($request->input('id'));
            if (!$book)
                return $this->error($request, 'NO_ID');
        }
        else
        {
            $book = new Book();
            $book->title = $request->input('title');
            $book->created_at = date('Y-m-d H:i:s');
        }

        $book->author = $request->input('author');
        $book->updated_at = date('Y-m-d H:i:s');
        $book->save();

        $request->session()->flash('return-message', ['success', 'The book was saved successfully!']);
        return redirect()->route('books');
    }

    /**
     * Used when preventing a URL to be accessed through the wrong method.
     */
    public function noMethod(Request $request)
    {
        return $this->error($request, 'WRONG_METHOD');
    }
}
