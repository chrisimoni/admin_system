<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Support\Str;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function a_user_can_be_created()
    {
        Mail::fake();
        $this->withoutExceptionHandling();


        $user = User::factory()->create();
        $token = auth()->login($user);
   
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'])->json('POST', '/api/create-user', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'request successfully submitted for approval'
        ]);
        $this->assertDatabaseHas('user_requests', [
            'request_type' => 'create',
            'request_object' => json_encode([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ]),
            'initiator_id' => 1,
        ]);
    }

}
