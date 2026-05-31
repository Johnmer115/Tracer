<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function routePrefix(): string
    {
        return match (auth()->user()?->usertype) {
            'Staff_OSA' => 'staff_osa',
            'Branch_OSA' => 'branch_osa',
            default => 'dean_osa',
        };
    }

    protected function routeName(string $name): string
    {
        return $this->routePrefix() . '.' . $name;
    }
}
