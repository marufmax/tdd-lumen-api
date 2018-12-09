<?php
namespace Tests\App\Http\Controllers;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use TestCase;

class BooksControllerTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function index_status_should_be_200()
    {
        $this->get('/books')
            ->seeStatusCode(200);
    }

    /** @test */
    public function index_should_return_collection_of_records()
    {
        $books = factory('App\Models\Book', 2)->create();

        $this->get('/books');

        foreach ($books as $book) {
            $this->seeJson([
                'title' => $book->title
            ])
                ->seeJson([
                    'title' => $book->title
                ]);
        }
    }

    /** @test */
    public function show_should_return_a_valied_book()
    {
        $book = factory('App\Models\Book')->create();

        $this->get("/books/{$book->id}")
            ->seeStatusCode(200)
            ->seeJson([
                'id'    => $book->id,
                'title'    => $book->title,
                'description'    => $book->description,
                'author' => $book->author
            ]);

        $data = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    /** @test */
    public function show_should_failed_when_book_id_does_not_exists()
    {
        $this->get('/books/9999')
            ->seeStatusCode(404)
            ->seeJson([
                'error' => [
                    'message' => 'Book not found'
                ]
            ]);
    }
    /** @test */
    public function show_route_does_not_match_invalid_route()
    {
        $this->get('/books/this-is-invalid');

        $this->assertNotRegExp('/Book not found/', $this->response->getContent());
    }

    /** @test */
    public function store_should_save_new_book_to_the_database()
    {

        // Create the book
        $this->json('POST', '/books', [
            'title' => 'The invisible man',
            'description' => 'An invisible man is trapped in the terror of his own',
            'author' => 'H. G. Wells'
        ]);

        dd($this->response->getContent());
        $this->seeJson(['created' => true])
            ->seeInDatabase('books', ['title' => 'The invisible man']);
    }

    /** @test */
    public function store_should_responsd_201_and_location_header_when_success()
    {
        // Create the book
        $this->json('POST', '/books', [
            'title' => 'The invisible man',
            'description' => 'An invisible man is trapped in the terror of his own',
            'author' => 'H. G. Wells'
        ]);

        $this->seeStatusCode(201)
            ->seeHeaderWithRegExp('Location', '#/books/[\d]+$#');
    }

    /** @test */
    public function update_should_only_change_fillable_fields()
    {
        $book = factory('App\Models\Book')->create([
            'title' => 'War of the Worlds',
            'description' => 'A science finction masterpice about Martain',
            'author' => 'H. G. Wells'
        ]);

        $this->put("/books/{$book->id}", [
            'id'            => 5,
            'title'         => 'War of the Worlds',
            'description'   => 'This books is way better than movie',
            'author'        => 'H. G. Wells'
        ]);

        $this->seeStatusCode(200)
            ->seeJson([
                'id' => 1,
                'title' => 'Waar of the Worlds',
                'description' => 'This books is way better than movie',
                'author' => 'H. G. Wells'
            ]);

        $this->seeInDatabase('books', [
            'title' => 'War of the Worlds',
        ]);
    }

    /** @test */
    public function update_should_faild_invalid_id()
    {
        $this->put('/books/9999999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not found'
                ]
            ]);
    }

    /** @test */
    public function update_should_not_match_invailed_route()
    {
        $this->put('/books/this-is-invailed')
            ->seeStatusCode(404);
    }

    /** @test */
    public function destroy_should_remove_a_valid_book()
    {
        $book = factory('App\Models\Book')->create();

        $this->delete("/books/{$book->id}")
            ->seeStatusCode(204)
            ->isEmpty();

        $this->notSeeInDatabase('books', ['id' => $book->id]);
    }

    /** @test */
    public function destroy_should_return_404_with_an_invalid_id()
    {
        $this->delete('/books/99999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not found'
                ]
            ]);
    }

    /** @test */
    public function destroy_should_not_match_an_invalid_id()
    {
        $this->delete('/books/this-is-an-invalid')
            ->seeStatusCode(404);
    }
}
