<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BooksController extends Controller
{
    /**
     * GET /books
     *
     * @return array
     */
    public function index()
    {
        return Book::all();
    }

    /**
     * GET /books/{id}
     *
     * @param integer $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            return Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ],
            ], 404);
        }
    }

    /**
     * POST /books
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $book = Book::create($request->all());

        return response()->json(['created' => true], 201, [
            'Location' => route('books.show', ['id' => $book->id])
        ]);
    }
}
