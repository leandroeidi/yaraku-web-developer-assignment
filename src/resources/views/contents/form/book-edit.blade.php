@extends('layouts.default')
@section('content')

<div style="width:100%; max-width:1000px;padding:32px;">

<div class="form-div">
    <a href="/books" class="button-cancel">< Back</a>
    <form method="post" action="/books/save">
    @csrf
    <table>
        @if($book->id)
        <tr>
            <th>ID</th>
            <td>
                {{ $book->id }}
                <input type="hidden" name="id" id="id" value="{{ $book->id }}">
            </td>
        </tr>
        @endif
        <tr>
            <th><span style="color:red">*</span>Title</th>
            <td>
                <input type="text" name="title" id="title" class="text-field" value="{{ old('title', $book->title) }}" required="required" autocomplete="off" @if($book->id) disabled="disabled" @endif>
                @error('title')
                    <div class="red">{{ $message }}</div>
                @enderror
            </td>
        </tr>
        <tr>
            <th><span style="color:red">*</span>Author</th>
            <td>
                <input type="text" name="author" id="author" class="text-field" value="{{ old('author', $book->author) }}" required="required" autocomplete="off">
                @error('author')
                    <div class="red">{{ $message }}</div>
                @enderror
            </td>
        </tr>
    </table>

    <div>
        <button class="button" style="margin-top:20px;">Save</button>
    </div>
    </form>
</div>

</div>

@stop