<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollegeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =[
            'arabic_name'=>$this->arabic_name,
            'english_name'=>$this->english_name,
            'email'=>$this->email,
            'image'=>asset($this->email),

        ];
        return $data;
        // return parent::toArray($request);
    }
}
