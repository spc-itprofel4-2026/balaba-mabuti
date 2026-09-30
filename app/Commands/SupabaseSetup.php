<?php

namespace App\Commands;

use App\Libraries\SupabaseService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;

/**
 * Creates the announcement tables in the linked Supabase project.
 *
 * Usage: php spark supabase:setup
 */
class SupabaseSetup extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'supabase:setup';
    protected $description = 'Creates the announcements tables in Supabase (cloud system of record).';
    protected $usage       = 'supabase:setup';
    protected $arguments   = [];

    public function run(array $params)
    {
        /** @var SupabaseService $supabase */
        $supabase = service('supabase');

        if (! $supabase->isConfigured()) {
            CLI::error('Supabase is not configured. Fill in supabase.* in .env first.');

            return;
        }

        $sql = <<<'SQL'
create table if not exists public.user_profiles (
    id               bigserial primary key,
    name             text not null,
    username         text not null,
    email            text,
    phone            text,
    role             text not null default 'officer',
    password_hash    text not null,
    active           boolean not null default true,
    last_login       timestamptz,
    created_at       timestamptz not null default now(),
    updated_at       timestamptz not null default now()
);

create table if not exists public.announcements (
    id               bigserial primary key,
    title            text not null,
    body             text not null,
    category         text not null default 'general',
    priority         text not null default 'normal',
    status           text not null default 'draft',
    recipient_count  integer not null default 0,
    delivered_count  integer not null default 0,
    sent_by          text,
    sent_at          timestamptz,
    created_at       timestamptz not null default now()
);

create table if not exists public.announcement_deliveries (
    id               bigserial primary key,
    announcement_id  bigint not null references public.announcements(id) on delete cascade,
    viber_id         text not null,
    member_name      text,
    status           text not null,
    response         text,
    created_at       timestamptz not null default now()
);

create unique index if not exists user_profiles_username_key on public.user_profiles (username);
create index if not exists user_profiles_role_idx on public.user_profiles (role);

create index if not exists announcements_created_at_idx on public.announcements (created_at desc);
create index if not exists announcements_status_idx on public.announcements (status);
create index if not exists deliveries_announcement_idx on public.announcement_deliveries (announcement_id);

alter table public.user_profiles enable row level security;
alter table public.announcements enable row level security;
alter table public.announcement_deliveries enable row level security;
SQL;

        try {
            $supabase->runSql($sql);
        } catch (RuntimeException $e) {
            CLI::error('Setup failed: ' . $e->getMessage());

            return;
        }

        $this->seedProfiles($supabase);

        CLI::write('Supabase tables are ready (user_profiles, announcements, announcement_deliveries).', 'green');
        CLI::write('Row Level Security is enabled with no public policies,');
        CLI::write('so only the server-side service_role key can read or write them.');
    }

    /**
     * Creates the two starter officer accounts when the profile table is empty.
     */
    private function seedProfiles(SupabaseService $supabase): void
    {
        try {
            $existing = $supabase->profiles(1);
        } catch (RuntimeException $e) {
            CLI::error('Could not read user_profiles: ' . $e->getMessage());

            return;
        }

        if ($existing !== []) {
            CLI::write('user_profiles already has ' . count($existing) . ' row(s); no seed needed.');

            return;
        }

        $seed = [
            ['name' => 'Hermie Jay Balaba', 'username' => 'hermie', 'email' => 'hermie@org.local', 'phone' => '+639181111111', 'role' => 'admin'],
            ['name' => 'Marc Erfred Mabuti', 'username' => 'marc', 'email' => 'marc@org.local', 'phone' => '+639182222222', 'role' => 'officer'],
        ];

        foreach ($seed as $row) {
            try {
                $supabase->createProfile([
                    'name'          => $row['name'],
                    'username'      => $row['username'],
                    'email'         => $row['email'],
                    'phone'         => $row['phone'],
                    'role'          => $row['role'],
                    'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                    'active'        => true,
                ]);
                CLI::write('  seeded profile: ' . $row['username'] . ' / password123', 'green');
            } catch (RuntimeException $e) {
                CLI::error('  seed failed for ' . $row['username'] . ': ' . $e->getMessage());
            }
        }
    }
}
