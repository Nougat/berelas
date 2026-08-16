<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/kleinanzeigen', 'pages::kleinanzeigen');
Route::livewire('/großhandel', 'pages::großhandel');
Route::livewire('/aufgaben', 'pages::tasks');
Route::livewire('/apple', 'pages::apple');
Route::livewire('/inventory', 'pages::inventory');