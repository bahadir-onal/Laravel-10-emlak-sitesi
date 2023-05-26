<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\Amenities;
use App\Models\PropertyType;
use App\Models\User;

class PropertyController extends Controller
{
    public function AllProperty()
    {
        $property = Property::latest()->get();
        return view('backend.property.all_property',compact('property'));
    }

    public function AddProperty()
    {
        $amenities = Amenities::latest()->get();
        $propertyType = PropertyType::latest()->get();
        $activeAgent = User::where('status','active')->where('role', 'agent')->get();
        
        return view('backend.property.add_property',compact('amenities','propertyType','activeAgent'));
    }
}
