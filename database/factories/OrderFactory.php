<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $year = date('Y');
        $month = date('m');
        $number = $this->faker->unique()->numberBetween(1, 999);
        $invoiceNumber = 'INV-'.$year.'-'.$month.'-';
        // $invoiceNumber = 'INV-'.$year.'-'.$month.'-'.$this->faker->numerify(str_pad($number, 3, '0', STR_PAD_LEFT));

        $month = $this->faker->numberBetween(1, 12);
        $year = $this->faker->numberBetween(2017, 2023);
        $day = $this->faker->numberBetween(1, 31);

        $createdAt = Carbon::create($year, $month, $day);

        $month1 = $this->faker->numberBetween(1, 12);
        $year1 = $this->faker->numberBetween(2017, 2023);
        $day1 = $this->faker->numberBetween(1, 31);

        $updatedAt = Carbon::create($year1, $month1, $day1);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'inventory_id' => Inventory::inRandomOrder()->first()->id,
            // 'address_id' => Address::inRandomOrder()->first()->id,
            'packaging_id' => $this->faker->randomElement([null, Package::inRandomOrder()->first()->id]),
            // 'coupon_id' => $this->faker->randomElement([null, Coupon::inRandomOrder()->first()->id]),
            'branch_id' => $this->faker->randomElement([null, Branch::inRandomOrder()->first()->id]),
            'employee_id' => $this->faker->randomElement([null, Employee::inRandomOrder()->first()->id]),
            'invoice_number' => $this->faker->randomElement([$invoiceNumber, null]),
            'packed_date' => $this->faker->dateTime(),
            'delivery_date' => $this->faker->dateTime(),
            'shipping_date' => $this->faker->dateTime(),
            'receiving_date' => $this->faker->dateTime(),
            'total_price' => $this->faker->randomFloat() ,
            'total_quantity' => $this->faker->randomDigit(),
            'type' => $this->faker->randomElement(['xo-delivery', 'kadmus']),
            'paid' => $this->faker->boolean(),
            'closed' => $this->faker->boolean(),
            'status' => $this->faker->randomElement(['processing' , 'received' , 'in_delivery' , 'canceled' , 'replace', 'retrieved' , 'fulfilled']),
            'payment_method' => $this->faker->name() ,
            'need_packaging' => $this->faker->boolean() ,
            'shipping_fee' => $this->faker->randomFloat(),
            'qr_code' => $this->faker->text(30)
            // 'created_at' => $createdAt,
            // 'updated_at' => $updatedAt,
        ];
    }
}
