@extends('layouts.default')
@section('content')

<div style="width:100%; max-width:1000px;padding:32px;">

{{-- Errors display --}}
@if ($errors->any())
    <div class="form-validation-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
{{-- End of Errors display --}}

{{-- Message from backend --}}
@if($message)
<div class="message" @if($message[0] == "success") style="background-color: #016d01;" @else style="background-color: #6d0404;" @endif id="message" onclick="closeMessage();">
    {{ $message[1] }}
</div>
@endif
{{-- End of Message from backend --}}

{{-- Top block --}}
<div class="filter-bar">
    <button class="button" onclick="toggleFilter();">Filters</button>

    {{-- Export options block --}}
    <div>
        <form method="post" action="/books/export">
        @csrf
        <input type="hidden" name="title" value="{{ old('title', (array_key_exists('title', $filters) ? $filters['title'] : '')) }}">
        <input type="hidden" name="author" value="{{ old('author', (array_key_exists('author', $filters) ? $filters['author'] : '')) }}">
        <input type="hidden" name="order_field" value="{{ old('order_field', $order_field) }}">
        <input type="hidden" name="order_direction" value="{{ old('order_direction', $order_direction) }}">
        <select class="select-field" name="file_fields">
            <option value="1" @if(old('file_fields')=="1") selected="selected" @endif>Titles and authors</option>
            <option value="2" @if(old('file_fields')=="2") selected="selected" @endif>Titles</option>
            <option value="3" @if(old('file_fields')=="3") selected="selected" @endif>Authors</option>
        </select>

        <select class="select-field" name="file_type">
            <option value="csv" @if(old('file_type')=="csv") selected="selected" @endif>CSV</option>
            <option value="xml" @if(old('file_type')=="xml") selected="selected" @endif>XML</option>
        </select>
        
        <button class="button">Export</button>
        </form>
    </div>
    {{-- End of Export options block --}}

    <div><a href="/{{ \Request::path() }}/new" class="button-new">Add new book</a></div>
</div>
{{-- End of Top block --}}

{{-- Filter block --}}
<form method="post" action="/books">
@csrf
<div class="filter" id="filter">
    <div class="filter-content" id="filter-content">
        <table>
            <tr>
                <th>Title</th>
                <td>
                    <input type="text" name="title" id="filter-title" class="text-field" value="{{ old('title', (array_key_exists('title', $filters) ? $filters['title'] : '')) }}">
                </td>
            </tr>
            <tr>
                <th>Author</th>
                <td>
                    <input type="text" name="author" id="filter-author" class="text-field" value="{{ old('author', (array_key_exists('author', $filters) ? $filters['author'] : '')) }}">
                </td>
            </tr>
            <tr>
                <th><button class="button" style="margin-top:20px;">Search</button></th>
                <td><button type="button" class="button-cancel" style="margin-top:20px" onclick="resetFilterBooks()">Reset</button></td>
            </tr>
        </table>
    </div>
</div>
</form>
{{-- End of Filter block --}}

@if($books->count())

    {{-- Book listing table --}}
    <form id="order_form" method="post" action="/books">
    @csrf
    <input type="hidden" name="title" value="{{ array_key_exists('title', $filters) ? $filters['title'] : '' }}">
    <input type="hidden" name="author" value="{{ array_key_exists('author', $filters) ? $filters['author'] : '' }}">
    <input type="hidden" name="order_field" id="order_field" value="{{ $order_field }}">
    <input type="hidden" name="order_direction" id="order_direction" value="{{ $order_direction }}">
    <div class="table-div">
        <table>
            <tr>
                <th>Title <img @if($order_field == 'title') src="/img/common/order-{{ $order_direction }}.png" @else src="/img/common/order-both.png" @endif class="clickable-image" onclick="changeOrderBooks('title', '{{ $order_field }}', '{{ $order_direction }}');"></th>
                <th>Author <img @if($order_field == 'author') src="/img/common/order-{{ $order_direction }}.png" @else src="/img/common/order-both.png" @endif class="clickable-image" onclick="changeOrderBooks('author', '{{ $order_field }}', '{{ $order_direction }}');"></th>
                <th>Actions</th>
            </tr>
            @foreach($books as $book)
            <tr>
                <td>
                    {{ $book->title }}
                </td>
                <td>
                    {{ $book->author }}
                </td>
                <td>
                    <div class="grid-actions-div">
                        <a href="/{{ \Request::path() }}/{{ $book->id }}/edit" class="grid-button">Edit</a>
                        <a href="/{{ \Request::path() }}/{{ $book->id }}/delete" onclick="return confirm('Are you sure you want to delete this book?')" class="grid-button">Delete</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    </form>
    {{-- End of Book listing table --}}

    {{-- Pagination --}}
    <div class="table-div-bottom">
        <div>Showing {{ $books->firstItem() }}-{{ $books->lastItem() }} of {{ $books->total() }} books</div>
        @if($books->hasPages())
        <div class="pagination-buttons">
            @if($books->currentPage() > 1)
            <a href="/{{ \Request::path() }}?page=1" class="pagination-button" style="letter-spacing:-3px;"><<</a>
            <a href="/{{ \Request::path() }}?page={{ $books->currentPage() - 1 }}" class="pagination-button"><</a>
            @endif
            @if($books->currentPage() > 2)
            ...
            @endif
            @if($books->currentPage() > 1)
            <a href="/{{ \Request::path() }}?page={{ $books->currentPage() - 1 }}" class="pagination-button">{{ $books->currentPage() - 1 }}</a>
            @endif
            <a href="/{{ \Request::path() }}?page={{ $books->currentPage() }}" class="pagination-button">{{ $books->currentPage() }}</a>
            @if($books->currentPage() < $books->lastPage())
            <a href="/{{ \Request::path() }}?page={{ $books->currentPage() + 1 }}" class="pagination-button">{{ $books->currentPage() + 1 }}</a>
            @endif
            @if($books->currentPage() < $books->lastPage() - 1)
            ...
            @endif
            @if($books->currentPage() < $books->lastPage())
            <a href="/{{ \Request::path() }}?page={{ $books->currentPage() + 1 }}" class="pagination-button">></a>
            <a href="/{{ \Request::path() }}?page={{ $books->lastPage() }}" class="pagination-button" style="letter-spacing:-3px;">>></a>
            @endif
        </div>
        @endif
    </div>
    {{-- End of Pagination --}}
@else
    <div style="font-size:16px;margin-top:30px;">No books match your search criteria. Please change the search parameters and try again.</div>
@endif

</div>

@stop