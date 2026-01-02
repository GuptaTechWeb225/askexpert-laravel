<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class RestaurantService
{
    use FileManagerTrait;

    /**
     * @return array[f_name: mixed, l_name: mixed, email: mixed, phone: mixed, country: mixed, city: mixed, zip: mixed, street_address: mixed, password: string]
     */


    public function isLoginSuccessful(string $email, string $password, string|null|bool $rememberToken): bool
    {
        if (auth('restaurant')->attempt(['email' => $email, 'password' => $password], $rememberToken)) {
            return true;
        }
        return false;
    }
    public function logout(): void
    {
        auth()->guard('restaurant')->logout();
        session()->invalidate();
    }
    public function getCustomerData(object $request): array
    {
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'country' => $request['country'] ?? null,
            'city' => $request['city'] ?? null,
            'zip' => $request['zip_code'] ?? null,
            'street_address' => $request['address'] ?? null,
            'password' => bcrypt($request['password'] ?? 'password')
        ];
    }

    public function deleteImage(object|null $data): bool
    {
        if ($data && $data['image']) {
            $this->delete('profile/' . $data['image']);
        };
        return true;
    }
}
