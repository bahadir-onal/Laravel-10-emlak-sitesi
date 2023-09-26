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
use App\Models\PropetyMessage;
use App\Models\State;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function PropertyDetails($id, $slug)
    {
        $property = Property::findOrFail($id);
        $multi_image = MultiImage::where('property_id', $id)->get();

        $amenities = $property->amenities_id;
        $property_amen = explode(',', $amenities);

        $facility = Facility::where('property_id', $id)->get();

        $type_id = $property->ptype_id;
        $related_property = Property::where('ptype_id', $type_id)->where('id', '!=', $id)->orderBy('id', 'desc')->limit(3)->get();

        return view('frontend.property.property_details', compact('property', 'multi_image', 'property_amen', 'facility', 'related_property'));
    }

    public function PropertyMessage(Request $request)
    {
        $property_id = $request->property_id;
        $agent_id = $request->agent_id;

        if (Auth::check()) {

            PropetyMessage::insert([

                'user_id' => Auth::user()->id,
                'agent_id' => $agent_id,
                'property_id' => $property_id,
                'msg_name' => $request->msg_name,
                'msg_email' => $request->msg_email,
                'msg_phone' => $request->msg_phone,
                'message' => $request->message,
                'created_at' => Carbon::now()
            ]);

            $notification = array(
                'message' => 'Send Message Successfully',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Plz Login Your Account First',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }
    }

    public function AgentDetails($id)
    {
        $agent = User::findOrFail($id);
        $property = Property::where('agent_id', $id)->get();
        $featured = Property::where('featured', '1')->limit(3)->get();

        $rentproperty = Property::where('property_status', 'rent')->get();
        $buyproperty = Property::where('property_status', 'buy')->get();

        return view('frontend.agent.agent_details', compact('agent', 'property', 'featured', 'rentproperty', 'buyproperty'));
    }

    public function AgentDetailsMessage(Request $request)
    {
        $agent_id = $request->agent_id;

        if (Auth::check()) {

            PropetyMessage::insert([

                'user_id' => Auth::user()->id,
                'agent_id' => $agent_id,
                'msg_name' => $request->msg_name,
                'msg_email' => $request->msg_email,
                'msg_phone' => $request->msg_phone,
                'message' => $request->message,
                'created_at' => Carbon::now()
            ]);

            $notification = array(
                'message' => 'Send Message Successfully',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Plz Login Your Account First',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }
    }

    public function RentProperty()
    {
        $property = Property::where('status', '1')->where('property_status', 'rent')->paginate(3);

        return view('frontend.property.rent_property', compact('property'));
    }

    public function BuyProperty()
    {
        $property = Property::where('status', '1')->where('property_status', 'buy')->get();

        return view('frontend.property.buy_property', compact('property'));
    }

    public function PropertyType($id)
    {
        $property = Property::where('status', '1')->where('ptype_id', $id)->get();

        $pbread = PropertyType::where('id', $id)->first();

        return view('frontend.property.property_type', compact('property', 'pbread'));
    }

    public function StateDetails($id)
    {
       $property = Property::where('status', '1')->where('state', $id)->get();

       $bstate = State::where('id', $id)->first();

       return view('frontend.property.state_property', compact('property', 'bstate'));
    }
}
