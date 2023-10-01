<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class TestimonialController extends Controller
{
    public function AllTestimonials()
    {
        $testimonial = Testimonial::latest()->get();

        return view('backend.testimonial.all_testimonial', compact('testimonial'));
    }

    public function AddTestimonials()
    {
        return view('backend.testimonial.add_testimonial');
    }

    public function StoreTestimonials(Request $request)
    {
        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(100, 100)->save('upload/testimonial/' . $name_gen);
        $save_url = 'upload/testimonial/' . $name_gen;

        Testimonial::insert([
            'name' => $request->name,
            'position' => $request->position,
            'message' => $request->message,
            'image' => $save_url
        ]);

        $notification = array(
            'message' => 'Testimonial inserted successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.testimonials')->with($notification);
    }

    public function EditTestimonials($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return view('backend.testimonial.edit_testimonial', compact('testimonial'));
    }

    public function UpdateTestimonials(Request $request)
    {
        $testimonial_id = $request->id;

        if ($request->file('image')) {

            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(100, 100)->save('upload/testimonial/' . $name_gen);
            $save_url = 'upload/testimonial/' . $name_gen;

            Testimonial::findOrFail($testimonial_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'message' => $request->message,
                'image' => $save_url
            ]);

            $notification = array(
                'message' => 'Testimonial updated successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.testimonials')->with($notification);

        } else {

            Testimonial::findOrFail($testimonial_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'message' => $request->message
            ]);

            $notification = array(
                'message' => 'Testimonial updated successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.testimonials')->with($notification);
        }
    }
}
