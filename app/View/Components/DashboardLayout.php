<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DashboardLayout extends Component
{
    public $title;
    public $canWrite;
    public $lpPermissions;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = null, $canWrite = null, $lpPermissions = null)
    {
        $this->title = $title;
        $this->canWrite = $canWrite;
        $this->lpPermissions = $lpPermissions;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.dashboard');
    }
}
