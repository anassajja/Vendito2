<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\User;
use App\Models\Offer;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Arr;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * Test the store function.
     */
    public function test_store_function()
    {
        $user = User::factory()->create(); // Create a new user

        $this->actingAs($user); // Authenticate the user

        $offerData = [ // Create an array of data to send to the API
            'title' => 'Test Title', // Add the title
            'description' => 'Test Description',
            'price' => 100,
            'image' => UploadedFile::fake()->image('offer.jpg'), // Add the image
            'category' => 'Test Category',
            'location' => 'Test Location',
            'condition' => 'Test Condition',
            'delivery' => 'Test Delivery',
            'negotiable' => 1, // or 0 if not negotiable
            'phone' => '1234567890',
            'status' => 'accepted',
            'user_id' => $user->id,
        ];

        $response = $this->postJson('http://127.0.0.1:8000/api/createOffer', $offerData); // Send a POST request to the API

        $response->assertStatus(201) // Check if the response status is 201
            ->assertJson([
                'message' => 'Offer created successfully',
                'offer' => Arr::except($offerData, ['image']) // Exclude the 'image' field
            ])
            ->assertJsonPath('offer.status', 'accepted'); // Add this line to check the status of the offer

        $this->assertContains($response['offer']['status'], ['accepted', 'unaccepted']); // Check if the status is one of the accepted values

        $this->assertDatabaseHas('offers', Arr::except($offerData, ['image'])); // Exclude the 'image' field
    }
}