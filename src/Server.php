<?php

namespace My\Coded\PHPServer;

use Exception;

class Server
{
    protected $host = null;
    protected $port = null;
    protected $socket = null;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->createSocket();

        $this->bind();
    }

    protected function createSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }

    protected function bind()
    {
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            throw new Exception('Could Not bind: ' . $this->host . ':' . $this->port . ' - ' . socket_strerror(socket_last_error()));
        }
    }

    public function  listen($callback)
    {
        // check if callable
        if (!is_callable($callback)) {
            throw new  Exception("Given argument should be callable");
        }

        while (1) {
            // listen
            socket_listen($this->socket);

            // get client socket
            $client = socket_accept($this->socket);

            // if false, we get error, close socket and continue
            if (!$client) {
                socket_close($client);
                continue;
            }

            // new request instance with client header
            $request = Request::withHeaderString(socket_read($client, 1024));

            // execute the callback
            $response = call_user_func($callback, $request);

            // if no response, then return 404 response object
            if (!$response || !$response instanceof Response) {
                $response = Response::error(404);
            }

            // convert response to string
            $response = (string) $response;

            // write response to client socket
            socket_write($client, $response, strlen($response));

            // close to except new
            socket_close($client);
        }
    }
}
