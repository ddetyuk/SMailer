<?php

class SMailer
{
    const EOL = "\r\n";
    const TIMEOUT = 10;

    protected $socket;

    protected $params = [
        'ssl' => [
            'verify_peer_name' => false,
            'verify_peer' => false,
        ],
        'server' => [
            'host' => '',
            'port' => '',
        ],
        'auth' => [
            'username' => '',
            'password' => '',
        ]
    ];

    public function __construct($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    protected function disconnect()
    {
        if (is_resource($this->socket)) {
            $this->_send("QUIT");
            $this->_receive();
            fclose($this->socket);
        }
    }

    protected function _send($request)
    {
        $result = fwrite($this->socket, $request . self::EOL);
        if ($result === false) {
            throw new RuntimeException('Could not send request');
        }
        return $result;
    }

    protected function _receive()
    {
        $response = fread($this->socket, 8192);
        if ($response === false) {
            throw new RuntimeException('Could not read');
        }
        return $response;
    }

    public function send($to, $from, $subject, $body, $headers = [])
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }

        $this->_send("MAIL FROM: <".$this->params['auth']['username'].">");
        $this->_receive();
        $this->_send("RCPT TO: <$to>");
        $this->_receive();

        $this->_send("DATA");
        $this->_receive();

        $message = array_merge($headers, [
            'Subject' => '=?UTF-8?B?' . base64_encode($subject) . '?=',
            'To' => "<" . $to . ">",
            'From' => $from,
            'Date' => time(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Transfer-Encoding' => 'base64',
        ]);

        foreach ($message as $k => $v) {
            $this->_send($k . ': ' . $v);
        }
        $this->_send(base64_encode($body));
        $this->_send(".");
        $results = $this->_receive();

        return substr($results, 0,3);
    }

    protected function connect()
    {
        set_error_handler(
            function ($error, $message = '') {
                throw new RuntimeException(sprintf("Could not open socket: %s", $message), $error);
            },
            E_WARNING
        );
        $address = sprintf("tcp://%s:%d", $this->params['server']['host'], $this->params['server']['port']);
        $context = stream_context_create($this->params);

        $errorCode = 0;
        $errorMessage = "Could not open socket";
        $this->socket = stream_socket_client($address, $errorCode, $errorMessage, self::TIMEOUT, STREAM_CLIENT_CONNECT, $context);
        restore_error_handler();

        if ($this->socket === false) {
            throw new RuntimeException($errorMessage);
        }

        if (stream_set_timeout($this->socket, self::TIMEOUT) === false) {
            throw new RuntimeException("Could not set stream timeout");
        }

        $this->_receive();

        //helo
        $this->_send("EHLO SMAIL");
        $this->_receive();

        //tls
        $this->_send("STARTTLS");
        $this->_receive();


        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT)) {
            throw new RuntimeException("Unable to connect via TLS");
        }

        $this->_send("EHLO SMAIL");
        $this->_receive();

        //auth
        $this->_send("AUTH LOGIN");
        $this->_receive();
        $this->_send(base64_encode($this->params['auth']['username']));
        $this->_receive();
        $this->_send(base64_encode($this->params['auth']['password']));
        $this->_receive();

        return $this->socket;
    }

}