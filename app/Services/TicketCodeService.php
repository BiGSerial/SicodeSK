<?php

namespace App\Services;

use App\Models\Area;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketCodeService
{
    /**
     * Gera o próximo código no padrão SIGLA-AAMM-SEQ (ex.: CIP-2509-0001)
     * com segurança transacional no PostgreSQL.
     */
    public static function nextCode(Area $area, ?DateTimeInterface $when = null): string
    {
        $when ??= now();
        $period = $when->format('ym'); // AAMM

        return DB::transaction(function () use ($area, $period, $when) {
            // UPSERT atômico no Postgres, retornando o novo last_number
            $row = DB::selectOne(
                // linguagem SQL pura para aproveitar RETURNING
                <<<SQL
                INSERT INTO area_ticket_counters (area_id, period, last_number, created_at, updated_at)
                VALUES (?, ?, 1, ?, ?)
                ON CONFLICT (area_id, period)
                DO UPDATE SET last_number = area_ticket_counters.last_number + 1,
                        updated_at = EXCLUDED.updated_at
                RETURNING last_number
                SQL,
                [$area->id, $period, now(), now()]
            );

            $seq = str_pad((string)$row->last_number, 4, '0', STR_PAD_LEFT);
            $sigla = Str::upper($area->sigla ?? 'XXX');

            return "{$sigla}-{$period}-{$seq}";
        });
    }
}
