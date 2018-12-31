<?php
namespace Tests\App\Exceptions;

use TestCase;
use \Mockery as m;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerTest extends TestCase
{
    /** @test */
    public function it_response_with_html_when_json_is_not_accepted()
    {
        // Mocking the isDebuMode method
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldNotReceive('isDebugMode');

        // Mock the interaction with request
        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(false);

        // Mock the interaction with exception
        $exception = m::mock(\Exception::class, ['Error!']);
        $exception->shouldNotReceive('getStatusCode');
        $exception->shouldNotReceive('getTrace');
        $exception->shouldNotReceive('getMessage');

        // Call the real method under the test
        $result = $subject->render($request, $exception);

        // Assert that `render` doesn't return a JsonResponse

        $this->assertNotInstanceOf(JsonResponse::class, $result);
    }

    /** @test */
    public function it_response_with_json_for_json_consumers()
    {
        // Mocking the isDebuMode method
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldReceive('isDebugMode')
          ->andReturnFalse();

        // Mock the interaction with request
        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturnTrue();

        // Mock the interaction with exception
        $exception = m::mock(\Exception::class, ['Doh!']);
        $exception->shouldReceive('getMessage')
              ->andReturn('Doh!');

        // Call the real method under the test
        $result = $subject->render($request, $exception);

        $data = $result->getData();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertObjectHasAttribute('error', $data);
        $this->assertAttributeEquals('Doh!', 'message', $data->error);
        $this->assertAttributeEquals(400, 'status', $data->error);
    }

    /** @test */
    public function it_provides_json_responses_for_http_exception()
    {
        // Mocking the isDebuMode method
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldReceive('isDebugMode')
          ->andReturnFalse();

        // Mock the interaction with request
        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturnTrue();

        $examples = [
                 [
                 'mock' => NotFoundHttpException::class,
                 'status' => 404,
                 'message' => 'Not Found'
                 ],
                 [
                 'mock' => AccessDeniedHttpException::class,
                 'status' => 403,
                 'message' => 'Forbidden'
                 ]
              ];

        foreach ($examples as $e) {
            $exception = m::mock($e['mock']);
            $exception->shouldReceive('getMessage')->andReturn(null);
            $exception->shouldReceive('getStatusCode')->andReturn($e['status']);

            /** @var JsonResponse $result */
            $result = $subject->render($request, $exception);
            $data = $result->getData();

            $this->assertEquals($e['status'], $result->getStatusCode());
            $this->assertEquals($e['message'], $data->error->message);
            $this->assertEquals($e['status'], $data->error->status);
        }
    }
}
