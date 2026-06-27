<?php

declare(strict_types=1);

it('boots the laravel test application via testbench', function (): void {
    expect(app())->not->toBeNull()
        ->and(app()->environment())->toBe('testing');
});
