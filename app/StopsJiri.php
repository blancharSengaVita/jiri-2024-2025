<?php

namespace App;

trait StopsJiri
{
    public function stopJiri()
    {
        session('currentJiri')->status = Jiri::STATUS_FINISHED;
        unset(session('currentJiri')->errors);
        session('currentJiri')->save();
        session()->forget('currentJiri');
        $this->dispatch('refreshSidebar');
    };
}
