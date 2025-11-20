<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{layout};

layout('layouts.landing');

new class extends Component {
    // Welcome page - statis
}; ?>

<div>
    <x-landing.hero />

    <x-landing.stats />

    <x-landing.features />

    <x-landing.testimonials />
</div>