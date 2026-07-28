<?php

namespace Tests\Support;

use App\Models\CommissionRule;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\Referral;
use App\Models\User;
use Database\Seeders\PaymentMethodsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $system = User::factory()->create(['email' => 'direct-sales@abdullahtown.pk', 'name' => 'Direct Sales', 'role' => 'customer', 'referral_code' => 'DIRECT-SALES']);
        $admin = User::factory()->create(['email' => 'admin@abdullahtown.pk', 'name' => 'Abdullah Town Admin', 'role' => 'admin']);
        $level3 = User::factory()->create(['email' => 'director@abdullahtown.pk', 'name' => 'Sales Director', 'role' => 'customer', 'referral_code' => 'AGT-DIRECTOR']);
        $level2 = User::factory()->create(['email' => 'manager@abdullahtown.pk', 'name' => 'Sales Manager', 'role' => 'customer', 'referral_code' => 'AGT-MANAGER']);
        $level1 = User::factory()->create(['email' => 'agent@abdullahtown.pk', 'name' => 'Sales Agent', 'role' => 'customer', 'referral_code' => 'AGT-AGENT']);

        $project = Project::create([
            'name' => 'Abdullah Town', 'slug' => 'abdullah-town', 'location' => 'Abbottabad',
            'gross_area_marla' => 8000, 'saleable_area_marla' => 8000, 'reserved_area_marla' => 0, 'status' => true,
        ]);
        foreach ([['5 Marla', 5], ['10 Marla', 10], ['15 Marla', 15], ['20 Marla', 20], ['Farmhouse', 80]] as [$name, $size]) {
            $package = PlotPackage::create([
                'project_id' => $project->id, 'name' => $name, 'size_marla' => $size,
                'booking_amount' => 350000, 'months' => 36, 'monthly_amount' => 50000,
                'month_12_balloon' => 150000, 'month_24_balloon' => 250000, 'month_36_balloon' => 350000, 'status' => true,
            ]);
            foreach ([1 => 5, 2 => 3, 3 => 2] as $level => $percentage) {
                CommissionRule::create(['package_id' => $package->id, 'level' => $level, 'percentage' => $percentage, 'status' => true]);
            }
        }

        Referral::create(['user_id' => $level1->id, 'sponsor_id' => $level2->id]);
        Referral::create(['user_id' => $level2->id, 'sponsor_id' => $level3->id]);
        Referral::create(['user_id' => $level3->id, 'sponsor_id' => $admin->id]);
        Referral::create(['user_id' => $system->id, 'sponsor_id' => null]);

        $this->call([RolesAndPermissionsSeeder::class, PaymentMethodsSeeder::class]);
    }
}
