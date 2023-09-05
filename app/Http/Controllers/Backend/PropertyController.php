<?php

namespace App\Http\Controllers\Backend;

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

    public function StoreProperty(Request $request)
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
            'agent_id' => $request->agent_id,
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
    
            return redirect()->route('all.property')->with($notification); 
        }
    }

    public function EditProperty($id)
    {
        $facilities = Facility::where('property_id',$id)->get();
        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_amenities = explode(',', $type);

        $multiImage = MultiImage::where('property_id',$id)->get();
     
        $propertyType = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();
        $activeAgent = User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.edit_property',compact('amenities','propertyType','activeAgent','property','property_amenities','multiImage','facilities'));
    }

    public function UpdateProperty(Request $request)
    {
        $amenities_id = $request->amenities_id;
        $amenities = implode(",",$amenities_id);

        $property_id = $request->id;

        Property::findOrFail($property_id)->update([
            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenities,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
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
            'agent_id' => $request->agent_id,
            'updated_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Property updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification); 
    }

    public function UpdatePropertyThumbnail(Request $request)
    {
        $property_id = $request->id;
        $oldImg = $request->old_img;

        $image = $request->file('property_thumbnail');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize('370','250')->save('upload/property/thumbnail/'.$name_gen);
        $save_url = 'upload/property/thumbnail/'.$name_gen;

        if (file_exists($oldImg)) {
            unlink($oldImg);
        }

        Property::findOrFail($property_id)->update([
            'property_thumbnail' => $save_url,
            'updated_at' => Carbon::now()
        ]);

        $notification = array(
            'message' => 'Property image thumbnail updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification); 
    }

    public function UpdatePropertyMultiImage(Request $request)
    {
        $imgs = $request->multi_img;

        foreach ($imgs as $id => $img) {

            $imgDel = MultiImage::findOrFail($id);
            unlink($imgDel->photo_name);

            $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
            Image::make($img)->resize(770,520)->save('upload/property/multi-image/'.$make_name);
            $uploadPath = 'upload/property/multi-image/'.$make_name;

            MultiImage::where('id',$id)->update([
                'photo_name' => $uploadPath,
                'updated_at' => Carbon::now()
            ]);
        }

        $notification = array(
            'message' => 'Property multi image updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function PropertyMultiImageDelete($id)
    {
        $oldImg = MultiImage::findOrFail($id);
        unlink($oldImg->photo_name);

        $oldImg->delete();

        $notification = array(
            'message' => 'Property multi image deleted succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function StoreNewMultiImage(Request $request)
    {
        $new_multiimage = $request->imageid;
        $img = $request->file('multi_img');

        $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
        Image::make($img)->resize(770,520)->save('upload/property/multi-image/'.$make_name);
        $uploadPath = 'upload/property/multi-image/'.$make_name;

        MultiImage::insert([
            'property_id' => $new_multiimage,
            'photo_name' => $uploadPath,
            'created_at' => Carbon::now()
        ]);

        $notification = array(
            'message' => 'Property multi image added succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function UpdatePropertyFacilities(Request $request)
    {
        $property_id = $request->id;

        if ($request->facility_name == NULL) {

            return redirect()->back();

        } else {
            Facility::where('property_id',$property_id)->delete();

            $facilities = Count($request->facility_name);

            for ($i=0; $i < $facilities; $i++) { 
                $fcount = new Facility();
                $fcount->property_id = $property_id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            }
        }

        $notification = array(
            'message' => 'Property facility updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function DeleteProperty($id)
    {
        $property = Property::findOrFail($id);
        unlink($property->property_thumbnail);

        $property->delete();

        $image = MultiImage::where('property_id', $id)->get();
        
        foreach ($image as $img) {
            unlink($img->photo_name);
            MultiImage::where('property_id', $id)->delete();
        }

        $facilitiesData = Facility::where('property_id', $id)->get();

        foreach ($facilitiesData as $item) {
            $item->facility_name;
            Facility::where('property_id', $id)->delete();
        }

        $notification = array(
            'message' => 'Property deleted succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function DetailsProperty($id)
    {
        $facilities = Facility::where('property_id',$id)->get();
        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_amenities = explode(',', $type);

        $multiImage = MultiImage::where('property_id',$id)->get();
     
        $propertyType = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();
        $activeAgent = User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.details_property',compact('amenities','propertyType','activeAgent','property','property_amenities','multiImage','facilities'));
    }

    public function InactiveProperty(Request $request)
    {
        $property_id = $request->id;
        Property::findOrFail($property_id)->update([
            'status' => 0
        ]);

        $notification = array(
            'message' => 'Property inactive succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification); 
    }

    public function ActiveProperty(Request $request)
    {
        $property_id = $request->id;
        Property::findOrFail($property_id)->update([
            'status' => 1
        ]);

        $notification = array(
            'message' => 'Property active succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification); 
    }

    public function AdminPackageHistory()
    {
       $packageHistory = PackagePlan::latest()->get();

       return view('backend.package.package_history',compact('packageHistory'));
    }

    public function AdminPackageInvoice($id)
    {
        $packageHistory = PackagePlan::where('id', $id)->first();

        $pdf = Pdf::loadView('backend.package.package_history_invoice', compact('packageHistory'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path()
        ]);

        return $pdf->download('invoice.pdf');
    }
}
