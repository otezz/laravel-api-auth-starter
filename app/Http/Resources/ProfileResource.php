<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $profile = UserResource::make($this)->toArray($request);
        $profile['permissions'] = $this->getAllPermissions();

        return $profile;
    }
}
