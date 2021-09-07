<?php

class SMailer
{
    const EOL = "\r\n";
    const TIMEOUT = 10;

    protected $socket;

    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $params;

    public function __construct($host, $port, $username, $password, $params = [])
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->params   = $params;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    protected function disconnect()
    {
        if (is_resource($this->socket)) {
            $this->send("QUIT");
            $this->receive();
            fclose($this->socket);
        }
    }

    protected function send($request)
    {
        $result = fwrite($this->socket, $request . self::EOL);
        if ($result === false) {
            throw new RuntimeException('Could not send request');
        }
        return $result;
    }

    protected function receive()
    {
        $response = fread($this->socket, 8192);
        if ($response === false) {
            throw new RuntimeException('Could not read');
        }
        return $response;
    }

    protected function code($response)
    {
        return substr($response, 0, 3);
    }

    protected function createBaseHeaders($to, $from, $subject, $extraHeaders)
    {
        return [
            'Subject'      => '=?UTF-8?B?' . base64_encode($subject) . '?=',
            'To'           => $to,
            'From'         => $from,
            'Date'         => time(),
            'MIME-Version' => '1.0',
        ];
    }

    public function createTextMessage($to, $from, $subject, $content, $extraHeaders=[])
    {
        $headers = $this->createBaseHeaders($to, $from, $subject, $extraHeaders);
        $headers = array_merge($headers, [
            'Content-Type'              => 'text/plain; charset=utf-8',
            'Content-Transfer-Encoding' => '8bit',
        ]);

        $body = '';
        foreach ($headers as $k => $v) {
            $body .= $k . ': ' . $v . self::EOL;
        }
        $body .= $content . self::EOL;
        return $body;
    }

    public function createHTMLMessage($to, $from, $subject, $content, $extraHeaders=[])
    {
        $headers = $this->createBaseHeaders($to, $from, $subject, $extraHeaders);
        $headers = array_merge($headers, [
            'Content-Type'              => 'text/html; charset=utf-8',
            'Content-Transfer-Encoding' => 'base64',
        ]);

        $body = '';
        foreach ($headers as $k => $v) {
            $body .= $k . ': ' . $v . self::EOL;
        }
        $body .= base64_encode($content) . self::EOL;
        return $body;
    }

    public function sendMail($to, $from, $message)
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }

        $this->send("MAIL FROM: <$from>");
        $this->receive();
        $this->send("RCPT TO: <$to>");
        $this->receive();

        $this->send("DATA");
        $this->receive();

        $this->send($message);
        $this->send(".");
        $results = $this->receive();

        return substr($results, 0, 3);
    }

    protected function connect()
    {
        set_error_handler(
            function ($error, $message = '') {
                throw new RuntimeException(sprintf("Could not open socket: %s", $message), $error);
            },
            E_WARNING
        );
        $address = sprintf("tcp://%s:%d", $this->host, $this->port);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer_name' => false,
                'verify_peer'      => false,
            ]]);

        $errorCode = 0;
        $errorMessage = "Could not open socket";
        $this->socket = stream_socket_client($address, $errorCode,
            $errorMessage, self::TIMEOUT, STREAM_CLIENT_CONNECT, $context);
        restore_error_handler();

        if ($this->socket === false) {
            throw new RuntimeException($errorMessage);
        }

        if (stream_set_timeout($this->socket, self::TIMEOUT) === false) {
            throw new RuntimeException("Could not set stream timeout");
        }

        $this->receive();

        $this->send("EHLO $this->host");
        $this->receive();

        $this->send("STARTTLS");
        $this->receive();

        if (!stream_socket_enable_crypto($this->socket, true,
            STREAM_CRYPTO_METHOD_SSLv3_CLIENT)) {
            throw new RuntimeException("Unable to connect via TLS");
        }

        $this->send("EHLO $this->host");
        $this->receive();

        $this->send("AUTH LOGIN");
        $this->receive();
        $this->send(base64_encode($this->username));
        $this->receive();
        $this->send(base64_encode($this->password));
        $this->receive();

        return $this->socket;
    }

}