<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\Amenities;
use App\Models\PropertyType;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AgentPropertyController extends Controller
{
    public function AgentAllProperty()
    {
        $id = Auth::user()->id;
        $property = Property::where('agent_id', $id)->latest()->get();

        return view('agent.property.all_property',compact('property'));
    }

    public function AgentAddProperty()
    {
        $propertyType = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();

        return view('agent.property.add_property',compact('propertyType','amenities'));
    }

    public function AgentStoreProperty(Request $request)
    {
        $amenities_id = $request->amenities_id;
        $amenities = implode(",",$amenities_id);

        $pcode = IdGenerator::generate([
            'table'  => 'properties',
            'field'  => 'property_code',
            'length' => '5',
            'prefix' => 'PC'
        ]);

        $image = $request->file('property_thumbnail');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize('370','250')->save('upload/property/thumbnail/'.$name_gen);
        $save_url = 'upload/property/thumbnail/'.$name_gen;

        $property_id = Property::insertGetId([
            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenities,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
            'property_code' => $pcode,
            'property_status' =>$request->property_status,

            'lowest_price' => $request->lowest_price,
            'max_price' => $request->max_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'garage' => $request->garage,
            'garage_size' => $request->garage_size,

            'property_size' => $request->property_size,
            'property_video' => $request->property_video,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'neighborhood' => $request->neighborhood,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => Auth::user()->id,
            'status' => 1,
            'property_thumbnail' => $save_url,
            'created_at' => Carbon::now(),
        ]);
        
        ///////// MULTIPLE IMAGE UPLOAD /////////

        $image = $request->file('multi_img');

        foreach ($image as $img) {

            $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
            Image::make($img)->resize('370','250')->save('upload/property/multi-image/'.$make_name);
            $uploadPath = 'upload/property/multi-image/'.$make_name;

            MultiImage::insert([
                'property_id' => $property_id,
                'photo_name' => $uploadPath,
                'created_at' => Carbon::now()
            ]);
        }

        ///////// FACILITIES ADD /////////

        $facilities = Count($request->facility_name);

        if ($facilities != NULL) {

            for ($i=0; $i < $facilities; $i++) { 
                $fcount = new Facility();
                $fcount->property_id = $property_id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            }
           
            $notification = array(
                'message' => 'Property created succesfully',
                'alert-type' => 'success'
            );
    
            return redirect()->route('agent.all.property')->with($notification); 
        }
    }
}
