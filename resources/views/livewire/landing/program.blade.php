<?php

use App\Models\Layanan;
use function Livewire\Volt\{layout, computed};

layout('components.layouts.landing');

$layanans = computed(function () {
    return Layanan::where('is_active', true)
        ->orderBy('name')
        ->get();
});

?>

<div>
    <x-landing.program-hero />

    <x-landing.program :layanans="$this->layanans" />
</div>