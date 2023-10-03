<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use Carbon\Carbon;
use App\Models\BlogPost;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function AllBlogCategory()
    {
        $category = BlogCategory::latest()->get();

        return view('backend.category.blog_category', compact('category'));
    }

    public function StoreBlogCategory(Request $request)
    {
        BlogCategory::insert([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
        ]);

        $notification = array(
            'message' => 'Blog category create successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.blog.category')->with($notification);
    }

    public function EditBlogCategory($id)
    {
        $categories = BlogCategory::findOrFail($id);

        return response()->json($categories);
    }

    public function UpdateBlogCategory(Request $request)
    {
        $cat_id = $request->cat_id;

        BlogCategory::findOrFail($cat_id)->update([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
        ]);

        $notification = array(
            'message' => 'Blog category updated successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.blog.category')->with($notification);
    }

    public function AllPost()
    {
        $post = BlogPost::latest()->get();

        return view('backend.post.all_post', compact('post'));
    }

    public function AddPost()
    {
        $blogcat = BlogCategory::latest()->get();

        return view('backend.post.add_post', compact('blogcat'));
    }

    public function StorePost(Request $request)
    {
        $image = $request->file('post_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(370, 250)->save('upload/post/' . $name_gen);
        $save_url = 'upload/post/' . $name_gen;

        BlogPost::insert([
            'blogcat_id' => $request->blogcat_id,
            'user_id' => Auth::user()->id,
            'post_title' => $request->post_title,
            'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'post_tags' => $request->post_tags,
            'post_image' => $save_url,
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'BlogPost Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.post')->with($notification);
    }
}
