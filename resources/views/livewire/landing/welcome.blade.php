<?php

use function Livewire\Volt\{layout};

layout('components.layouts.landing');

?>

<div>
    <x-landing.hero />

    <x-landing.stats />

    <x-landing.features />

    <x-landing.testimonials />
</div>