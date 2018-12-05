<?php
namespace Tests\App\Http\Controllers;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use TestCase;

class BooksControllerTest extends TestCase
{
    /** @test */
    public function index_status_should_be_200()
    {
        $this->get('/books')
            ->seeStatusCode(200);
    }

    /** @test */
    public function index_should_return_collection_of_records()
    {
        $this->get('/books')
            ->seeJson([
                'title' => 'War of the Worlds'
            ])
            ->seeJson([
                'title' => 'A Wrinkle in Time'
            ]);
    }

    /** @test */
    public function show_should_return_a_valied_book()
    {
        $this->get('/books/1')
            ->seeStatusCode(200)
            ->seeJson([
                'id'    => 1,
                'author' => 'H. G. Wells'
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
}
