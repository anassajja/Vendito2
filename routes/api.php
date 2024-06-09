<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//auth Tested successfully
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'users']); //show all users
Route::get('/users/{id}', [AuthController::class, 'getUserById']); //get user by id
Route::middleware('auth:sanctum')->put('/update/{id}', [AuthController::class, 'updateUser']); //update user
Route::middleware('auth:sanctum')->put('/updateProfile', [AuthController::class, 'updateProfile']); //update user profile
Route::middleware('auth:sanctum')->delete('/delete/{id}', [AuthController::class, 'deleteUser']); //delete user
//Route::delete('/delete/{id}', [AuthController::class, 'delete']); //delete user
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/sendotp', [AuthController::class, 'SendOTP']);
Route::middleware('auth:sanctum')->delete('/block/{id}', [AuthController::class, 'blockUser']); //block user
Route::middleware('auth:sanctum')->post('/unblock/{id}', [AuthController::class, 'unblockUser']); //unblock user
Route::middleware('auth:sanctum')->put('/activate/{id}', [AuthController::class, 'acceptUser']); //activate user
Route::middleware('auth:sanctum')->put('/desactivate/{id}', [AuthController::class, 'rejectUser']); //deactivate user
// Route::post('/verifyotp', [AuthController::class, 'verifyOTP']);
// Route::post('/resetpassword', [AuthController::class, 'ResetPassword']);

//offers Tested successfully
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{id}', [OfferController::class, 'show']);
Route::middleware('auth:sanctum')->post('/createOffer', [OfferController::class, 'store']);
Route::middleware('auth:sanctum')->put('/updateOffer/{id}', [OfferController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/deleteOffer/{id}', [OfferController::class, 'delete']);
Route::middleware('auth:sanctum')->get('/user/{id}/offers', [OfferController::class, 'userOffers']); //get user offers
Route::middleware('auth:sanctum')->get('/offers/category/{category}', [OfferController::class, 'categoryOffers']); //get offers by category
Route::middleware('auth:sanctum')->get('/offers/location/{location}', [OfferController::class, 'locationOffers']); //get offers by location
Route::middleware('auth:sanctum')->put('/offers/accept/{id}', [OfferController::class, 'acceptOffer']); //accept offer
Route::middleware('auth:sanctum')->put('/offers/reject/{id}', [OfferController::class, 'denyOffer']); //reject offer
Route::middleware('auth:sanctum')->delete('/offers/archive/{id}', [OfferController::class, 'archiveOffer']); //archive offer


//Contact  Tested successfully

Route::middleware('auth:sanctum')->get('/contacts', [ContactController::class, 'index']);
Route::middleware('auth:sanctum')->get('/contacts/{id}', [ContactController::class, 'show']);
Route::post('/createContact', [ContactController::class, 'store']);
Route::middleware('auth:sanctum')->put('/updateContact/{id}', [ContactController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/deleteContact/{id}', [ContactController::class, 'delete']);

// Filter Tested successfully

Route::get('/offers/filter/price/min/{minPrice}', [OfferController::class, 'filterByMinPrice']);
Route::get('/offers/filter/price/max/{maxPrice}', [OfferController::class, 'filterByMaxPrice']);
Route::get('/offers/filter/category/{category}', [OfferController::class, 'filterByCategory']);
Route::get('/offers/filter/location/{location}', [OfferController::class, 'filterByLocation']);

// Search Tested successfully
Route::get('/offers/search/title/{title}', [OfferController::class, 'searchByTitle']);
Route::get('/offers/search/description/{description}', [OfferController::class, 'searchByDescription']);
Route::get('/offers/search/price/{price}', [OfferController::class, 'searchByPrice']);
Route::get('/offers/search/category/{category}', [OfferController::class, 'searchByCategory']);
Route::get('/offers/search/location/{location}', [OfferController::class, 'searchByLocation']);
Route::get('search', [OfferController::class, 'search']);


// Forgot Password
Route::post('password/email', [AuthController::class, 'sendPasswordResetLink']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);

/* // Server reload
Route::get('/server-load', function () { //get server load
    $load = sys_getloadavg(); //get the server load
    return response()->json(['load' => $load[0]]); //return the load
});
 */