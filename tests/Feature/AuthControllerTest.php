<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_user_with_valid_email() // Test the register function
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com', // Valid email
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData); // Send a POST request to the API

        $response->assertStatus(200); // 200 OK
        $this->assertDatabaseHas('users', [ // Check if the user is in the database
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
        ]);
    }

    /** @test */
    public function it_does_not_register_a_user_with_invalid_email()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'example.com', // Invalid email
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData); // Send a POST request to the API

        $response->assertStatus(422); // 422 Unprocessable Entity
        $this->assertDatabaseMissing('users', [ // Check if the user is not in the database
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'example.com', // Invalid email
        ]);
    }

    /** @test */
    public function it_registers_a_user_with_valid_avatar()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => public_path('avatars/1715476830.jpg'), // Use an existing avatar file
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData);

        $response->assertStatus(200); // Expect a 200 status code

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
        ]);
    }

    public function it_does_not_register_a_user_with_invalid_avatar()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => 'https://i.pravatar.cc', // Invalid avatar
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData); // Send a POST request to the API

        $response->assertStatus(422); // 422 Unprocessable Entity
        $this->assertDatabaseMissing('users', [ // Check if the user is not in the database
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
        ]);
    }

    /** @test */
    public function it_registers_a_user_with_valid_password_and_confirmation()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
            'password' => 'password', // Valid password
            'password_confirmation' => 'password', // Password confirmation matches password
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData);

        $response->assertStatus(200); // Expect a 200 status code

        $this->assertDatabaseHas('users', [ // Check if the user is in the database
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
        ]);
    }

    public function it_does_not_register_a_user_with_unmatched_password_and_confirmation()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'different_password', // Password confirmation does not match password
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/register', $userData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'cnie' => '123456789',
            'phone' => '0612345678',
            'address' => '123 Main St, Apt 4',
            'city' => 'Casablanca',
            'birthdate' => '1999-01-01',
            'email' => 'john.doe@gmail.com',
        ]);
    }
}