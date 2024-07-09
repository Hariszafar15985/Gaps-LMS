<?php

use Illuminate\Support\Facades\Route;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Xero;

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

Route::group(['namespace' => 'Auth', 'middleware' => ['share']], function () {
    Route::get('/login', 'LoginController@showLoginForm')->name('web.login');
    Route::post('/login', 'LoginController@login');
    Route::get('/logout', 'LoginController@logout');
    // Route::get('/register', 'RegisterController@showRegistrationForm');
    // Route::post('/register', 'RegisterController@register');
    Route::get('/verification', 'VerificationController@index');
    Route::post('/verification', 'VerificationController@confirmCode');
    Route::get('/verification/resend', 'VerificationController@resendCode');
    Route::get('/forget-password', 'ForgotPasswordController@showLinkRequestForm');
    Route::post('/send-email', 'ForgotPasswordController@forgot');
    Route::get('reset-password/{token}', 'ResetPasswordController@getPassword');
    Route::post('/reset-password', 'ResetPasswordController@updatePassword');
    Route::get('/google', 'SocialiteController@redirectToGoogle');
    Route::get('/google/callback', 'SocialiteController@handleGoogleCallback');
    Route::get('/facebook/redirect', 'SocialiteController@redirectToFacebook');
    Route::get('/facebook/callback', 'SocialiteController@handleFacebookCallback');
    Route::get('/reff/{code}', 'ReferralController@referral');
});


Route::group(['namespace' => 'Web', 'middleware' => ['impersonate', 'share']], function () {
    Route::get('/stripe', function () {
        return view('web.default.cart.channels.stripe');
    });

    Route::fallback(function () {
        return view("errors.404", ['pageTitle' => trans('public.error_404_page_title')]);
    });
    // set Locale
    Route::post('/locale', 'LocaleController@setLocale');

    //Route::get('/', 'HomeController@index');
    Route::get('/', function () {
        return redirect('/login');
    });

    Route::group(['prefix' => 'course'], function () {
        Route::get('/{slug}', 'WebinarController@course')->middleware(['web.auth', 'webinar.access'])->name("webinars");
        Route::get('/{slug}/file/{file_id}/download', 'WebinarController@downloadFile');
        Route::get('/{slug}/lessons/{lesson_id}/read', 'WebinarController@getLesson')->middleware("drip-feed")->name('web.lesson.read');
        Route::post('/getFilePath', 'WebinarController@getFilePath');
        Route::get('/{slug}/file/{file_id}/play', 'WebinarController@playFile')->middleware('drip-file');
        Route::get('/{slug}/file/{file_id}/showHtml', 'WebinarController@showHtmlFile')->middleware('drip-file');
        Route::get('/{slug}/free', 'WebinarController@free');
        Route::post('/{id}/report', 'WebinarController@reportWebinar');
        Route::post('/{id}/learningStatus', 'WebinarController@learningStatus')->name('webinar.learningStatus');
        Route::post('/add-notes', 'CourseNotesController@saveNotes')->name("add.notes");
    });

    Route::group(['prefix' => 'certificate_validation'], function () {
        Route::get('/', 'CertificateValidationController@index');
        Route::post('/validate', 'CertificateValidationController@checkValidate');
    });

    Route::group(['middleware' => 'web.auth'], function () {

        Route::group(['prefix' => 'laravel-filemanager'], function () {
            \UniSharp\LaravelFilemanager\Lfm::routes();
        });

        Route::group(['prefix' => 'reviews'], function () {
            Route::post('/store', 'WebinarReviewController@store');
            Route::post('/store-reply-comment', 'WebinarReviewController@storeReplyComment');
            Route::get('/{id}/delete', 'WebinarReviewController@destroy');
            Route::get('/{id}/delete-comment/{commentId}', 'WebinarReviewController@destroy');
        });

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('{slug}/toggle', 'FavoriteController@toggle');
            Route::post('/{id}/update', 'FavoriteController@update');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::post('/store', 'CommentController@store');
            Route::post('/{id}/reply', 'CommentController@storeReply');
            Route::post('/{id}/update', 'CommentController@update');
            Route::post('/{id}/report', 'CommentController@report');
            Route::get('/{id}/delete', 'CommentController@destroy');
        });

        Route::group(['prefix' => 'cart'], function () {
            Route::get('/', 'CartController@index');
            Route::post('/store', 'CartController@store');
            Route::get('/{id}/delete', 'CartController@destroy');

            Route::post('/coupon/validate', 'CartController@couponValidate');
            Route::post('/checkout', 'CartController@checkout')->name('checkout');
        });

        Route::group(['prefix' => 'meetings'], function () {
            Route::post('/reserve', 'MeetingController@reserve');
        });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/{id}/follow', 'UserController@followToggle');
        });

        Route::group(['prefix' => 'become_instructor'], function () {
            Route::get('/', 'UserController@becomeInstructors');
            Route::post('/', 'UserController@becomeInstructorsStore');
        });

    });

    Route::group(['prefix' => 'users'], function () {
        //middleware added to disallow public access to user profile
        //(admin and instructor can view profile)
        Route::get('/{id}/profile', 'UserController@profile')->middleware('auth')->name('get.user.profile');
        Route::post('/{id}/availableTimes', 'UserController@availableTimes');
        Route::post('/{id}/send-message', 'UserController@sendMessage');
        Route::post('/upload-user-document', 'UserController@uploadUserDocument');
        Route::delete('/delete-user-document', 'UserController@deleteUserDocument');
        Route::get('/{id}/download-enrollment-pdf', 'UserController@downloadEnrollmentPdf');

        Route::post('/addProfileNote', 'UserController@addUpdateProfileNote')->name('panel.add.profile.note');
        Route::post('/removeProfileNote/{id}', 'UserController@removeProfileNote')->name('panel.remove.profile.note');
        Route::post("/student-document-visiblity", "UserController@studentDocVisiblity")->name("student.document.visiblity");

    });

    Route::group(['prefix' => 'payments'], function () {
        Route::post('/payment-request', 'PaymentController@paymentRequest');
        Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentController@paymentVerify']);
        Route::post('/verify/{gateway}', ['as' => 'payment_verify_post', 'uses' => 'PaymentController@paymentVerify']);
        Route::get('/status', 'PaymentController@payStatus');
    });

    Route::group(['prefix' => 'subscribes'], function () {
        Route::get('/apply/{webinarSlug}', 'SubscribeController@apply');
    });

    Route::group(['prefix' => 'search'], function () {
        Route::get('/', 'SearchController@index');
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/{categoryTitle}/{subCategoryTitle?}', 'CategoriesController@index');
    });

    Route::get('/classes', 'ClassesController@index');

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'BlogController@index');
        Route::get('/categories/{category}', 'BlogController@index');
        Route::get('/{slug}', 'BlogController@show');
    });

    Route::group(['prefix' => 'contact'], function () {
        Route::get('/', 'ContactController@index');
        Route::post('/store', 'ContactController@store');
    });

    Route::group(['prefix' => 'instructors'], function () {
        Route::get('/', 'UserController@instructors');
    });

    Route::group(['prefix' => 'organizations'], function () {
        Route::get('/', 'UserController@organizations');
    });

    Route::group(['prefix' => 'load_more'], function () {
        Route::get('/{role}', 'UserController@handleInstructorsOrOrganizationsPage');
    });

    Route::group(['prefix' => 'pages'], function () {
        Route::get('/{link}', 'PagesController@index');
    });

    // Captcha
    Route::group(['prefix' => 'captcha'], function () {
        Route::post('create', function () {
            $response = ['status' => 'success', 'captcha_src' => captcha_src('flat')];

            return response()->json($response);
        });
        Route::get('{config?}', '\Mews\Captcha\CaptchaController@getCaptcha');
    });

    Route::post('/newsletters', 'UserController@makeNewsletter');

    Route::group(['prefix' => 'jobs'], function () {
        Route::get('/{methodName}', 'JobsController@index');
        Route::post('/{methodName}', 'JobsController@index');
    });
});


/**
 * Xero Routes
 */
Route::get('xero/connect', function(){
    return Xero::connect();
});

Route::post('xeroListener', 'Web\XeroWebHookController@index');

Route::get('sendmail', function () {

    $details = [
        'title' => 'Test Mail',
        'body' => 'This is for testing email using smtp'
    ];

    \Mail::to('mr.sajidbwn@gmail.com')->send(new \App\Mail\ConfirmEnrollmentMail($details));

    dd("Email is Sent - Testing.");
});



Route::get('test', function () {
    Log::info('test log');
    dd("Log added");exit;
    // \Artisan::call("StudyInactiveNotification monthly");

});


Route::get("/sample", function(){
    $data = getGeneralSettings();
    // echo "<pre>";
    // print_r($data);
    // exit;
    $generalSettings = $data;
    $generalSettings["student_name"] = "Haris Zafar";
    // return $data;
    \Mail::to('haris.zafar@provelopers.net')->send(new \App\Mail\ConfirmEnrollmentMail($generalSettings));
    dd("Sended");

    return view("web.default.emails.confirm-enrollment");
});
