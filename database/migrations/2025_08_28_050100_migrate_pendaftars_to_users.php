<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        // Migrate active (non-deleted) pendaftars into users
        DB::table('pendaftars')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $user = null;

                    // Prefer match by email if present
                    if (!empty($row->email)) {
                        $user = DB::table('users')->where('email', $row->email)->first();
                    }

                    // Fallback by nik
                    if (!$user && !empty($row->nik)) {
                        $user = DB::table('users')->where('nik', $row->nik)->first();
                    }

                    // Fallback by no_hp
                    if (!$user && !empty($row->no_hp)) {
                        $user = DB::table('users')->where('no_hp', $row->no_hp)->first();
                    }

                    $now = now();

                    $payload = [
                        'name'           => $row->nama ?? ($user->name ?? null),
                        'email'          => $row->email ?? ($user->email ?? null),
                        'nik'            => $row->nik ?? ($user->nik ?? null),
                        'no_hp'          => $row->no_hp ?? ($user->no_hp ?? null),
                        'no_tiket'       => $row->no_tiket ?? ($user->no_tiket ?? null),
                        // Map province -> region; address -> village
                        'city'           => $row->city ?? ($user->city ?? null),
                        'region'         => $row->province ?? ($user->region ?? null),
                        'village'        => $row->address ?? ($user->village ?? null),
                        'checkin'        => $row->checkin ?? ($user->checkin ?? null),
                        'notes'          => $row->notes ?? ($user->notes ?? null),
                        'nomor_punggung' => $row->nomor_punggung ?? ($user->nomor_punggung ?? null),
                        'status_payment' => $row->status_payment ?? ($user->status_payment ?? null),
                        'payment_type'   => $row->payment_type ?? ($user->payment_type ?? null),
                        'total_bayar'    => $row->total_bayar ?? ($user->total_bayar ?? null),
                        'event_id'       => $row->event_id ?? ($user->event_id ?? null),
                        'start_at'       => $row->start_at ?? ($user->start_at ?? null),
                        'finish_at'      => $row->finish_at ?? ($user->finish_at ?? null),
                        'updated_at'     => $now,
                    ];

                    if ($user) {
                        // Update existing user by id
                        DB::table('users')->where('id', $user->id)->update($payload);
                    } else {
                        // Create new user with safe defaults
                        $email = $payload['email'];
                        if (empty($email)) {
                            $email = 'pendaftar-' . $row->id . '-' . Str::random(6) . '@example.local';
                        }

                        $insert = array_merge($payload, [
                            'email'      => $email,
                            'password'   => Hash::make(Str::random(16)),
                            'created_at' => $now,
                        ]);

                        DB::table('users')->insert($insert);
                    }
                }
            });
    }

    public function down(): void
    {
        // No destructive rollback: we don't delete users.
        // Optionally, you could nullify migrated fields for users that originated from pendaftars,
        // but it's safer to leave data intact.
    }
};
