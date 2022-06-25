<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Autho Routes
require __DIR__.'/auth.php';

// Atom/ RSS Feed Routes
Route::feeds();
// Language Switch
Route::get('language/{language}', 'LanguageController@switch')->name('language.switch');

/*
*
* Frontend Routes
*
* --------------------------------------------------------------------
*/
Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::get('/', 'FrontendController@index')->name('index');
	Route::post('order-complete1', 'FrontendController@orderCompletePost');
    Route::get('home', 'FrontendController@index')->name('home');
    Route::get('privacy', 'FrontendController@privacy')->name('privacy');
    Route::get('terms', 'FrontendController@terms')->name('terms');
    Route::get('return', 'FrontendController@Returnp')->name('return');
	Route::get('about', 'FrontendController@about')->name('about');
	Route::get('contact', 'FrontendController@contact')->name('contact');
	Route::get('product/{slug}/{id}', 'FrontendController@product')->name('product');
Route::get('shop/{category?}', 'FrontendController@shop');
Route::get('shop/all/{category?}', 'FrontendController@shopAll');
Route::get('my-account', 'FrontendController@register')->name('register');
Route::post('registerpost','FrontendController@registerPost')->name('post.register');
Route::get('/verification/token/{token}/{email}', 'FrontendController@AccountVerify');
Route::get('/resend-activation/{email}/{fname}', 'FrontendController@ResendActivation');
Route::get('forget-password', 'FrontendController@forgetPassword');
Route::post('reset-password', 'FrontendController@forgetPasswordPost');	
Route::get('reset-password/{token}/{email}', 'FrontendController@resetPassword');	
Route::post('resetpassword/', 'FrontendController@resetPasswordPost');	

Route::get('login-user', 'FrontendController@UserLoginCustom'); 	
Route::get('tutorial', 'FrontendController@Tutorial'); 	 

Route::get('school-registeration', 'FrontendController@SchoolReg');	
Route::get('office-registeration', 'FrontendController@OfficeReg');	

Route::post('getcitiesbyidajax', 'FrontendController@getCitiesbyid');	
Route::post('getschoolbycityidajax', 'FrontendController@getSchoolbyCityid');	
Route::post('getshipping', 'FrontendController@getShippingCharges');	



//ajax rout add to cart
Route::post('addtocart', 'FrontendController@addToCart');
Route::post('getcartnumber', 'FrontendController@cartNumber');
Route::post('getcarttotal', 'FrontendController@carttTotal');
Route::post('getcartlist', 'FrontendController@cartList');
Route::get('cartitemremove/{id}', 'FrontendController@cartRemove');

Route::get('cart', 'FrontendController@cart');
Route::post('updatecart', 'FrontendController@updateCart');

Route::get('checkout', 'FrontendController@checkout');
Route::post('checkoutpost', 'FrontendController@checkoutPost');
 
Route::get('order-complete/{orderid}', 'FrontendController@orderComplete');
Route::post('order-complete', 'FrontendController@orderCompletePost');
Route::get('payment/{orderid}', 'FrontendController@PendingPayment');
Route::get('order-view/{orderid}', 'FrontendController@orderView');
Route::get('search', 'FrontendController@Search'); 

Route::post('send-message', 'FrontendController@SendMsg');
Route::get('new-registration','FrontendController@newReg');


Route::get('user-login', 'FrontendController@Login');
Route::post('/dologin', 'FrontendController@UserLogin');
  
  Route::group(['middleware' => 'auth'], function() {
        Route::get('/user-area', 'FrontendController@UserArea');
        Route::post('/userupdate', 'FrontendController@UserUpdate');

        Route::get('/changepass', 'HomeController@ChangePass');
        Route::post('/updatepass', 'HomeController@UpdatePass');
        Route::get('/customer-orders', 'HomeController@CustomerOrders');
        Route::get('/orderview/{orderno}', 'HomeController@OrderView');
        Route::get('/wishlist-view', 'HomeController@WishlistView');
        Route::get('/removewishlist/{id}', 'HomeController@RemoveWishlist');
        Route::get('logout', 'FrontendController@logOut');

    });
	
	

    Route::group(['middleware' => ['auth']], function () {
        /*
        *
        *  Users Routes
        *
        * ---------------------------------------------------------------------
        */
        $module_name = 'users';
        $controller_name = 'UserController';
        Route::get('profile/{id}', ['as' => "$module_name.profile", 'uses' => "$controller_name@profile"]);
        Route::get('profile/{id}/edit', ['as' => "$module_name.profileEdit", 'uses' => "$controller_name@profileEdit"]);
        Route::patch('profile/{id}/edit', ['as' => "$module_name.profileUpdate", 'uses' => "$controller_name@profileUpdate"]);
        Route::get("$module_name/emailConfirmationResend/{id}", ['as' => "$module_name.emailConfirmationResend", 'uses' => "$controller_name@emailConfirmationResend"]);
        Route::get('profile/changePassword/{username}', ['as' => "$module_name.changePassword", 'uses' => "$controller_name@changePassword"]);
        Route::patch('profile/changePassword/{username}', ['as' => "$module_name.changePasswordUpdate", 'uses' => "$controller_name@changePasswordUpdate"]);
        Route::delete('users/userProviderDestroy', ['as' => 'users.userProviderDestroy', 'uses' => 'UserController@userProviderDestroy']);
    });
});

/*
*
* Backend Routes
* These routes need view-backend permission
* --------------------------------------------------------------------
*/
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'as' => 'backend.', 'middleware' => ['auth', 'can:view_backend']], function () {

    /**
     * Backend Dashboard
     * Namespaces indicate folder structure.
     */
Route::get('/', 'BackendController@index')->name('home');
    Route::get('dashboard', 'BackendController@index')->name('dashboard');


    //products routes
    Route::get('products', 'ProductController@products')->name('products');
    Route::get('add-class-product/{sid}/{cid}', 'ProductController@addNewProuct');
    Route::post('add-product-post', 'ProductController@addNewProuctPost');
    Route::get('edit-product/{id}/{sid}/{cid}', 'ProductController@editProuct');
    Route::post('attrajaxdelete', 'ProductController@attrDelete');
    Route::post('editproductpost', 'ProductController@editProuctPost');
    Route::get('delete-product/{id}', 'ProductController@deleteProduct');

    //schools
     Route::get('schools', 'SchoolController@schools')->name('schools');
    Route::get('add-school', 'SchoolController@addNewSchool');
    Route::post('add-school-post', 'SchoolController@addNewSchoolPost');
    Route::get('edit-school/{id}', 'SchoolController@editSchool');
    Route::post('editschoolpost', 'SchoolController@editSchoolPost');
    Route::get('delete-school/{id}', 'SchoolController@deleteSchool');
	Route::get('delete-owner/{id}', 'SchoolController@deleteOwner');
	 //schools
     Route::get('states', 'SchoolController@states')->name('states');
    Route::get('add-state', 'SchoolController@addNewState');
    Route::post('add-state-post', 'SchoolController@addNewStatePost');
    Route::get('edit-state/{id}', 'SchoolController@editState');
    Route::post('editstatepost', 'SchoolController@editStatePost');
    Route::get('delete-state/{id}', 'SchoolController@deleteState');
	
	//statecity
    Route::get('add-state-city/{id}', 'SchoolController@addNewStateCity');
    Route::post('add-city-post', 'SchoolController@addNewCityPost');
    Route::get('edit-city/{sid}/{cid}', 'SchoolController@EditCity');
    Route::post('edit-city-post', 'SchoolController@editCityPost');
    Route::post('edit-city-post', 'SchoolController@editCityPost');
    Route::get('delete-city/{id}', 'SchoolController@deleteCity');

    //classes
    Route::get('add-school-class/{id}', 'SchoolController@addNewSchoolClass');
    Route::post('add-class-post', 'SchoolController@addNewClassPost');
    Route::get('edit-class/{sid}/{cid}', 'SchoolController@EditClass');
    Route::post('edit-class-post', 'SchoolController@editClassPost');
    Route::post('edit-class-post', 'SchoolController@editClassPost');
    Route::get('delete-class/{id}', 'SchoolController@deleteClass');

    //categories
    Route::get('allcategories', 'SchoolController@Categories')->name('allcategories');
    Route::post('add-category-post', 'SchoolController@addNewCategoryPost');
    Route::get('edit-category/{id}/{pid?}', 'SchoolController@editCategory');
    Route::post('editcategorypost', 'SchoolController@editCatPost');
    Route::get('delete-category/{id}', 'SchoolController@deleteCategory');
    Route::get('add-subcat/{id}/{pid?}', 'SchoolController@addSubCat');
	
	//shipping charges
	Route::get('shipping', 'SchoolController@Shipping')->name('shipping');
	Route::post('shippingpost', 'SchoolController@ShippingPost');
	
	//orders
	 Route::get('orders-list', 'BackendController@OrdersList')->name('orderslist');
	 Route::get('ordersview/{orderid}', 'BackendController@OrderView');
	 Route::get('orderdelete/{orderid}', 'BackendController@OrderDelete');
	 
	 //pending request
	 Route::get('pending-request', 'BackendController@PendingReq')->name('pendingusers');
	 Route::get('pendingaction/{id}/{action}', 'BackendController@PendingAct');
	 Route::post('changeorderstatus', 'BackendController@ChangeOrderStatus');
	 Route::post('changeordertracking', 'BackendController@ChangeOrdertracking');
	 
	 Route::get('stock-report', 'BackendController@Stockreports')->name('admin.reports.stock');
	  Route::post('getclasses', 'BackendController@getClasses');
	 Route::post('getcats', 'BackendController@getCats'); 
	 Route::post('getprods', 'BackendController@getProds');
	 
	 Route::get('deluser/{userid}','BackendController@delUser');
	 
	 Route::get('sale-report', 'BackendController@Salereports');
	 Route::post('getcatsalereports', 'BackendController@Salereportscat');
	 
	 Route::get('saleman-report', 'BackendController@Salemanreport');
	 Route::post('saleman-report-post', 'BackendController@Salemanreportpost');
	 Route::get('orderslist/{userid}', 'BackendController@OrdersofSingle');
	
	 Route::get('comission', 'BackendController@Comissionreports');
	 Route::post('getcomissionbyrole', 'BackendController@Comissionreportbyrole');
	 
	 Route::get('allusers', ['uses' => 'BackendController@AllUsers','as' => 'backend.admin.allusers' ]);
	 //comissions
	 
	
    /*
     *
     *  Settings Routes
     *
     * ---------------------------------------------------------------------
     */
    Route::group(['middleware' => ['permission:edit_settings']], function () {
        $module_name = 'settings';
        $controller_name = 'SettingController';
        Route::get("$module_name", "$controller_name@index")->name("$module_name");
        Route::post("$module_name", "$controller_name@store")->name("$module_name.store");
    });

    /*
    *
    *  Notification Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'notifications';
    $controller_name = 'NotificationsController';
    Route::get("$module_name", ['as' => "$module_name.index", 'uses' => "$controller_name@index"]);
    Route::get("$module_name/markAllAsRead", ['as' => "$module_name.markAllAsRead", 'uses' => "$controller_name@markAllAsRead"]);
    Route::delete("$module_name/deleteAll", ['as' => "$module_name.deleteAll", 'uses' => "$controller_name@deleteAll"]);
    Route::get("$module_name/{id}", ['as' => "$module_name.show", 'uses' => "$controller_name@show"]);

    /*
    *
    *  Backup Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'backups';
    $controller_name = 'BackupController';
    Route::get("$module_name", ['as' => "$module_name.index", 'uses' => "$controller_name@index"]);
    Route::get("$module_name/create", ['as' => "$module_name.create", 'uses' => "$controller_name@create"]);
    Route::get("$module_name/download/{file_name}", ['as' => "$module_name.download", 'uses' => "$controller_name@download"]);
    Route::get("$module_name/delete/{file_name}", ['as' => "$module_name.delete", 'uses' => "$controller_name@delete"]);

    /*
    *
    *  Roles Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'roles';
    $controller_name = 'RolesController';
    Route::resource("$module_name", "$controller_name");

    /*
    *
    *  Users Routes
    *
    * ---------------------------------------------------------------------
    */
    $module_name = 'users';
    $controller_name = 'UserController';
    Route::get("$module_name/profile/{id}", ['as' => "$module_name.profile", 'uses' => "$controller_name@profile"]);
    Route::get("$module_name/profile/{id}/edit", ['as' => "$module_name.profileEdit", 'uses' => "$controller_name@profileEdit"]);
    Route::patch("$module_name/profile/{id}/edit", ['as' => "$module_name.profileUpdate", 'uses' => "$controller_name@profileUpdate"]);
    Route::get("$module_name/emailConfirmationResend/{id}", ['as' => "$module_name.emailConfirmationResend", 'uses' => "$controller_name@emailConfirmationResend"]);
    Route::delete("$module_name/userProviderDestroy", ['as' => "$module_name.userProviderDestroy", 'uses' => "$controller_name@userProviderDestroy"]);
    Route::get("$module_name/profile/changeProfilePassword/{id}", ['as' => "$module_name.changeProfilePassword", 'uses' => "$controller_name@changeProfilePassword"]);
    Route::patch("$module_name/profile/changeProfilePassword/{id}", ['as' => "$module_name.changeProfilePasswordUpdate", 'uses' => "$controller_name@changeProfilePasswordUpdate"]);
    Route::get("$module_name/changePassword/{id}", ['as' => "$module_name.changePassword", 'uses' => "$controller_name@changePassword"]);
    Route::patch("$module_name/changePassword/{id}", ['as' => "$module_name.changePasswordUpdate", 'uses' => "$controller_name@changePasswordUpdate"]);
    Route::get("$module_name/trashed", ['as' => "$module_name.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/trashed/{id}", ['as' => "$module_name.restore", 'uses' => "$controller_name@restore"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::get("$module_name/index_list", ['as' => "$module_name.index_list", 'uses' => "$controller_name@index_list"]);
    Route::resource("$module_name", "$controller_name");
    Route::patch("$module_name/{id}/block", ['as' => "$module_name.block", 'uses' => "$controller_name@block", 'middleware' => ['permission:block_users']]);
    Route::patch("$module_name/{id}/unblock", ['as' => "$module_name.unblock", 'uses' => "$controller_name@unblock", 'middleware' => ['permission:block_users']]);
});

Route::group(['namespace' => 'Backend', 'prefix' => 'staff', 'as' => 'backend.', 'middleware' => ['auth', 'backend']], function () {
	 Route::get('/dashboard', 'StaffController@index');
	 Route::get('/new-order', 'StaffController@PlceOrder');
	 Route::post('getclasses', 'StaffController@getClasses');
	 Route::post('getcats', 'StaffController@getCats');
	 Route::post('getprods', 'StaffController@getProds');
	 Route::post('getcatprods', 'StaffController@getCatProds');
	 Route::get('orderpending/{orderid}', 'StaffController@orderPending');
	 Route::get('cart', 'StaffController@cart');
	 Route::get('checkout', 'StaffController@checkout');
	 Route::post('checkoutpost', 'StaffController@checkoutPost');
	 Route::get('order-complete/', 'StaffController@orderComplete');
	 Route::post('order-complete/', 'StaffController@orderCompletePost');
	 Route::get('payment/{orderid}', 'StaffController@doPayment');
	 Route::get('userprofile', 'StaffController@UserProfileStaff');
	 Route::post('userprofilepost', 'StaffController@UserProfileStaffPost');
	 Route::get('orders-list', 'StaffController@OrdersList');
	 Route::get('ordersview/{orderid}', 'StaffController@OrderView');
	 Route::get('orderdelete/{orderid}', 'StaffController@OrderDelete');
	 Route::get('reqform/{orderid}', 'StaffController@Reqform');
	 Route::post('uploadattachment', 'StaffController@UploadAttach');
	 Route::get('commission-report', 'StaffController@CommissionReport');
	 Route::get('commission-filter', 'StaffController@CommissionSearch');
	 
});
