<?php

namespace App\Http\Controllers;

class HealthcheckController extends Controller
{
    /**
     * @return string[]
     */
    public function index(): array
    {
        return ['status' => 'ok'];
    }
}
