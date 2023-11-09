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
 * - index (public)
 * - bookSearchBase (private)
 * - deleteBook (public)
 * - error (private)
 * - newBook (public)
 * - editBook (public)
 * - saveBook (public)
 * - noMethod (public)
 * - exportFile (public)
 */ 
class BooksController extends BaseController
{
    // Variables used for the chosen field and direction of the search's orderBy
    var $order_field;
    var $order_direction;

    /**
     * Displays the books listing page. Also handles filter and order form parameters.
     */ 
    public function index(Request $request)
    {
        // Gets the books list to tbe displayed
        $books = $this->bookSearchBase($request);
        $books = $books->paginate(10);

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
            'order_field' => $this->order_field,
            'order_direction' => $this->order_direction,
        ]);
    }

    /**
     * Prepares the search in the books table to be used by other functions.
     *
     * @param Request $request     A request that contains the fields necessary for the search
     */ 
    private function bookSearchBase(Request $request)
    {
        // Gets the field and direction to be used on orderBy, which by default is 'title asc'
        $this->order_field = $request->input('order_field') ? $request->input('order_field') : 'title';
        $this->order_direction = $request->input('order_direction') ? $request->input('order_direction') : 'asc';

        // Creates the search based on the parameters
        $books = Book::when($request->input('title'), function($query) use ($request){
            return $query->where('title', 'like', '%'.$request->input('title').'%');
        })
        ->when($request->input('author'), function($query) use ($request){
            return $query->where('author', 'like', '%'.$request->input('author').'%');
        })
        ->when($this->order_field, function($query) use ($request){
            return $query->orderBy($this->order_field, $this->order_direction);
        });

        return $books;
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

        // Gets book from id (or creates a new one) and fills the fields
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

    /**
     * Exports data of current book search to CSV and XML files.
     */
    public function exportFile(Request $request)
    {
        // Validates export options
        $validator = Validator::make($request->all(), [
            'file_fields' => ['required','integer'],
            'file_type' => ['required','in:csv,xml'],
        ]);

        $niceNames = array(
            'file_fields' => 'fields',
            'file_type' => 'file type',
        );

        $validator->setAttributeNames($niceNames)->validate();

        // Gets the data from the books to be included in the file
        $books = $this->bookSearchBase($request);
        $books = $books->get();

        // Prepares the necessary parameters
        $file_type = $request->input('file_type');
        $file_fields = $request->input('file_fields');

        if ($file_fields == 1)
            $columns = array('Title', 'Author');
        else if ($file_fields == 2)
            $columns = array('Title');
        else if ($file_fields == 3)
            $columns = array('Author');

        $file_name = 'books.'.$file_type;

        // Creates the header of the file
        $headers = array(
            "Content-type"        => "text/".$file_type,
            "Content-Disposition" => "attachment; filename=$file_name",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        // Puts the necessary data in the file, taking in consideration which fields the user wants
        $callback = function() use($books, $columns, $file_type)
        {
            $file = fopen('php://output', 'w');

            if ($file_type == 'csv')
            {
                fputcsv($file, $columns);

                foreach ($books as $book) {
                    $data_in_row = array();
                    if (in_array('Title', $columns))
                        $data_in_row[] = $book->title;
                    if (in_array('Author', $columns))
                        $data_in_row[] = $book->author;
                    fputcsv($file, $data_in_row);
                }
            }
            else
            {
                $xml = '<?xml version="1.0" encoding="UTF-8" ?><BOOKS>';
                foreach ($books as $book)
                {
                    $xml .= '<BOOK>';
                    if (in_array('Title', $columns))
                        $xml .= '<TITLE>'.$book->title.'</TITLE>';
                    if (in_array('Author', $columns))
                        $xml .= '<AUTHOR>'.$book->author.'</AUTHOR>';
                    $xml .= '</BOOK>';
                }
                $xml .= '</BOOKS>';

                fwrite($file, $xml);
            }

            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }  
}
