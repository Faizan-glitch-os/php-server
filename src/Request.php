<?php

namespace My\Coded\PHPServer;

class Request
{
    protected $headers = [];
    protected $method = null;
    protected $uri = null;
    protected $parameters = [];

    public function __construct($method, $uri, $headers)
    {
        $this->method = strtoupper($method);
        $this->headers = $headers;

        [$this->uri, $params] = explode('?', $uri);

        parse_str($params, $this->parameters);
    }


    public static function withHeaderString($header)
    {
        // convert string to array
        $lines = explode("\n", $header);

        // extract method, uri and protocol
        $request_line = array_shift($lines);

        // only method and uri
        [$method, $uri] = explode(' ', $request_line);

        $headers = [];

        foreach ($lines as $line) {
            // clean the line
            $line = trim($line);

            if (strpos($line, ': ') !== false) {
                [$key, $value] = explode(': ', $line);

                $headers[$key] = $value;
            }
        }
        return new static($method, $uri, $headers);
    }

    // Getters for uri, method, header and param
    public function uri()
    {
        return $this->uri;
    }

    public function method()
    {
        return $this->method;
    }

    public function header($key, $default = null)
    {
        if (!isset($this->headers[$key])) {
            return $default;
        }

        return $this->headers[$key];
    }

    public function param($key, $default = null)
    {
        if (!isset($this->parameters[$key])) {
            return $default;
        }

        return $this->parameters[$key];
    }
}
