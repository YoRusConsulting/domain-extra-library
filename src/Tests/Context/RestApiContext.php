<?php

namespace AppInWeb\DomainExtraLibrary\Tests\Context;

use Behat\Behat\Context\Context;
use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;

/**
 * RestApiContext
 *
 * @uses \Context
 */
class RestApiContext implements Context
{
    /** @var RestApiBrowser $restApiBrowser */
    private $restApiBrowser;

    /**
     * @param RestApiBrowser $restApiBrowser
     */
    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
    }

    /**
     * @param string $header
     * @param string $value
     *
     * @throws \Exception
     *
     * @Then the response header :header should be equal to :value
     */
    public function theResponseHeaderShouldBeEqualTo(string $header, string $value): void
    {
        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = implode(',', $response->getHeader($header));

        if ($headerInResponse !== $value) {
            throw new \Exception(sprintf('Response header value is equals to %s', $headerInResponse));
        }
    }

    /**
     * @param string $header
     *
     * @throws \Exception
     *
     * @Then the response header :header should exist
     */
    public function theResponseHeaderShouldExist(string $header): void
    {
        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = $response->getHeader($header);

        if (empty($headerInResponse)) {
            throw new \Exception(sprintf('Response header value does not contain `%s` key', $header));
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $header
     *
     * @throws \Exception
     *
     * @Then I send a :method request to :url with :header header value retrieved from resource just created before
     */
    public function iSendARequestToWithHeaderValueRetrievedFromResourceJustCreatedBefore(string $method, string $url, string $header): void
    {
        $this->theResponseHeaderShouldExist($header);

        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = implode(',', $response->getHeader($header));

        $replacedUrl = sprintf($url, $headerInResponse);
        $this->restApiBrowser->sendRequest($method, $replacedUrl);
    }

    /**
     * Checks, whether the response content is null or empty string
     *
     * @throws \Exception
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual = $this->restApiBrowser->getResponse()->getBody()->getContents();
        $message = "The response of the current page is not empty, it is: $actual";

        if (null !== $actual && "" !== $actual) {
            throw new \Exception($message);
        }
    }
}
