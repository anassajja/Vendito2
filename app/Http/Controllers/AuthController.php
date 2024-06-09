<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User; //import User model
use Illuminate\Foundation\Auth\User as Authenticatable; //import Authenticatable
use Illuminate\Support\Facades\Cookie; //import Cookie
use Illuminate\Support\Facades\Hash; //import Hash
use Illuminate\Support\Facades\Log; //import Log
use Illuminate\Support\Str; //import Str

    
class AuthController extends Controller
{

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']); //validate email
    
        $status = Password::sendResetLink( //send password reset link
            $request->only('email') //send email
        );
    
        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['status' => __($status)], 200)
                    : response()->json(['email' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        // Log the request parameters
        Log::info('Request parameters:', $request->all());

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed', //password must be at least 8 characters and must match password_confirmation
        ]);
        $status = Password::reset( //reset password
            $request->only('email', 'password', 'password_confirmation', 'token'), //reset password
            function ($user, $password) {
                $user->forceFill([ //force fill user
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60)); //set remember token

                $user->save();
            }
        );
        // Log the status
        Log::info('Password reset status:', ['status' => $status]);
        return $status == Password::PASSWORD_RESET
                    ? response()->json(['status' => __($status)], 200)
                    : response()->json(['email' => __($status)], 400);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (auth()->attempt($credentials) && auth()->user()->status == 'active') { //if user is active
            $token = auth()->user()->createToken('authToken')->plainTextToken; //create token
            $role = auth()->user()->role; //get user role
            // Set user role in a cookie
            $minutes = 60; // Set the cookie expiration time to 60 minutes
            Cookie::queue('userRole', auth()->user()->role, $minutes); // Set the user role in a cookie
            // Create a response
            $response = response()->json([
                'id' => auth()->user()->id, // Add the user's ID to the response
                'avatar' => auth()->user()->avatar, // Add the user's avatar to the response
                'token' => $token,
                'role' => $role,
                'message' => 'Login successful'
            ], 200);
            // Set the token as a cookie that expires after 48 hours
            return $response->withCookie(cookie('token', $token, 60 * 48));
        } //if login successful
        else if (auth()->attempt($credentials) && auth()->user()->status == 'inactive') { //if user is inactive
            return response()->json([
                'message' => 'User inactive'
            ], 403);
        } //if user is inactive
        else if (auth()->attempt($credentials) && auth()->user()->status == 'blocked') { //if user is blocked
            return response()->json([
                'message' => 'User blocked'
            ], 403);
        } //if user is blocked
        else {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        } //if login unsuccessful
    }

    //show users
    public function users(Request $request)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            $users = User::all(); //get all users
            if (!$users) { 
                return response()->json([
                    'message' => 'No users found'
                ], 404);
            } //if no users found
            else {
                return response()->json([
                    'message' => 'Users fetched successfully',
                    'users' => $users //return users
                ], 200);
            }

        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    //update user
    public function updateUser(Request $request, $id)
    {
        $admin = auth()->user(); //get authenticated user
    
        if ($admin->is_admin) { // assuming you have an is_admin field in your users table
            $userToUpdate = User::find($id); // find the user to update
    
            if ($userToUpdate) {
                $userToUpdate->update($request->all()); //update user
    
                return response()->json([
                    'message' => 'User updated successfully',
                    'user' => $userToUpdate
                ], 200);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    //get user by id
    public function getUserById($id)
    {
        $user = User::find($id); //find user by id
        
        if (!$user) { //if user not found
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } //if user not found
        else {
            return response()->json([
                'message' => 'User fetched successfully',
                'user' => $user
            ], 200);
        }
    }

    //delete user
    public function deleteUser(Request $request)
    {
        $user = auth()->user(); //get authenticated user
    
        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            $userToDelete = User::find($request->id); // assuming you're passing the id of the user to delete in the request
    
            if ($userToDelete) {
                $userToDelete->delete();
    
                return response()->json([
                    'message' => 'User deleted successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();  //delete user tokens

        if (!$request->bearerToken()) {
            return response()->json([
                'message' => 'User not logged in'
            ], 401);
        } //if user not logged in

        else {
            return response()->json([
                'message' => 'Logout successful'
            ], 200);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required',
            'cnie' => 'required',
            'birthdate'=> 'required',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'avatar',
            'email' => 'required|email|unique:users', //unique email in users table
            'password' => 'required',
            'role', 
            'status',
            'is_admin',
        ]);

        $user = User::create(array_merge($request->all(), ['password' => bcrypt($request->password)])); // create user

        if ($request->hasFile('avatar')) { //if request has avatar
            $avatar = $request->file('avatar'); //get avatar
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension(); //set avatar name
            $avatar->isValid() ? true : false; //check if avatar is valid
            $avatar->move(public_path('avatars'), $avatarName); //move avatar to public/avatars folder
            $user->avatar = $avatarName; //set avatar to avatar name
            $user->save(); //save user
        } else {
            return $request; //return request
            $user->avatar = ''; //set avatar to empty string
        }

        if ($request->role == 'admin') {
            $user->is_admin = true; //set is_admin to true
            $user->status = 'active'; //set status to active
            $user->save(); //save user
        } //if role is admin 

        if (!$user) {
            return response()->json([
                'message' => 'User not created'
            ], 500);
        } //if user not created
        else {
            // $user->sendEmailVerificationNotification(); //send email verification notification
            // Create a response
            $response = response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);

            return $response;
            }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user(); //get authenticated user
    
            if($user) {
                if ($request->hasFile('avatar')) { //if request has avatar
                    $avatar = $request->file('avatar'); //get avatar
                    $avatarName = time() . '.' . $avatar->getClientOriginalExtension(); //set avatar name
                    $avatar->isValid() ? true : false; //check if avatar is valid
                    $avatar->move(public_path('avatars'), $avatarName); //move avatar to public/avatars folder
                    $user->avatar = $avatarName; //set avatar to avatar name
                }
    
                if ($request->has('password')) { //if request has password
                    $user->password = Hash::make($request->password); //hash and set password
                }
    
                $user->save(); //save user
                $user->update($request->except(['avatar', 'password'])); //update user
    
                return response()->json([
                    'message' => 'Profile updated successfully',
                    'user' => $user
                ], 200);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function blockUser(Request $request)
    {
        $user = auth()->user(); //get authenticated user
    
        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            $userToBlock = User::find($request->id); // assuming you're passing the id of the user to block in the request
    
            if ($userToBlock) {
                // Export user data to a JSON file
                $userData = json_encode($userToBlock->toArray()); //convert user data to JSON
                $directoryPath = public_path("blocked_users");
                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0777, true);
                }
                file_put_contents("{$directoryPath}/{$userToBlock->id}.json", $userData); //save user data to a JSON file
                $userToBlock->update(['status' => 'blocked']); //update user status to blocked
                $userToBlock->delete(); //delete user
    
                return response()->json([
                    'message' => 'User blocked successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        }
    }

    public function unblockUser(Request $request)
    {
        $user = auth()->user(); //get authenticated user
    
        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            // Import user data from a JSON file
            $userData = json_decode(file_get_contents(public_path("blocked_users/{$request->id}.json")), true); //get user data from JSON file
    
            if ($userData) { //if user data exists
                $userData['password'] = bcrypt('password'); // set default password
                $userData['status'] = 'inactive'; // set status to inactive
                
                $userToUnblock = User::create($userData); //create user from JSON data
    
                unlink(public_path("blocked_users/{$request->id}.json")); //delete JSON file
    
                return response()->json([
                    'message' => 'User unblocked successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        }
    }

    public function acceptUser(Request $request)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            $userToAccept = User::find($request->id); // assuming you're passing the id of the user to accept in the request

            if ($userToAccept) {
                $userToAccept->update(['status' => 'active']); //update user status to active

                return response()->json([
                    'message' => 'User accepted successfully'
                ], 200);
            } else if (!$userToAccept) { //if user not found
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            else {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }
    }

   public function rejectUser(Request $request)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->is_admin) { // assuming you have an is_admin field in your users table
            $userToReject = User::find($request->id); // assuming you're passing the id of the user to reject in the request

            if ($userToReject) {
                $userToReject->update(['status' => 'inactive']); //update user status to inactive

                return response()->json([
                    'message' => 'User rejected successfully'
                ], 200);
            } else if (!$userToReject) { //if user not found
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            else {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }
    }
}
