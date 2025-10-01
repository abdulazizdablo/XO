<?php

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;





it('validates the registration payload', function () {
    $response = $this->postJson('/api/v1/user/register', []);

    $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', false)
                ->has('error.message')
        );
});

it('registers a new user and stores the verification code', function () {
    $payload = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '0999399999',
        'email' => 'jane6786786@example.com',
        'password' => 'secret1234',
    ];

    $response = $this->postJson('/api/v1/user/register', $payload);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->has('data.user.id')
                ->where('data.user.phone', $payload['phone'])
                ->where('data.message', 'User created successfully')
        );

    $this->assertDatabaseHas('users', [
        'phone' => $payload['phone'],
        'email' => $payload['email'],
    ]);

    $user = User::where('phone', $payload['phone'])->first();

    $this->assertDatabaseHas('user_verifications', [
        'user_id' => $user->id,
    ]);
});

it('prevents login for unverified users', function () {
    $user = User::create([
        'first_name' => 'Un',
        'last_name' => 'Verified',
        'phone' => '0999999998',
        'password' => Hash::make('secret1234'),
        'isVerified' => false,
    ]);

    $response = $this->postJson('/api/v1/user/login', [
        'phone' => $user->phone,
        'password' => 'secret1234',
    ]);

    $response->assertStatus(403)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('message', 'Please verify your number')
                ->where('user_id', $user->id)
        );
});

it('logs in a verified user and returns access tokens', function () {
    $user = User::create([
        'first_name' => 'Verified',
        'last_name' => 'User',
        'phone' => '0999999997',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    $response = $this->postJson('/api/v1/user/login', [
        'phone' => $user->phone,
        'password' => 'secret1234',
    ]);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.user.id', $user->id)
                ->has('data.token')
                ->has('data.refresh_token')
                ->where('data.message', fn ($message) => is_string($message) && $message !== '')
        );
});

it('resends the verification code for a registered user', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '0999999996',
        'password' => Hash::make('secret1234'),
        'isVerified' => false,
    ]);

    $response = $this->postJson('/api/v1/user/resend-code', [
        'phone' => $user->phone,
    ]);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.message', fn ($message) => ! empty($message))
        );

    $this->assertDatabaseHas('user_verifications', [
        'user_id' => $user->id,
    ]);
});

it('verifies a user using the otp flow', function () {
    $user = User::create([
        'first_name' => 'Pending',
        'last_name' => 'Verification',
        'phone' => '0999999995',
        'password' => Hash::make('secret1234'),
        'isVerified' => false,
    ]);

    UserVerification::create([
        'user_id' => $user->id,
        'verify_code' => '0000',
        'expired_at' => now()->addMinutes(15),
    ]);

    $response = $this->postJson('/api/v1/user/verify', [
        'phone' => $user->phone,
        'verification_code' => '0000',
    ]);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('user.id', $user->id)
                ->where('message', 'User has been verified')
                ->has('token')
        );

    $this->assertTrue($user->fresh()->isVerified);
    $this->assertDatabaseMissing('user_verifications', [
        'user_id' => $user->id,
    ]);
});

it('rejects forgot password requests for unknown users', function () {
    $response = $this->postJson('/api/v1/user/forget-password', [
        'phone' => '0911111111',
    ]);

    $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', false)
            
        );
});

it('issues a reset code for verified users', function () {
    $user = User::create([
        'first_name' => 'Reset',
        'last_name' => 'Candidate',
        'phone' => '0999999994',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    $response = $this->postJson('/api/v1/user/forget-password', [
        'phone' => $user->phone,
    ]);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.message', fn ($message) => ! empty($message))
        );

    $this->assertDatabaseHas('user_verifications', [
        'user_id' => $user->id,
    ]);
});

it('resets the password when the verification code matches', function () {
    $user = User::create([
        'first_name' => 'Reset',
        'last_name' => 'User',
        'phone' => '0999999993',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    UserVerification::create([
        'user_id' => $user->id,
        'verify_code' => '0000',
        'expired_at' => now()->addMinutes(15),
    ]);

    $response = $this->postJson('/api/v1/user/reset-password', [
        'phone' => $user->phone,
        'password' => 'new-secret-123',
        'password_confirmation' => 'new-secret-123',
        'verification_code' => '0000',
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.message', fn ($message) => ! empty($message))
        );

    $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
});

it('confirms the verification code for password resets', function () {
    $user = User::create([
        'first_name' => 'Otp',
        'last_name' => 'Checker',
        'phone' => '0999999992',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    UserVerification::create([
        'user_id' => $user->id,
        'verify_code' => '0000',
        'expired_at' => now()->addMinutes(15),
    ]);

    $response = $this->postJson('/api/v1/user/verify-otp-password', [
        'phone' => $user->phone,
        'verification_code' => '0000',
    ]);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.message', fn ($message) => ! empty($message))
        );
});

it('refreshes tokens for authenticated users', function () {
    $user = User::create([
        'first_name' => 'Refreshed',
        'last_name' => 'User',
        'phone' => '0999999991',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/user/refresh-token');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('user.id', $user->id)
                ->has('refresh_token')
        );
});

it('returns the authentication status for the current token', function () {
    $user = User::create([
        'first_name' => 'Token',
        'last_name' => 'Owner',
        'phone' => '0999999990',
        'password' => Hash::make('secret1234'),
        'isVerified' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/user/get-user');

    $response->assertOk()
        ->assertJson([
            'user' => true,
        ]);
});