<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;

class IpTable extends Component
{
    #[Locked]
    public string $modelClass = '';

    #[Locked]
    public int|string|null $modelKey = null;

    public ?string $title = null;

    public function mount(Model $model, ?string $title = null): void
    {
        $this->modelClass = $model::class;
        $this->modelKey = $model->getKey();
        $this->title = $title;
    }

    public function model(): ?Model
    {
        if ($this->modelClass === '' || !is_subclass_of($this->modelClass, Model::class)) {
            return null;
        }

        return $this->modelClass::query()->find($this->modelKey);
    }

    public function render(): View
    {
        return view('livewire.ip-table');
    }
}
