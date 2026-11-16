<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return match (auth()->user()->role) {
            'owner'    => redirect()->route('owner.home'),
            'karyawan' => redirect()->route('karyawan.home'),
            'kasir'    => redirect()->route('kasir.home'),
            default    => redirect()->route('beranda'),
        };
    }
}