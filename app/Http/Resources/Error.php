<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Error extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray($message)
    {
        return [ 'message' => $message ];
    }
}
