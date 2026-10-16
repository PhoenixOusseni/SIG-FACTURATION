<?php

namespace Database\Factories;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProduitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Produit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => 'PRD-FAC-' . $this->faker->unique()->numberBetween(10000, 99999),
            'designation' => $this->faker->words(2, true),
            'prix_unitaire' => $this->faker->randomFloat(2, 1000, 500000),
            'unite' => $this->faker->randomElement(['piece', 'kg', 'litre', 'paquet', 'carton']),
            'qte' => $this->faker->randomFloat(2, 1, 500),
            'description' => $this->faker->optional()->sentence(8),
            'tva' => $this->faker->randomFloat(2, 0, 20),
            'reduction' => $this->faker->randomFloat(2, 0, 15),
        ];
    }
}
