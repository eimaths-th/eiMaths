<?php

namespace Tests\Feature;

use App\Models\Parents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParentAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function parent_can_login_with_correct_credentials()
    {
        // Create a test parent
        $parent = Parents::create([
            'fname' => 'Test',
            'lname' => 'Parent',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'telp' => '1234567890',
        ]);

        // Attempt to login
        $response = $this->postJson('/api/parent/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'parent' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ],
            ]);
    }

    /** @test */
    public function parent_cannot_login_with_incorrect_credentials()
    {
        // Attempt to login with invalid credentials
        $response = $this->postJson('/api/parent/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert the response
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    /** @test */
    public function authenticated_parent_can_logout()
    {
        // Create and authenticate a parent
        $parent = Parents::create([
            'fname' => 'Test',
            'lname' => 'Parent',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'telp' => '1234567890',
        ]);

        $token = $parent->createToken('test-token')->plainTextToken;

        // Attempt to logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/parent/logout');

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
    }
}
