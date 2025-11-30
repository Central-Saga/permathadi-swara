<?php

namespace App\Actions\Fortify;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'telepon' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'tanggal_lahir' => ['nullable', 'date'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // Assign role 'Anggota' if role exists
        $anggotaRole = Role::where('name', 'Anggota')->first();
        if ($anggotaRole) {
            $user->assignRole('Anggota');
        }

        // Create anggota record
        Anggota::create([
            'user_id' => $user->id,
            'telepon' => $input['telepon'],
            'alamat' => $input['alamat'] ?? null,
            'tanggal_lahir' => isset($input['tanggal_lahir']) ? $input['tanggal_lahir'] : null,
            'tanggal_registrasi' => now(),
            'status' => 'Aktif',
        ]);

        return $user;
    }
}
