<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\PerfilPostulante;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Por defecto crea postulantes. Requiere que RolSeeder se haya ejecutado.
            'rol_id' => fn () => Rol::where('slug', Rol::POSTULANTE)->value('id'),
            'nombre' => fake()->firstName().' '.fake()->lastName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '9'.fake()->numerify('########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Postulante con su perfil profesional (relación 1:1). */
    public function postulante(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->perfilPostulante()->exists()) {
                PerfilPostulante::factory()->create(['usuario_id' => $user->id]);
            }
        });
    }

    /** Reclutador de una empresa (si no se indica, se crea una verificada). */
    public function reclutador(?Empresa $empresa = null): static
    {
        return $this->state(fn () => [
            'rol_id' => Rol::where('slug', Rol::RECLUTADOR)->value('id'),
            'empresa_id' => $empresa?->id ?? Empresa::factory(),
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn () => ['rol_id' => Rol::where('slug', Rol::SUPERVISOR)->value('id')]);
    }

    public function administrador(): static
    {
        return $this->state(fn () => ['rol_id' => Rol::where('slug', Rol::ADMINISTRADOR)->value('id')]);
    }
}
