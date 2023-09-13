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
use App\Models\PackagePlan;
use App\Models\PropetyMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

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

        $id = Auth::user()->id;
        $property = User::where('role','agent')->where('id', $id)->first();

        $property_count = $property->credit;

        if ($property_count == 1 || $property_count == 7) {

            return redirect()->route('buy.package');

        } else {

            return view('agent.property.add_property',compact('propertyType','amenities'));

        }
    }

    public function AgentStoreProperty(Request $request)
    {
        $id = Auth::user()->id;
        $user_id = User::findOrFail($id);
        $nid = $user_id->credit;



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

            User::where('id', $id)->update([
                'credit' => DB::raw('1 + '. $nid)
            ]);
           
            $notification = array(
                'message' => 'Property created succesfully',
                'alert-type' => 'success'
            );
    
            return redirect()->route('agent.all.property')->with($notification); 
        }
    }

    public function AgentEditProperty($id)
    {
        $facilities = Facility::where('property_id',$id)->get();
        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_amenities = explode(',', $type);

        $multiImage = MultiImage::where('property_id',$id)->get();
     
        $propertyType = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();

        return view('agent.property.edit_property',compact('amenities','propertyType','property','property_amenities','multiImage','facilities'));
    }

    public function AgentUpdateProperty(Request $request)
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
            'agent_id' => Auth::user()->id,
            'updated_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Property updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('agent.all.property')->with($notification);
    }

    public function AgentUpdatePropertyThumbnail(Request $request)
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

        return redirect()->route('agent.all.property')->with($notification); 
    }

    public function AgentUpdatePropertyMultiimage(Request $request)
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

    public function AgentPropertyMultiimageDelete($id)
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

    public function AgentStoreNewMultiimage(Request $request)
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

    public function AgentUpdatePropertyFacilities(Request $request)
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

    public function BuyPackage()
    {
        return view('agent.package.buy_package');
    }

    public function BuyBusinessPlan()
    {
        $id = Auth::user()->id;
        $user = User::find($id);
        return view('agent.package.business_plan',compact('user'));
    }

    public function StoreBusinessPlan(Request $request)
    {
        $id = Auth::user()->id;
        $user_id = User::findOrFail($id);
        $credit = $user_id->credit;

        PackagePlan::insert([
            'user_id' => $id,
            'package_name' => 'Business',
            'invoice' => 'ETS'.mt_rand(10000000,99999999),
            'package_credits' => '3',
            'package_amount' => '20',
            'created_at' => Carbon::now()
        ]);

        User::where('id',$id)->update([
            'credit' => DB::raw('3 +'.$credit)
        ]);

        $notification = array(
            'message' => 'You have purchase business package successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('agent.all.property')->with($notification);  
    }

    public function BuyProfessionalPlan()
    {
        $id = Auth::user()->id;
        $data = User::find($id);

        return view('agent.package.professional_plan',compact('data'));
    }

    public function StoreProfessionalPlan(Request $request)
    {
        $id = Auth::user()->id;
        $user_id = User::findOrFail($id);
        $credit = $user_id->credit;

        PackagePlan::insert([
            'user_id' => $id,
            'package_name' => 'Professional',
            'invoice' => 'ETS'.mt_rand(10000000,99999999),
            'package_credits' => '10',
            'package_amount' => '50',
            'created_at' => Carbon::now()
        ]);

        User::where('id',$id)->update([
            'credit' => DB::raw('10 +'.$credit)
        ]);

        $notification = array(
            'message' => 'You have purchase professional package successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('agent.all.property')->with($notification);  
    }

    public function PackageHistory()
    {
        $id = Auth::user()->id;
        $packageHistory = PackagePlan::where('user_id', $id)->get();

        return view('agent.package.package_history', compact('packageHistory'));
    }

    public function AgentPackageInvoice($id)
    {
        $packageHistory = PackagePlan::where('id', $id)->first();

        $pdf = Pdf::loadView('agent.package.package_history_invoice', compact('packageHistory'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path()
        ]);

        return $pdf->download('invoice.pdf');
    }

    public function AgentPropertyMessage()
    {
        $id = Auth::user()->id;
        $user_message = PropetyMessage::where('agent_id', $id)->get();

        return view('agent.message.all_message', compact('user_message'));
    }
}