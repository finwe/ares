<?php declare(strict_types=1);

namespace h4kuna\Ares\Http;

use h4kuna\Ares\Exceptions\ServerResponseException;
use Nette\Utils\Json;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class TransportProvider
{

	private RequestFactoryInterface $requestFactory;
	private ClientInterface $client;
	private StreamFactoryInterface $streamFactory;

	public function __construct(RequestFactoryInterface $requestFactory, ClientInterface $client, StreamFactoryInterface $streamFactory)
	{
		$this->requestFactory = $requestFactory;
		$this->client = $client;
		$this->streamFactory = $streamFactory;
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface|string $url
	 */
	public function response($url): ResponseInterface
	{
		$request = $url instanceof RequestInterface ? $url : $this->createRequest($url);
		try {
			$response = $this->client->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new ServerResponseException($e->getMessage(), $e->getCode(), $e);
		}

		return $response;
	}


	public function createRequest(string $url, string $method = 'GET'): RequestInterface
	{
		return $this->requestFactory->createRequest($method, $url)
			->withHeader('X-Powered-By', 'h4kuna/ares');
	}


	/**
	 * @param array<string, mixed> $data
	 */
	public function createJsonRequest(string $url, array $data = []): RequestInterface
	{
		$request = $this->createPost($url, 'application/json');
		if ($data !== []) {
			$request = $request->withBody($this->streamFactory->createStream(Json::encode($data)));
		}

		return $request;
	}


	/**
	 * @param string|\Psr\Http\Message\StreamInterface $body
	 */
	public function createXmlRequest(string $url, $body): RequestInterface
	{
		if (is_string($body)) {
			$body = $this->streamFactory->createStream($body);
		}

		return $this->createPost($url, 'application/xml')
			->withBody($body);
	}


	private function createPost(string $url, string $contentType): RequestInterface
	{
		return $this->createRequest($url, 'POST')
			->withHeader('Content-Type', "$contentType; charset=utf-8");
	}

}
