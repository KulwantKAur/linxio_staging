<?php

namespace App\Tests\Behat\Support;

use Fesor\JsonMatcher\JsonMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

trait RequestTrait
{
    private $baseUrl;
    private $authToken;
    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * @return KernelInterface
     */
    abstract public function getKernel();

    /**
     * @param string $token
     * @return $this
     */
    protected function authorized($token)
    {
        $this->authToken = $token;
        return $this;
    }

    /**
     * @param string $url - relative URL
     * @param array $headers
     * @return $this
     */
    protected function get(string $url, array $headers = [], $external = false)
    {
        return $this->sendRequest('GET', $url, $headers, null, $external);
    }

    /**
     * @param string $url - relative URL
     * @param null|string|array|object $data - if array of object passed, it will be serializer to JSON
     * @param array $headers
     * @param array $files
     * @return $this
     */
    protected function post(string $url, $data = null, array $headers = [], array $files = [])
    {
        return $this->sendRequest('POST', $url, $headers, $data, false, $files);
    }

    /**
     * @param string $url - relative URL
     * @param null|string|array|object $data - if array of object passed, it will be serializer to JSON
     * @param array $headers
     * @return $this
     */
    protected function put(string $url, $data = null, array $headers = [])
    {
        return $this->sendRequest('PUT', $url, $headers, $data);
    }

    /**
     * @param string $url - relative URL
     * @param null|string|array|object $data - if array of object passed, it will be serializer to JSON
     * @param array $headers
     * @return $this
     */
    protected function patch(string $url, $data = null, array $headers = [])
    {
        return $this->sendRequest('PATCH', $url, $headers, $data);
    }

    /**
     * @param string $url - relative URL
     * @param array $headers
     * @return $this
     */
    protected function delete(string $url, array $headers = [])
    {
        return $this->sendRequest('DELETE', $url, $headers);
    }

    private function sendRequest($method, $url, array $headers, $data = null, $external = false, $files = [])
    {
        $body = null;
        $requestBody = [];
        if (!in_array($method, ['GET', 'DELETE'])) {
            $body = $this->buildRequestBody($data);
        }
        if ($headers['CONTENT_TYPE'] && $headers['CONTENT_TYPE'] === 'multipart/form-data') {
            $requestBody = $data;
        }

        $headers = $this->prepareHeaders($headers);

        $request = Request::create(
            sprintf('%s' . ($external ? '' : '/') . '%s', rtrim($this->baseUrl, '/'), ltrim($url, '/')),
            $method,
            $requestBody, [], $files,
            $headers,
            $body
        );
        $kernel = $this->getKernel();
        $this->lastResponse = $kernel->handle($request);
        if (in_array(DoctrineHelperTrait::class, class_uses($this))) {
            $this->rememberToReloadEntities();
        }
        return $this;
    }

    private function prepareHeaders(array $headers)
    {
        if (!array_key_exists('CONTENT_TYPE', $headers)) {
            $headers['CONTENT_TYPE'] = 'application/json';
        }
        if ($this->authToken) {
            $headers['HTTP_Authorization'] = sprintf('Bearer %s', $this->authToken);
        }
        return $headers;
    }

    private function buildRequestBody($data)
    {
        if (is_string($data)) {
            return $data;
        }
        if (is_null($data)) {
            return null;
        }
        return $this->getContainer()->get('serializer')->serialize($data, 'json');
    }

    protected function responseContentShouldContains($expected)
    {
        $actual = $this->lastResponse ?
            $this->lastResponse->getContent() : '';
        if (strpos($actual, $expected) === false) {
            throw new \RuntimeException(sprintf(
                'Expected content "%s", instead "%s" given',
                $expected, $actual
            ));
        }
        return $this;
    }

    protected function responseCodeShouldBeOrContent($expectedCode)
    {
        $actualCode = $this->lastResponse ?
            $this->lastResponse->getStatusCode() : 0;
        if ($actualCode !== $expectedCode) {
            throw new \RuntimeException($this->getResponse()->getContent());
        }
        return $this;
    }

    protected function responseCodeShouldBe($expectedCode)
    {
        $actualCode = $this->lastResponse ?
            $this->lastResponse->getStatusCode() : 0;
        if ($actualCode !== $expectedCode) {
            print_r($this->lastResponse->getContent());
            throw new \RuntimeException(sprintf(
                'Expected status code "%s", instead "%s" given',
                $expectedCode, $actualCode
            ));
        }
        return $this;
    }

    protected function jsonResponse(array $excludedKeys = null)
    {
        if (null === $excludedKeys) {
            $excludedKeys = ['id', 'created_at', 'updated_at'];
        }
        // todo: check content type of response
        return JsonMatcher::create(
            $this->lastResponse->getContent(),
            $excludedKeys
        );
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        return json_decode($this->lastResponse->getContent(), true);
    }

    /**
     * @return string
     */
    public function getResponseContent(): string
    {
        return $this->lastResponse->getContent();
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {
        $this->authToken = null;
        $this->lastResponse = null;
    }

    /**
     * @Then /^[R,r]esponse code is (\d+)$/
     */
    public function responseCodeIs($code)
    {
        $this->responseCodeShouldBe((int)$code);
    }

    public function getAuthToken()
    {
        return $this->authToken;
    }
}