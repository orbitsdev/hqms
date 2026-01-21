<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'description' => $this->description,
            'avg_duration' => $this->avg_duration,

            // Availability info (when loaded)
            'booked_count' => $this->when(isset($this->booked_count), $this->booked_count),
            'is_available' => $this->when(isset($this->is_available), $this->is_available),
            'query_date' => $this->when(isset($this->query_date), $this->query_date),

            'is_active' => $this->is_active,
        ];
    }
}
