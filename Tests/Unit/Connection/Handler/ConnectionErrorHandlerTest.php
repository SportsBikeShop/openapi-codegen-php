<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Tests\Unit\Connection\Handler;

use GuzzleHttp\Ring\Future\CompletedFutureArray;
use PHPUnit\Framework\TestCase;
use \Exception;
use Elastic\OpenApi\Codegen\Connection\Handler\ConnectionErrorHandler;
use Elastic\OpenApi\Codegen\Exception as CodegenException;
use Elastic\OpenApi\Codegen\Exception\ConnectionException;
use Elastic\OpenApi\Codegen\Exception\CouldNotResolveHostException;
use Elastic\OpenApi\Codegen\Exception\CouldNotConnectToHostException;
use Elastic\OpenApi\Codegen\Exception\OperationTimeoutException;

/**
 * Check connection error are turns into comprehensive exceptions by the handler.
 *
 * @package Elastic\OpenApi\Codegen\Test\Unit\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class ConnectionErrornHandlerTest extends TestCase
{
    /**
     * Check the exception is thrown when needed.
     *
     * @dataProvider errorDataProvider
     */
    public function testExceptionTypes($response, $exceptionClass, $exceptionMessage)
    {
        if (null != $exceptionClass) {
            $this->expectException($exceptionClass);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $handler = new ConnectionErrorHandler(
            function () use ($response) {
                return new CompletedFutureArray($response);
            }
        );

        $handlerResponse = $handler(array())->wait();

        if (null == $exceptionClass) {
            $this->assertEquals($response, $handlerResponse);
        }
    }

    /**
     * @return array
     */
    public function errorDataProvider()
    {
        $data = array(
          array(
            array('error' => new Exception('Unknown exception')),
            "Elastic\OpenApi\Codegen\Exception\ConnectionException",
            'Unknown exception',
          ),
          array(
            array('error' => new Exception('Unknown exception'), 'curl' => array()),
            "Elastic\OpenApi\Codegen\Exception\ConnectionException",
            'Unknown exception',
          ),
          array(
            array('error' => new Exception('Could not resolve host'), 'curl' => array('errno' => CURLE_COULDNT_RESOLVE_HOST)),
            "Elastic\OpenApi\Codegen\Exception\CouldNotResolveHostException",
            'Could not resolve host',
          ),
          array(
            array('error' => new Exception('Could not connect to host'), 'curl' => array('errno' => CURLE_COULDNT_CONNECT)),
            "Elastic\OpenApi\Codegen\Exception\CouldNotConnectToHostException",
            'Could not connect to host',
          ),
          array(
            array('error' => new Exception('Timeout exception'), 'curl' => array('errno' => CURLE_OPERATION_TIMEOUTED)),
            "Elastic\OpenApi\Codegen\Exception\OperationTimeoutException",
            'Timeout exception',
          ),
          array(
            array('foo' => 'bar'),
            null,
            null,
          ),
        );

        return $data;
    }
}
