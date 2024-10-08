<?php

namespace App\View\Components;

use App\Models\ListeningParty;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ListeningPartyInfo extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ListeningParty $listeningParty) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.listening-party-info');
    }
}
