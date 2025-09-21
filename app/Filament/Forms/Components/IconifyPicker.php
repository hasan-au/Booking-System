<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class IconifyPicker extends Field
{
    protected string $view = 'filament.forms.components.iconify-picker';

    // Optional: restrict search to certain icon sets (prefixes), e.g. ['mdi','tabler','ph']
    protected array|Closure|null $prefixes = null;

    // How many results to ask from Iconify per search
    protected int|Closure $limit = 64;

    public function prefixes(array|Closure|null $prefixes): static
    {
        $this->prefixes = $prefixes;
        return $this;
    }

    public function getPrefixes(): ?array
    {
        $value = $this->evaluate($this->prefixes);
        return $value ?: null;
    }

    public function limit(int|Closure $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function getLimit(): int
    {
        return (int) $this->evaluate($this->limit);
    }
}
