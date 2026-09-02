<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DesignerTaskController;
use App\Http\Controllers\DesignFileController;
use App\Http\Controllers\OwnerReviewController;
use App\Http\Controllers\DesignerMonitoringController;

use App\Http\Controllers\Designer\DashboardController as DesignerDashboardController;

use App\Http\Controllers\Cameraman\DashboardController as CameramanDashboardController;
use App\Http\Controllers\Cameraman\PhotoTaskController;

use App\Http\Controllers\ProductPhotoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderReferenceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\CameramanMonitoringController;
use App\Http\Controllers\ReportController;


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();


    if ($user->hasRole('owner')) {

        return redirect()->route('owner.dashboard');

    }


    if ($user->hasRole('admin')) {

        return redirect()->route('admin.dashboard');

    }


    if ($user->hasRole('designer')) {

        return redirect()->route('designer.dashboard');

    }


    if ($user->hasRole('cameraman')) {

        return redirect()->route('cameraman.dashboard');

    }


    return redirect()->route('login');

});


/*
|--------------------------------------------------------------------------
| OWNER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner'])->group(function () {


    Route::get(
    '/owner/reports',
    [ReportController::class, 'index']
)->name('owner.reports');

Route::get(
    '/owner/reports/export/pdf',
    [ReportController::class, 'exportPdf']
)->name('owner.reports.export.pdf');

Route::get(
    '/owner/reports/export/excel',
    [ReportController::class, 'exportExcel']
)->name('owner.reports.export.excel');

    /*
    |--------------------------------------------------------------------------
    | Owner Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/owner/dashboard',
        [OwnerDashboardController::class, 'index']
    )->name('owner.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Owner Cameraman Monitoring
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/owner/cameramen',
        [CameramanMonitoringController::class, 'index']
    )->name('owner.cameramen.index');


    Route::get(
        '/owner/cameramen/{cameraman}',
        [CameramanMonitoringController::class, 'show']
    )->name('owner.cameramen.show');


    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'customers',
        CustomerController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    |
    | Owner can create, view index, edit, update and delete orders.
    | Order show() is shared.
    |
    */

    Route::resource(
        'orders',
        OrderController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | Owner Order References
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/orders/{order}/references',
        [OrderReferenceController::class, 'store']
    )->name('orders.references.store');


    Route::delete(
        '/references/{reference}',
        [OrderReferenceController::class, 'destroy']
    )->name('orders.references.destroy');


    Route::get(
        '/references/{reference}/download',
        [OrderReferenceController::class, 'download']
    )->name('orders.references.download');


    Route::get(
        '/references/{reference}/preview',
        [OrderReferenceController::class, 'preview']
    )->name('orders.references.preview');

});


/*
|--------------------------------------------------------------------------
| OWNER ONLY — DESIGN APPROVAL
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Only Owner can approve or request revision.
|
*/

Route::middleware(['auth', 'role:owner'])->group(function () {


    Route::patch(
        '/orders/{order}/approve',
        [OwnerReviewController::class, 'approve']
    )->name('orders.approve');


    Route::patch(
        '/orders/{order}/revision',
        [OwnerReviewController::class, 'revision']
    )->name('orders.revision');

});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/dashboard',
        [AdminDashboardController::class, 'index']
    )->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin Cameraman Monitoring
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/cameramen',
        [CameramanMonitoringController::class, 'index']
    )->name('admin.cameramen.index');


    Route::get(
        '/admin/cameramen/{cameraman}',
        [CameramanMonitoringController::class, 'show']
    )->name('admin.cameramen.show');


    /*
    |--------------------------------------------------------------------------
    | Admin Order Management
    |--------------------------------------------------------------------------
    |
    | Admin can create, view, edit, update and delete orders.
    | Approval remains Owner-only.
    |
    */

    Route::resource(
        'admin/orders',
        OrderController::class
    )
    ->except(['show'])
    ->names('admin.orders');


    /*
    |--------------------------------------------------------------------------
    | Admin Order Details
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/orders/{order}',
        [OrderController::class, 'show']
    )->name('admin.orders.show');


    /*
    |--------------------------------------------------------------------------
    | Admin Customer Management
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'admin/customers',
        CustomerController::class
    )->names('admin.customers');

});


/*
|--------------------------------------------------------------------------
| OWNER + ADMIN — ORDER OPERATIONS
|--------------------------------------------------------------------------
|
| Admin can help Owner with operational tasks.
| Approval remains Owner-only.
|
*/

Route::middleware(['auth', 'role:owner|admin'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Production / Delivery Status
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/orders/{order}/ready-hq',
        [OrderController::class, 'readyAtHQ']
    )->name('orders.readyHQ');


    Route::patch(
        '/orders/{order}/dispatch',
        [OrderController::class, 'dispatchDelivery']
    )->name('orders.dispatch');


    Route::patch(
        '/orders/{order}/ready-pickup',
        [OrderController::class, 'readyForPickup']
    )->name('orders.readyPickup');


    Route::patch(
        '/orders/{order}/mark-delivered',
        [OrderController::class, 'markDelivered']
    )->name('orders.markDelivered');


    Route::patch(
        '/orders/{order}/confirm-pickup',
        [OrderController::class, 'confirmPickup']
    )->name('orders.confirmPickup');


    /*
    |--------------------------------------------------------------------------
    | Designer Monitoring
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/designer-monitoring',
        [DesignerMonitoringController::class, 'index']
    )->name('designer.monitoring');


    Route::get(
        '/designer-monitoring/{designer}',
        [DesignerMonitoringController::class, 'show']
    )->name('designer.monitoring.show');


    /*
    |--------------------------------------------------------------------------
    | Cameraman Assignment
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/orders/{order}/assign-cameraman',
        [OrderController::class, 'assignCameraman']
    )->name('orders.assignCameraman');

});


/*
|--------------------------------------------------------------------------
| DESIGNER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:designer'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Designer Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/designer/dashboard',
        [DesignerDashboardController::class, 'index']
    )->name('designer.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Designer Tasks
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/designer/tasks',
        [DesignerTaskController::class, 'index']
    )->name('designer.task');


    Route::patch(
        '/designer/tasks/{order}/start',
        [DesignerTaskController::class, 'startTask']
    )->name('designer.task.start');


    Route::get(
        '/designer/tasks/{order}',
        [DesignerTaskController::class, 'show']
    )->name('designer.task.show');


    /*
    |--------------------------------------------------------------------------
    | Submit Design
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/designer/orders/{order}/submit',
        [DesignerTaskController::class, 'submitForApproval']
    )->name('designer.orders.submit');


    Route::patch(
        '/designer/tasks/{order}/submit',
        [DesignerTaskController::class, 'submitForApproval']
    )->name('designer.tasks.submit');


    /*
    |--------------------------------------------------------------------------
    | Designer Files
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/designer/orders/{order}/designs',
        [DesignFileController::class, 'store']
    )->name('designer.designs.store');


    Route::delete(
        '/designer/designs/{designFile}',
        [DesignFileController::class, 'destroy']
    )->name('designer.designs.destroy');

});


/*
|--------------------------------------------------------------------------
| CAMERAMAN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:cameraman'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Cameraman Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/cameraman/dashboard',
        [CameramanDashboardController::class, 'index']
    )->name('cameraman.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Cameraman Tasks
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/cameraman/tasks',
        [PhotoTaskController::class, 'index']
    )->name('cameraman.tasks');


    Route::get(
        '/cameraman/tasks/{order}',
        [PhotoTaskController::class, 'show']
    )->name('cameraman.tasks.show');


    /*
    |--------------------------------------------------------------------------
    | Start Photo Session
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/cameraman/tasks/{order}/start',
        [PhotoTaskController::class, 'start']
    )->name('cameraman.tasks.start');


    /*
    |--------------------------------------------------------------------------
    | Complete Photo Session
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/cameraman/tasks/{order}/complete',
        [PhotoTaskController::class, 'complete']
    )->name('cameraman.tasks.complete');


    /*
    |--------------------------------------------------------------------------
    | Upload Product Photos
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/cameraman/photos/{order}',
        [ProductPhotoController::class, 'store']
    )->name('cameraman.photos.store');


    /*
    |--------------------------------------------------------------------------
    | Delete Product Photo
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/cameraman/photos/{photo}',
        [ProductPhotoController::class, 'destroy']
    )->name('cameraman.photos.destroy');

});


/*
|--------------------------------------------------------------------------
| SHARED
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Order Details
    |--------------------------------------------------------------------------
    |
    | Owner, Admin and Designer can view Order Details.
    |
    */

    Route::get(
        '/orders/{order}',
        [OrderController::class, 'show']
    )
    ->middleware('role:owner|admin|designer')
    ->name('orders.show');


    /*
    |--------------------------------------------------------------------------
    | Designer Files
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/designs/{designFile}/download',
        [DesignFileController::class, 'download']
    )->name('designs.download');


    Route::get(
        '/designs/{designFile}/preview',
        [DesignFileController::class, 'preview']
    )->name('designs.preview');


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications',
        [NotificationController::class, 'index']
    )->name('notifications.index');


    Route::patch(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    )->name('notifications.readAll');


    Route::patch(
        '/notifications/{notification}/read',
        [NotificationController::class, 'read']
    )->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | Quick Customer
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/orders/quick-customer',
        [CustomerController::class, 'quickStore']
    )->name('orders.quickCustomer');


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'products',
        ProductController::class
    )->except(['show']);


    Route::patch(
        '/products/{product}/toggle',
        [ProductController::class, 'toggle']
    )->name('products.toggle');


    /*
    |--------------------------------------------------------------------------
    | Repeat Order
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/orders/{order}/repeat',
        [OrderController::class, 'repeat']
    )->name('orders.repeat');


    Route::post(
        '/orders/{order}/repeat',
        [OrderController::class, 'storeRepeat']
    )->name('orders.storeRepeat');


    /*
    |--------------------------------------------------------------------------
    | Job Order
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/orders/{order}/job-order/create',
        [JobOrderController::class, 'create']
    )
    ->middleware('role:designer')
    ->name('job-orders.create');


    Route::post(
        '/orders/{order}/job-order',
        [JobOrderController::class, 'store']
    )
    ->middleware('role:designer')
    ->name('job-orders.store');


    /*
    |--------------------------------------------------------------------------
    | Generate Job Order Word
    |--------------------------------------------------------------------------
    |
    | Designer, Owner and Admin can generate Word.
    |
    */

    Route::get(
        '/job-orders/{jobOrder}/generate-word',
        [JobOrderController::class, 'generateWord']
    )
    ->middleware('role:designer|owner|admin')
    ->name('job-orders.generate-word');


    /*
    |--------------------------------------------------------------------------
    | Delete Job Order
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/job-orders/{jobOrder}',
        [JobOrderController::class, 'destroy']
    )
    ->middleware('role:designer|owner|admin')
    ->name('job-orders.destroy');

});


require __DIR__ . '/auth.php';