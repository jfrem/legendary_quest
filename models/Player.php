<?php
// models/Player.php

class Player
{
    private $conn;
    private $table_name = "players";

    public function __construct($db)
    {
        $this->conn = $db;
    }
}
