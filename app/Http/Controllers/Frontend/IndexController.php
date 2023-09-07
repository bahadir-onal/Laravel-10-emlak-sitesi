<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\Amenities;
use App\Models\PackagePlan;
use App\Models\PropertyType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function PropertyDetails($id, $slug)
    {
        $property = Property::findOrFail($id);
        $multi_image = MultiImage::where('property_id', $id)->get();

        $amenities = $property->amenities_id;
        $property_amen = explode(',',$amenities);

        $facility = Facility::where('property_id', $id)->get();

        return view('frontend.property.property_details', compact('property', 'multi_image', 'property_amen', 'facility'));
    }
}
