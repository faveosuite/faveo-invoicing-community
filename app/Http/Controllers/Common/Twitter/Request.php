<?php

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith.
 */

namespace App\Http\Controllers\Common\Twitter;

use Stringable;

class Request implements Stringable
{
    protected array $parameters;

    protected $httpUrl;

    public static $version = '1.0';

    /**
     * Constructor.
     *
     * @param  string  $httpMethod
     * @param  string  $httpUrl
     * @param  array|null  $parameters
     */
    public function __construct(protected $httpMethod, $httpUrl, array $parameters = [])
    {
        $parameters = array_merge(Util::parseParameters(parse_url($httpUrl, PHP_URL_QUERY)), $parameters);
        $this->parameters = $parameters;

        $this->httpUrl = $httpUrl;
    }

    /**
     * pretty much a helper function to set up the request.
     *
     * @param  Token  $token
     * @param  string  $httpMethod
     * @param  string  $httpUrl
     */
    public static function fromConsumerAndToken(
        Consumer $consumer,
        ?Token $token = null,
        $httpMethod = null,
        $httpUrl = null,
        array $parameters = []
    ): self {
        $defaults = [
            'oauth_version' => self::$version,
            'oauth_nonce' => self::generateNonce(),
            'oauth_timestamp' => time(),
            'oauth_consumer_key' => $consumer->key,
        ];
        if ($token instanceof \App\Http\Controllers\Common\Twitter\Token) {
            $defaults['oauth_token'] = $token->key;
        }

        $parameters = array_merge($defaults, $parameters);

        return new self($httpMethod, $httpUrl, $parameters);
    }

    /**
     * @param  string  $name
     * @param  string  $value
     */
    public function setParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param  $name
     * @return string|null
     */
    public function getParameter($name)
    {
        return $this->parameters[$name] ?? null;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param  $name
     */
    public function removeParameter($name): void
    {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     */
    public function getSignableParameters(): string
    {
        // Grab all parameters
        $params = $this->parameters;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return Util::buildHttpQuery($params);
    }

    /**
     * Returns the base string of this request.
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function getSignatureBaseString(): string
    {
        $parts = [
            $this->getNormalizedHttpMethod(),
            $this->getNormalizedHttpUrl(),
            $this->getSignableParameters(),
        ];

        $parts = Util::urlencodeRfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * Returns the HTTP Method in uppercase.
     */
    public function getNormalizedHttpMethod(): string
    {
        return strtoupper((string) $this->httpMethod);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path.
     */
    public function getNormalizedHttpUrl(): string
    {
        $parts = parse_url((string) $this->httpUrl);

        $scheme = $parts['scheme'];
        $host = strtolower($parts['host']);
        $path = $parts['path'];

        return sprintf('%s://%s%s', $scheme, $host, $path);
    }

    /**
     * Builds a url usable for a GET request.
     */
    public function toUrl(): string
    {
        $postData = $this->toPostdata();
        $out = $this->getNormalizedHttpUrl();
        if ($postData !== '' && $postData !== '0') {
            $out .= '?'.$postData;
        }

        return $out;
    }

    /**
     * Builds the data one would send in a POST request.
     */
    public function toPostdata(): string
    {
        return Util::buildHttpQuery($this->parameters);
    }

    /**
     * Builds the Authorization: header.
     *
     *
     * @throws TwitterOAuthException
     */
    public function toHeader(): string
    {
        $first = true;
        $out = 'Authorization: OAuth';
        foreach ($this->parameters as $k => $v) {
            if (! str_starts_with((string) $k, 'oauth')) {
                continue;
            }

            if (is_array($v)) {
                throw new TwitterOAuthException('Arrays not supported in headers');
            }

            $out .= ($first) ? ' ' : ', ';
            $out .= /* @scrutinizer ignore-type */Util::urlencodeRfc3986($k).'="'.
            /* @scrutinizer ignore-type */Util::urlencodeRfc3986($v).'"';
            $first = false;
        }

        return $out;
    }

    public function __toString(): string
    {
        return $this->toUrl();
    }

    /**
     * @param  Token  $token
     */
    public function signRequest(SignatureMethod $signatureMethod, Consumer $consumer, ?Token $token = null): void
    {
        $this->setParameter('oauth_signature_method', $signatureMethod->getName());
        $signature = $this->buildSignature($signatureMethod, $consumer, $token);
        $this->setParameter('oauth_signature', $signature);
    }

    /**
     * @param  Token  $token
     * @return string
     */
    public function buildSignature(SignatureMethod $signatureMethod, Consumer $consumer, ?Token $token = null)
    {
        return $signatureMethod->buildSignature($this, $consumer, $token);
    }

    public static function generateNonce(): string
    {
        return md5(microtime().mt_rand());
    }
}
