<?php

use App\Livewire\WelcomeScreen;
use App\Livewire\IdentityList;
use App\Livewire\AddIdentity;
use App\Livewire\IdentityDetail;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeScreen::class);
Route::get('/identities', IdentityList::class);
Route::get('/identities/add', AddIdentity::class);
Route::get('/identities/{index}', IdentityDetail::class);
