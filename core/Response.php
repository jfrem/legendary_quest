<?php
// core/Response.php

class Response
{
    public static function json($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data);
        exit();
    }

    public static function error($message, $status = 400)
    {
        self::json(["message" => $message], $status);
    }
}
