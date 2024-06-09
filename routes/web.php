<?php

use Illuminate\Support\Facades\Route;
use App\Mail\MyTestEmail;
use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testroute', function() {
    $name = "Funny Coder";

    // The email sending is done using the to method on the Mail facade
    Mail::to('kohahim869@acname.com')->send(new MyTestEmail($name));

    return "Email has been sent successfully!";
});

//offers images
Route::get('/offers/images/{filename}', function ($filename) //get offer images
{
    $path = public_path('images/' . $filename); //path to images folder

    if (!File::exists($path)) { //if file does not exist
        abort(404); //return 404 error
    }

    $file = File::get($path); //get file
    $type = File::mimeType($path); // get file type
 
    $response = Response::make($file, 200); //make response
    $response->header("Content-Type", $type); //set header

    return $response;
});

//user avatar
Route::get('/users/avatar/{filename}', function ($filename)
{
    $path = public_path('avatars/' . $filename);

    if (!File::exists($path)) { // if file does not exist
        abort(404); // return 404 error
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

