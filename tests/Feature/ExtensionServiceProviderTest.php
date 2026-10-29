<?php

declare(strict_types=1);

use Larasell\Extension\ExtensionServiceProvider;

it('boots inside the admin panel', function () {
    expect(app()->getProviders(ExtensionServiceProvider::class))->toHaveCount(1)
        ->and(config('larasell.extension'))->toBeArray();

    signInAdmin();

    $this->get(route('larasell.admin.home'))->assertOk();
});
