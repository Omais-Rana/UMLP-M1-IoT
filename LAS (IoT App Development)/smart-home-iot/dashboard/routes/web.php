<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('This is a test email from your Smart Home Dashboard.', function ($message) {
            $message->to('omaisrana439@gmail.com')
                ->subject('Test Email Connection');
        });
        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Error sending email: ' . $e->getMessage();
    }
});
