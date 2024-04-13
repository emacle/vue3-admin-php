<?php

namespace App\Libraries;

use Medoo\Medoo;
use Config\Database;

class MedooService
{
    protected $medoo;

    public function __construct()
    {
        $dbConfig = new Database();
        $this->medoo = new Medoo($dbConfig->medoodb);
    }

    public function getMedoo()
    {
        return $this->medoo;
    }
}