<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard()
    {
        return view('admin.index');
    }

    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message' => 'Admin logout succesfully',
            'alert-type' => 'success'
        );

        return redirect('/admin/login')->with( $notification);
    }

    public function AdminLogin()
    {
        return view('admin.admin_login');
    }

    public function AdminProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);

        return view('admin.admin_profile_view',compact('profileData'));
    }

    public function AdminProfileStore(Request $request)
    {
        $id = Auth::user()->id;
        $data = User::find($id);
        $data->username = $request->username;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        if ($request->file('photo')) {

            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename = date('YmdHi').$file->getClientOriginalName(); 
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo'] = $filename;  
        }

        $data->save();

        $notification = array(
            'message' => 'Admin profile updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    public function AdminChangePassword()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);

        return view('admin.admin_change_password',compact('profileData'));
    }

    public function AdminUpdatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);

        if (!Hash::check($request->old_password, auth::user()->password)) {
            
            $notification = array(
                'message' => 'Old password does not match!',
                'alert-type' => 'error'
            );
    
            return redirect()->back()->with($notification); 
        }

        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        $notification = array(
            'message' => 'Password change succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 
    }

    /////////////// AGENT USER ALL FUNCTİON ///////////////

    public function AllAgent()
    {
        $allAgent = User::where('role', 'agent')->get();
        
        return view('backend.agentuser.all_agent',compact('allAgent')); 
    }

    public function AddAgent()
    {
        return view('backend.agentuser.add_agent'); 
    }

    public function StoreAgent(Request $request)
    {
        User::insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'agent',
            'status' => 'active',
        ]);

        $notification = array(
            'message' => 'Agent created succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.agent')->with($notification);
    }

    public function EditAgent($id)
    {
        $allAgent = User::findOrFail($id);

        return view('backend.agentuser.edit_agent',compact('allAgent')); 
    }

    public function UpdateAgent(Request $request)
    {
        $user_id = $request->id;

        User::findOrFail($user_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $notification = array(
            'message' => 'Agent updated succesfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.agent')->with($notification);
    }

    public function DeleteAgent($id)
    {
        User::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Agent deleted succesfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function changeStatus(Request $request)
    {
        $user = User::find($request->user_id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['success' => 'Status change successfully.']);
        
    }
}