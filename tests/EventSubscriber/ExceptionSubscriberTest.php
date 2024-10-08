<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ExceptionSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionSubscriberTest extends KernelTestCase
{
    public function testOnException()
    {
        $request = Request::create('/api/fake', 'POST');

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $httpKernel,
            $request,
            0,
            (new \Exception('Internal Server Error!'))
        );

        (new ExceptionSubscriber())->onKernelException($event);
        $response = $event->getResponse();

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame('{"error":"Internal Server Error!"}', $response->getContent());
    }
}
