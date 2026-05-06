<?php

declare(strict_types=1);

use App\Models\User;

test('user initials are generated correctly from full name', function () {
    $user = new User(['name' => 'Juan Pérez']);

    expect($user->initials())->toBe('JP');
});

test('user initials work with single name', function () {
    $user = new User(['name' => 'Admin']);

    expect($user->initials())->toBe('A');
});

test('user initials only uses first two words', function () {
    $user = new User(['name' => 'Juan Carlos Pérez García']);

    expect($user->initials())->toBe('JC');
});

test('user initials are uppercased', function () {
    $user = new User(['name' => 'john doe']);

    expect($user->initials())->toBe('JD');
});

test('isVerified returns true when email is verified', function () {
    $user = new User();
    $user->email_verified_at = now();

    expect($user->isVerified())->toBeTrue();
});

test('isVerified returns false when email is not verified', function () {
    $user = new User(['email_verified_at' => null]);

    expect($user->isVerified())->toBeFalse();
});
