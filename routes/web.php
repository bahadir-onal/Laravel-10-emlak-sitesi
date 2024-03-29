<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Backend\PropertyTypeController;
use App\Http\Controllers\Backend\PropertyController;
use App\Http\Controllers\Agent\AgentPropertyController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\StateController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\CompareController;
use App\Http\Controllers\TestimonialController;
use App\Http\Middleware\RedirectIfAuthenticated;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', [UserController::class, 'Index']);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {

        Route::get('/user/profile', [UserController::class, 'UserProfile'])->name('user.profile');
        Route::post('/user/profile/store', [UserController::class, 'UserProfileStore'])->name('user.profile.store');
        Route::get('/user/logout', [UserController::class, 'UserLogout'])->name('user.logout');
        Route::get('/user/change/password', [UserController::class, 'UserChangePassword'])->name('user.change.password');
        Route::post('/user/password/update', [UserController::class, 'UserPasswordUpdate'])->name('user.password.update');


         // User wishlist all route 
        Route::controller(WishlistController::class)->group(function(){
            Route::get('/user/wishlist', 'UserWishlist')->name('user.wishlist'); 
            Route::get('/get-wishlist-property', 'GetWishlistProperty'); 
            Route::get('/wishlist-remove/{id}', 'WishlistRemove'); 
        });

         // User compare all route 
         Route::controller(CompareController::class)->group(function(){
            Route::get('/user/compare', 'UserCompare')->name('user.compare');
            Route::get('/get-compare-property', 'GetCompareProperty');
            Route::get('/compare-remove/{id}', 'CompareRemove'); 
        });
        
    });

require __DIR__.'/auth.php';

    // ADMIN MİDDLEWARE
    Route::middleware(['auth', 'role:admin'])->group(function () { 

        Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
        Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
        Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
        Route::post('/admin/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');
        Route::get('/admin/change/password', [AdminController::class, 'AdminChangePassword'])->name('admin.change.password');
        Route::post('/admin/update/password', [AdminController::class, 'AdminUpdatePassword'])->name('admin.update.password');

    });

    // AGENT MİDDLEWARE
    Route::middleware(['auth', 'role:agent'])->group(function () { 

        Route::get('/agent/dashboard', [AgentController::class, 'AgentDashboard'])->name('agent.dashboard');
        Route::get('/agent/logout', [AgentController::class, 'AgentLogout'])->name('agent.logout');
        Route::get('/agent/profile', [AgentController::class, 'AgentProfile'])->name('agent.profile');
        Route::post('/agent/profile/store', [AgentController::class, 'AgentProfileStore'])->name('agent.profile.store');
        Route::get('/agent/change/password', [AgentController::class, 'AgentChangePassword'])->name('agent.change.password');
        Route::post('/agent/update/password', [AgentController::class, 'AgentUpdatePassword'])->name('agent.update.password');


        // AGENT PROPERTY ALL ROUTE
        Route::controller(AgentPropertyController::class)->group(function(){
            Route::get('/agent/all/property', 'AgentAllProperty')->name('agent.all.property');
            Route::get('/agent/add/property', 'AgentAddProperty')->name('agent.add.property');
            Route::post('/agent/store/property', 'AgentStoreProperty')->name('agent.store.property');        
            Route::get('/agent/edit/property/{id}', 'AgentEditProperty')->name('agent.edit.property');
            Route::post('/agent/update/property', 'AgentUpdateProperty')->name('agent.update.property');
            Route::post('/agent/update/property/thumbnail', 'AgentUpdatePropertyThumbnail')->name('agent.update.property.thumbnail');        
            Route::post('/agent/update/property/multiimage', 'AgentUpdatePropertyMultiimage')->name('agent.update.property.multiimage');        
            Route::get('/agent/property/multiimage/delete/{id}', 'AgentPropertyMultiimageDelete')->name('agent.property.multiimage.delete');
            Route::post('/agent/store/new/multiimage', 'AgentStoreNewMultiimage')->name('agent.store.new.multiimage');
            Route::post('/agent/update/property/facilities', 'AgentUpdatePropertyFacilities')->name('agent.update.property.facilities');        
            //Message Route
            Route::get('/agent/property/message/', 'AgentPropertyMessage')->name('agent.property.message');
            Route::get('/agent/message/details/{id}', 'AgentMessageDetails')->name('agent.message.details');
        });

        
        // AGENT BUY PACKAGE ROUTE
        Route::controller(AgentPropertyController::class)->group(function(){
            Route::get('/buy/package', 'BuyPackage')->name('buy.package');
            Route::get('/buy/business/plan', 'BuyBusinessPlan')->name('buy.business.plan');
            Route::post('/store/business/plan', 'StoreBusinessPlan')->name('store.business.plan');
            Route::get('/buy/professional/plan', 'BuyProfessionalPlan')->name('buy.professional.plan');
            Route::post('/store/professional/plan', 'StoreProfessionalPlan')->name('store.professional.plan');
            Route::get('/package/history', 'PackageHistory')->name('package.history');
            Route::get('/agent/package/invoice/{id}', 'AgentPackageInvoice')->name('agent.package.invoice');
        });
    });

    Route::get('/agent/login', [AgentController::class, 'AgentLogin'])->name('agent.login')->middleware(RedirectIfAuthenticated::class)->name('agent.login');
    Route::post('/agent/register', [AgentController::class, 'AgentRegister'])->name('agent.register');
    
    Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login')->middleware(RedirectIfAuthenticated::class);
    
    // ADMIN GROUP MİDDLEWARE
    Route::middleware(['auth', 'role:admin'])->group(function () { 

        //PROPERTY TYPE ROUTE
        Route::controller(PropertyTypeController::class)->group(function(){
            Route::get('/all/type', 'AllType')->name('all.type');
            Route::get('/add/type', 'AddType')->name('add.type');
            Route::post('/store/type', 'StoreType')->name('store.type');
            Route::get('/edit/type/{id}', 'EditType')->name('edit.type');
            Route::post('/update/type', 'UpdateType')->name('update.type');
            Route::get('/delete/type/{id}', 'DeleteType')->name('delete.type');
        });

        // AMENİTİES ALL ROUTE
        Route::controller(PropertyTypeController::class)->group(function(){
            Route::get('/all/amenitie', 'AllAmenitie')->name('all.amenitie');
            Route::get('/add/amenitie', 'AddAmenitie')->name('add.amenitie');
            Route::post('/store/amenitie', 'StoreAmenitie')->name('store.amenitie');
            Route::get('/edit/amenitie/{id}', 'EditAmenitie')->name('edit.amenitie');
            Route::post('/update/amenitie', 'UpdateAmenitie')->name('update.amenitie');
            Route::get('/delete/amenitie/{id}', 'DeleteAmenitie')->name('delete.amenitie');
        });

        // PROPERTY ALL ROUTE
        Route::controller(PropertyController::class)->group(function(){
            Route::get('/all/property', 'AllProperty')->name('all.property');
            Route::get('/add/property', 'AddProperty')->name('add.property');
            Route::post('/store/property', 'StoreProperty')->name('store.property');
            Route::get('/edit/property/{id}', 'EditProperty')->name('edit.property');
            Route::post('/update/property', 'UpdateProperty')->name('update.property');
            Route::post('/update/property/thumbnail', 'UpdatePropertyThumbnail')->name('update.property.thumbnail');
            Route::post('/update/property/multiimage', 'UpdatePropertyMultiImage')->name('update.property.multiimage');
            Route::get('/property/multiimage/delete/{id}', 'PropertyMultiImageDelete')->name('property.multiimage.delete');
            Route::post('/store/new/multiimage', 'StoreNewMultiImage')->name('store.new.multiimage');
            Route::post('/update/property/facilities', 'UpdatePropertyFacilities')->name('update.property.facilities');
            Route::get('/delete/property/{id}', 'DeleteProperty')->name('delete.property');
            Route::get('/details/property/{id}', 'DetailsProperty')->name('details.property');
            Route::post('/inactive/property', 'InactiveProperty')->name('inactive.property');
            Route::post('/active/property', 'ActiveProperty')->name('active.property');
            Route::get('/admin/package/history', 'AdminPackageHistory')->name('admin.package.history');
            Route::get('/admin/package/invoice/{id}', 'AdminPackageInvoice')->name('admin.package.invoice');
            //Message Route
            Route::get('/admin/property/message/', 'AdminPropertyMessage')->name('admin.property.message');
        });

        //ADMİN AGENT ALL ROUTE
        Route::controller(AdminController::class)->group(function(){
            Route::get('/all/agent', 'AllAgent')->name('all.agent');
            Route::get('/add/agent', 'AddAgent')->name('add.agent');
            Route::post('/store/agent', 'StoreAgent')->name('store.agent');
            Route::get('/edit/agent/{id}', 'EditAgent')->name('edit.agent');
            Route::post('/update/agent', 'UpdateAgent')->name('update.agent');
            Route::get('/delete/agent/{id}', 'DeleteAgent')->name('delete.agent');
            Route::get('/changeStatus', 'changeStatus');
        });

        //STATE ALL ROUTE
        Route::controller(StateController::class)->group(function(){
            Route::get('/all/state', 'AllState')->name('all.state');
            Route::get('/add/state', 'AddState')->name('add.state');
            Route::post('/store/state', 'StoreState')->name('store.state');
            Route::get('/edit/state/{id}', 'EditState')->name('edit.state');
            Route::post('/update/state', 'UpdateState')->name('update.state');
            Route::get('/delete/state/{id}', 'DeleteState')->name('delete.state');
        });

        //TESTİMONİAL ALL ROUTE
        Route::controller(TestimonialController::class)->group(function(){
            Route::get('/all/testimonials', 'AllTestimonials')->name('all.testimonials');
            Route::get('/add/testimonials', 'AddTestimonials')->name('add.testimonials');
            Route::post('/store/testimonials', 'StoreTestimonials')->name('store.testimonials');
            Route::get('/edit/testimonials/{id}', 'EditTestimonials')->name('edit.testimonials');
            Route::post('/update/testimonials', 'UpdateTestimonials')->name('update.testimonials');
        });

        //BLOG CATEGORY ALL ROUTE
        Route::controller(BlogController::class)->group(function(){
            Route::get('/all/blog/category', 'AllBlogCategory')->name('all.blog.category');
            Route::post('/store/blog/category', 'StoreBlogCategory')->name('store.blog.category');
            Route::get('/blog/category/{id}', 'EditBlogCategory');
            Route::post('/update/blog/category', 'UpdateBlogCategory')->name('update.blog.category');
        });

        //BLOG ALL ROUTE
        Route::controller(BlogController::class)->group(function(){
            Route::get('/all/post', 'AllPost')->name('all.post');
            Route::get('/add/post', 'AddPost')->name('add.post');
            Route::post('/store/post', 'StorePost')->name('store.post');
            Route::get('/edit/post/{id}', 'EditPost')->name('edit.post');
            Route::post('/update/post', 'UpdatePost')->name('update.post');
            Route::get('/delete/post/{id}', 'DeletePost')->name('delete.post');
        });
    });


    // Frontend Property Details All Route 
    Route::get('/property/details/{id}/{slug}', [IndexController::class, 'PropertyDetails']); 

    // Frontend Property Wishlist All Route
    Route::post('/add-to-wishList/{property_id}', [WishlistController::class, 'AddToWishlist']); 

    // Frontend Property Compare All Route
    Route::post('/add-to-compare/{property_id}', [CompareController::class, 'AddToCompare']);
    
    // Frontend Send Message All Route
    Route::post('/property/message', [IndexController::class, 'PropertyMessage'])->name('property.message'); 

    // Agent details page in frontend All Route
    Route::get('/agent/details/{id}', [IndexController::class, 'AgentDetails'])->name('agent.details'); 

    // Agent details send message All Route
    Route::post('/agent/details/message', [IndexController::class, 'AgentDetailsMessage'])->name('agent.details.message'); 
    
    // Agent details get all rents All Route
    Route::get('/rent/property', [IndexController::class, 'RentProperty'])->name('rent.property'); 

    // Agent details get all buy All Route
    Route::get('/buy/property', [IndexController::class, 'BuyProperty'])->name('buy.property');

    // Get all property type data All Route
    Route::get('/property/type/{id}', [IndexController::class, 'PropertyType'])->name('property.type');

    // Get state details data All Route
    Route::get('/state/details/{id}', [IndexController::class, 'StateDetails'])->name('state.details');

    // Home page buy search All Route
    Route::post('/buy/property/search', [IndexController::class, 'BuyPropertySearch'])->name('buy.property.search');

    // Home page rent search All Route
    Route::post('/rent/property/search', [IndexController::class, 'RentPropertySearch'])->name('rent.property.search');
    
    // Blog details All Route
    Route::get('/blog/details/{slug}', [BlogController::class, 'BlogDetails']);