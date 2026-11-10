@php
    $map = [
        'pending' => ['warning', 'Pending'],
        'confirmed' => ['info', 'Confirmed'],
        'done' => ['success', 'Done'],
        'rejected' => ['danger', 'Rejected'],
        'cancelled' => ['secondary', 'Cancelled'],
    ];
    [$color, $label] = $map[$status] ?? ['secondary', ucfirst((string) $status)];
@endphp
<span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ $label }}</span>
