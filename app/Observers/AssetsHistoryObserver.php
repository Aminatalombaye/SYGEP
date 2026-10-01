<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetsHistory;

class AssetsHistoryObserver
{
    public function created(Asset $asset): void
    {
        $this->record($asset, 'creation');
    }

    public function updated(Asset $asset): void
    {
        if ($asset->wasChanged(['status_id', 'location_id', 'agent_id', 'service_id'])) {
            $this->record($asset, 'modification');
        }
    }

    private function record(Asset $asset, string $action): void
    {
        AssetsHistory::create([
            'asset_id'    => $asset->id,
            'action'      => $action,
            'status_id'   => $asset->status_id,
            'location_id' => $asset->location_id,
            'agent_id'    => $asset->agent_id,
            'service_id'  => $asset->service_id,
            'user_id'     => auth()->id(),
        ]);
    }
}
