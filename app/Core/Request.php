<?php

namespace App\Core;

class Request
{
    private $data = [];
    private $method;
    private $contentType;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $this->parseInput();
    }

    private function parseInput()
    {
        if ($this->method === 'GET') {
            $this->data = $_GET;
        } elseif ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'PATCH' || $this->method === 'DELETE') {
            if (strpos($this->contentType, 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $this->data = json_decode($input, true) ?? [];
            } else {
                $this->data = $_POST;
            }
        }
    }

    public function all()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function only($keys)
    {
        $result = [];
        foreach ((array) $keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->get($key);
            }
        }
        return $result;
    }

    public function except($keys)
    {
        $result = $this->data;
        foreach ((array) $keys as $key) {
            unset($result[$key]);
        }
        return $result;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getFiles()
    {
        return $_FILES;
    }

    public function file($name)
    {
        return $_FILES[$name] ?? null;
    }

    public function getHeaders()
    {
        return getallheaders();
    }

    public function getHeader($name)
    {
        $headers = getallheaders();
        return $headers[$name] ?? null;
    }
}
