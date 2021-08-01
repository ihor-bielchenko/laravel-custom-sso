<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Delete extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray($model)
    {
        return parent::toArray($model);
    }
}
