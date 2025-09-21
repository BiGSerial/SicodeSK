<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @var \Illuminate\Support\Collection<int, \App\Models\Ticket> */
    protected Collection $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection(): Collection
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'Ticket',
            'Título',
            'Tipo',
            'Área',
            'Status',
            'Prioridade',
            'Criado em',
            'Atualizado em',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->code,
            $ticket->title,
            $ticket->type->name ?? '—',
            $ticket->area->name ?? '—',
            $this->formatStatus($ticket->status),
            $this->formatPriority($ticket->priority),
            $ticket->created_at?->format('d/m/Y H:i'),
            $ticket->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function formatStatus(?string $status): string
    {
        if (!$status) {
            return '—';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    private function formatPriority($priority): string
    {
        if (!$priority) {
            return '—';
        }

        return $priority->name ?? '—';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
            ],
        ];
    }
}
