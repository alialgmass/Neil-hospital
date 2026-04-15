<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            // general
            ['key' => 'hospital_phone',   'value' => '',            'group' => 'general'],
            ['key' => 'hospital_email',   'value' => '',            'group' => 'general'],
            ['key' => 'hospital_tax_no',  'value' => '',            'group' => 'general'],
            // finance
            ['key' => 'tax_pct',          'value' => '0',           'group' => 'finance'],
            ['key' => 'stock_alert_pct',  'value' => '20',          'group' => 'inventory'],
            // beds
            ['key' => 'surgery_beds',     'value' => '30',          'group' => 'beds'],
            ['key' => 'lasik_beds',       'value' => '20',          'group' => 'beds'],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'hospital_phone', 'hospital_email', 'hospital_tax_no',
            'tax_pct', 'stock_alert_pct', 'surgery_beds', 'lasik_beds',
        ])->delete();
    }
};
