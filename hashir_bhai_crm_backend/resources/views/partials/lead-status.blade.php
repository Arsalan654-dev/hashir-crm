@php
    $map = [
        'new' => ['primary', 'New'],
        'contacted' => ['info', 'Contacted'],
        'qualified' => ['warning', 'Qualified'],
        'proposal' => ['secondary', 'Proposal'],
        'negotiation' => ['dark', 'Negotiation'],
        'won' => ['success', 'Won'],
        'lost' => ['danger', 'Lost'],
    ];
    [$color, $label] = $map[$status] ?? ['secondary', ucfirst((string) $status)];
@endphp
<span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ $label }}</span>
