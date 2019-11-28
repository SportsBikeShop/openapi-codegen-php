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
use Elastic\OpenApi\Codegen\Connection\Handler\ResponseSerializationHandler;
use Elastic\OpenApi\Codegen\Serializer\SmartSerializer;

/**
 * Unit tests for the response serialization handler.
 *
 * @package Elastic\OpenApi\Codegen\Test\Unit\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class ResponseSerializationHandlerTest extends TestCase
{
    /**
     * Check data unserialization accross various response of the dataprovider.
     *
     * @dataProvider requestDataProvider
     */
    public function testBodyContent($body, $expectedResult)
    {
        if ($expectedResult instanceof \Exception) {
            $this->expectException(get_class($expectedResult));
        }

        $handler = $this->getHandler($body);
        $result = $handler(array())->wait();

        if (!$expectedResult instanceof \Exception) {
            $this->assertEquals($expectedResult, $result['body']);
        }
    }

    /**
     * @return array
     */
    public function requestDataProvider()
    {
        $data = array(
            array('{"foo": "bar"}', array('foo' => 'bar')),
            array('["foo", "bar"]', array('foo', 'bar')),
            array('{}', array()),
            array('[]', array()),
            // @todo : try invalid response and exception
        );

        return $data;
    }

    /**
     * @return \Elastic\OpenApi\Codegen\Connection\Handler\RequestSerializationHandler
     */
    private function getHandler($body)
    {
        $handler = function () use ($body) {
            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $body);
            rewind($stream);
            $headers = array('content_type' => 'application/json');

            return new CompletedFutureArray(array('body' => $stream, 'transfer_stats' => $headers));
        };

        $serializer = $this->getSerializer();

        return new ResponseSerializationHandler($handler, $serializer);
    }

    /**
     * @return \Elastic\OpenApi\Codegen\Serializer\SmartSerializer
     */
    private function getSerializer()
    {
        return new SmartSerializer();
    }
}
