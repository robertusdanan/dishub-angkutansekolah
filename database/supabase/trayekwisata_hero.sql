-- Jalankan di Supabase SQL Editor.
create table if not exists public.trayekwisata_hero (
    id uuid primary key default gen_random_uuid(),
    tipe text not null check (tipe in ('gambar', 'video')),
    url text not null,
    urutan integer not null default 0,
    aktif boolean not null default true,
    created_at timestamptz not null default now()
);
create index if not exists trayekwisata_hero_order_idx on public.trayekwisata_hero (aktif, urutan);
