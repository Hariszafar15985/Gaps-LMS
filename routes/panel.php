<?php

use App\Http\Controllers\Admin\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Panel Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Panel', 'prefix' => 'panel', 'middleware' => ['impersonate', 'panel', 'share']], function () {

    Route::get('/', 'DashboardController@dashboard');

    Route::group(['prefix' => 'users'], function () {
        Route::post('/search', 'UserController@search');
        Route::post('/contact-info', 'UserController@contactInfo');
        Route::post('/offlineToggle', 'UserController@offlineToggle');
    });

    Route::group(['prefix' => 'webinars'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'WebinarController@index');
            Route::get('/new', 'WebinarController@create');
            Route::get('/invitations', 'WebinarController@invitations');
            Route::post('/store', 'WebinarController@store');
            Route::get('/{id}/step/{step?}', 'WebinarController@edit');
            Route::get('/{id}/edit', 'WebinarController@edit')->name('panel_edit_webinar');
            Route::post('/{id}/update', 'WebinarController@update');
            Route::get('/{id}/delete', 'WebinarController@destroy');
            Route::get('/{id}/duplicate', 'WebinarController@duplicate');
            Route::get('/{id}/export-students-list', 'WebinarController@exportStudentsList');
            Route::post('/order-items', 'WebinarController@orderItems');
            Route::post('/{id}/getContentItemByLocale', 'WebinarController@getContentItemByLocale');
        });

        Route::get('/organization_classes', 'WebinarController@organizationClasses');
        Route::get('/{id}/invoice', 'WebinarController@invoice');
        Route::get('/{id}/getNextSessionInfo', 'WebinarController@getNextSessionInfo');
        Route::post('/updateQuizRelation', 'WebinarController@updateQuizRelation');

        Route::group(['prefix' => 'courses'], function () {
            Route::get('/', 'WebinarController@purchases');
            Route::post('/getJoinInfo', 'WebinarController@getJoinInfo');
        });

        Route::post('/search', 'WebinarController@search');

        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'CommentController@myClassComments');
            Route::post('/store', 'CommentController@store');
            Route::post('/{id}/update', 'CommentController@update');
            Route::get('/{id}/delete', 'CommentController@destroy');
            Route::post('/{id}/reply', 'CommentController@reply');
            Route::post('/{id}/report', 'CommentController@report');
        });

        Route::get('/my-comments', 'CommentController@myComments');

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('/', 'FavoriteController@index');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });
    });

    Route::group(['prefix' => 'quizzes'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::get('/', 'QuizController@index');
            Route::get('/new', 'QuizController@create');
            Route::get('/pending', 'QuizController@pending')->name('panel.pending_quizzes');
            Route::post('/store', 'QuizController@store');
            Route::get('/{id}/edit', 'QuizController@edit')->name('panel_edit_quiz');
            Route::post('/{id}/update', 'QuizController@update');
            Route::get('/{id}/delete', 'QuizController@destroy');
            Route::post("/questions/reorder", 'QuizController@reOrderQuestions')->name("panel.questions.sort");

        });
        Route::get('/{id}/start', 'QuizController@start')->middleware("quiz-drip-lock")->name('panel.quizzes.start');
        Route::post('/{id}/store-result', 'QuizController@quizzesStoreResult')->name('panel.quiz.submit');
        Route::get('/{quizResultId}/status', 'QuizController@status')->name('quiz_status');

        Route::get('/my-results', 'QuizController@myResults')->name('panel_quiz_results_list');
        Route::get('/opens', 'QuizController@opens');

        Route::get('/{quizResultId}/result', 'QuizController@showResult')->name('panel_quiz_result_detail');
        Route::get('/quiz/getQuestionAttachment', 'QuizController@downloadQuestionAttachment')->name('get.quiz.attachment');

        Route::group(['prefix' => 'results'], function () {
            Route::get('/', 'QuizController@results');
            Route::get('/{quizResultId}/delete', 'QuizController@destroyQuizResult');
            Route::get('/{quizResultId}/downloadCertificate', 'CertificateController@downloadCertificate');
            Route::get('/{quizResultId}/showCertificate', 'CertificateController@showCertificate');
        });

        Route::get('/{quizResultId}/edit-result', 'QuizController@editResult');
        Route::post('/{quizResultId}/update-result', 'QuizController@updateResult');


    });

    Route::group(['prefix' => 'quizzes-questions'], function () {
        Route::post('/store', 'QuizQuestionController@store');
        Route::get('/{id}/edit', 'QuizQuestionController@edit');
        Route::get('/{id}/getQuestionByLocale', 'QuizQuestionController@getQuestionByLocale');
        Route::post('/{id}/update', 'QuizQuestionController@update');
        Route::get('/{id}/delete', 'QuizQuestionController@destroy');
    });

    Route::group(['prefix' => 'filters'], function () {
        Route::get('/get-by-category-id/{categoryId}', 'FilterController@getByCategoryId');
    });

    Route::group(['prefix' => 'tickets'], function () {
        Route::post('/store', 'TicketController@store');
        Route::post('/{id}/update', 'TicketController@update');
        Route::get('/{id}/delete', 'TicketController@destroy');
    });

    Route::group(['prefix' => 'sessions'], function () {
        Route::post('/store', 'SessionController@store');
        Route::post('/{id}/update', 'SessionController@update');
        Route::get('/{id}/delete', 'SessionController@destroy');
        Route::get('/{id}/joinToBigBlueButton', 'SessionController@joinToBigBlueButton');
    });

    Route::group(['prefix' => 'chapters'], function () {
        Route::get('/{id}', 'ChapterController@getChapter');
        Route::get('/getAllByWebinarId/{webinar_id}', 'ChapterController@getAllByWebinarId');
        Route::post('/store', 'ChapterController@store');
        Route::post('/{id}/update', 'ChapterController@update');
        Route::get('/{id}/delete', 'ChapterController@destroy');
        Route::get('/{id}/duplicate', 'ChapterController@duplicate');
        Route::post('/change', 'ChapterController@change');
    });

    Route::group(['prefix' => 'files'], function () {
        Route::post('/store', 'FileController@store');
        Route::post('/{id}/update', 'FileController@update');
        Route::get('/{id}/delete', 'FileController@destroy');
    });

    Route::group(['prefix' => 'text-lesson'], function () {
        Route::post('/store', 'TextLessonsController@store');
        Route::post('/{id}/update', 'TextLessonsController@update');
        Route::get('/{id}/delete', 'TextLessonsController@destroy');
        Route::get('/{id}/duplicate', 'TextLessonsController@duplicate');
        Route::get('/getAllLessonsByChapterId/{id}', 'TextLessonsController@getAllLessonsByChapterId');
        Route::get('/audio/{id}/delete', 'TextLessonsController@deleteAudio')->name("panel.delete.audio");
    });

    Route::group(['prefix' => 'prerequisites'], function () {
        Route::post('/store', 'PrerequisiteController@store');
        Route::post('/{id}/update', 'PrerequisiteController@update');
        Route::get('/{id}/delete', 'PrerequisiteController@destroy');
    });

    Route::group(['prefix' => 'faqs'], function () {
        Route::post('/store', 'FAQController@store');
        Route::post('/{id}/update', 'FAQController@update');
        Route::get('/{id}/delete', 'FAQController@destroy');
    });

    Route::group(['prefix' => 'webinar-quiz'], function () {
        Route::post('/store', 'WebinarQuizController@store');
        Route::post('/{id}/update', 'WebinarQuizController@update');
        Route::get('/{id}/delete', 'WebinarQuizController@destroy');
    });


    Route::group(['prefix' => 'certificates'], function () {
        Route::get('/', 'CertificateController@lists');
        Route::get('/achievements', 'CertificateController@achievements');
    });

    Route::group(['prefix' => 'meetings'], function () {
        Route::get('/reservation', 'ReserveMeetingController@reservation');
        Route::get('/requests', 'ReserveMeetingController@requests');

        Route::get('/settings', 'MeetingController@setting')->name('meeting_setting');
        Route::post('/{id}/update', 'MeetingController@update');
        Route::post('saveTime', 'MeetingController@saveTime');
        Route::post('deleteTime', 'MeetingController@deleteTime');
        Route::post('temporaryDisableMeetings', 'MeetingController@temporaryDisableMeetings');

        Route::get('/{id}/join', 'ReserveMeetingController@join');
        Route::post('/create-link', 'ReserveMeetingController@createLink');
        Route::get('/{id}/finish', 'ReserveMeetingController@finish');

        //new routes for organization & manager roles
        Route::get('/newMeeting', 'MeetingController@newMeeting');
        Route::get('/getOrganizationMeetings', 'MeetingController@getOrganizationMeetings')->name('PendingOrganizationMeetings');
    });

    Route::group(['prefix' => 'financial'], function () {
        Route::get('/sales', 'SaleController@index');
        Route::get('/summary', 'AccountingController@index');
        Route::get('/payout', 'PayoutController@index');
        Route::post('/request-payout', 'PayoutController@requestPayout');
        Route::get('/account', 'AccountingController@account');
        Route::post('/charge', 'AccountingController@charge');
        Route::get('/organizationSummary', 'SaleController@organizationSummary');

        Route::group(['prefix' => 'offline-payments'], function () {
            Route::get('/{id}/edit', 'AccountingController@account');
            Route::post('/{id}/update', 'AccountingController@updateOfflinePayment');
            Route::get('/{id}/delete', 'AccountingController@deleteOfflinePayment');
        });

        Route::get('/subscribes', 'SubscribesController@index');
        Route::post('/pay-subscribes', 'SubscribesController@pay');
    });

    Route::group(['prefix' => 'setting'], function () {
        Route::get('/step/{step?}', 'UserController@setting')->name('get.panel.user_setting');
        Route::get('/', 'UserController@setting')->name('get.panel_user_setting_main');
        Route::post('/', 'UserController@update')->name('post.panel_user_save_setting');
        Route::post('/metas', 'UserController@storeMetas');
        Route::post('metas/{meta_id}/update', 'UserController@updateMeta');
        Route::get('metas/{meta_id}/delete', 'UserController@deleteMeta');
        Route::middleware(['impersonate','share'])->get("/delete/document/{docId}", "UserController@deleteDocument")->name("delete.docs");
        Route::post("/usi-doc/save", "UserController@saveUsiDocuments")->name("save-usiDoc");
    });

    Route::group(['prefix' => 'support'], function () {
        Route::get('/', 'SupportsController@index');
        Route::get('/new', 'SupportsController@create');
        Route::post('/store', 'SupportsController@store');
        Route::get('{id}/conversations', 'SupportsController@index');
        Route::post('{id}/conversations', 'SupportsController@storeConversations');
        Route::get('{id}/close', 'SupportsController@close');

        Route::group(['prefix' => 'tickets'], function () {
            Route::get('/', 'SupportsController@tickets');
            Route::get('{id}/conversations', 'SupportsController@tickets');
        });
    });

    Route::group(['prefix' => 'marketing', 'middleware' => 'user.not.access'], function () {
        Route::get('/special_offers', 'SpecialOfferController@index')->name('special_offer_index');
        Route::post('/special_offers/store', 'SpecialOfferController@store');
        Route::get('/special_offers/{id}/disable', 'SpecialOfferController@disable');
        Route::get('/promotions', 'MarketingController@promotions');
        Route::post('/pay-promotion', 'MarketingController@payPromotion');
    });

    Route::group(['prefix' => 'marketing'], function () {
        Route::get('/affiliates', 'AffiliateController@affiliates');
    });

    Route::group(['prefix' => 'noticeboard'], function () {
        Route::get('/', 'NoticeboardController@index');
        Route::get('/new', 'NoticeboardController@create');
        Route::post('/store', 'NoticeboardController@store');
        Route::get('/{noticeboard_id}/edit', 'NoticeboardController@edit');
        Route::post('/{noticeboard_id}/update', 'NoticeboardController@update');
        Route::get('/{noticeboard_id}/delete', 'NoticeboardController@delete');
        Route::get('/{noticeboard_id}/saveStatus', 'NoticeboardController@saveStatus');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationsController@index');
        Route::get('/{id}/saveStatus', 'NotificationsController@saveStatus');
        Route::get('/students/settings', 'NotificationsController@studentNotificationSettings');
        Route::post('/students/settings/update', 'NotificationsController@studentNotificationUpdate');
    });

    // organization instructor and students route
    Route::group(['prefix' => 'manage'], function () {
        Route::get('/{user_type}', 'UserController@manageUsers')->name('panel.manage.list.users');
        Route::get('/students/behindSchedule', 'UserController@manageUsersBehindSchedule')->name('panel.students.behindSchedule');
        Route::get('/{user_type}/new', 'UserController@createUser')->name('panel.manage.create.user');
        Route::post('/{user_type}/new', 'UserController@storeUser');
        Route::get('/{user_type}/{user_id}/edit', 'UserController@editUser');
        Route::get('/{user_type}/{user_id}/edit/step/{step?}', 'UserController@editUser');
        Route::get('/{user_type}/{user_id}/delete', 'UserController@deleteUser');
        Route::get('/students/{id}/enrol', 'UserController@enrolStudents');
        Route::get('/students/course/{slug}/{payment_type}/{id}', 'WebinarController@processEnrolment');
        /* Route::get('/students/course/{slug}/free/{id}', 'WebinarController@free');
        Route::get('/students/course/{slug}/paid/{id}', 'WebinarController@paid'); */
        Route::get('/students/break/create/{user_id}', 'UserController@createBreakRequest')->name('panel.break.create');
        Route::post('/students/break/save', 'UserController@saveBreakRequest')->name('panel.break.save');
        // following route will be used to view the enrolled courses list with progress of each course
        Route::get('/{user_type}/{user_id}/courses', 'UserController@getEnrolledCourses');
    });

    Route::group(['prefix' => 'organizationSites'], function() {
        Route::get('/', 'OrganizationSiteController@index')->name('panel.manage.organizationSites');
        Route::get('/new', 'OrganizationSiteController@create')->name('panel.get.new.organizationSite');
        Route::post('/new', 'OrganizationSiteController@store')->name('panel.post.new.organizationSite');
        Route::get('/edit/{id}', 'OrganizationSiteController@edit')->name('panel.get.edit.organizationSite');
        Route::post('/update/{id}', 'OrganizationSiteController@update')->name('panel.post.update.organizationSite');
        Route::get('/delete/{id}', 'OrganizationSiteController@destroy')->name('panel.delete.organizationSite');
    });

    // panel.get.organization.site.managers
    Route::group(['prefix' => 'fetch'], function() {
        Route::post('/getSiteManagers', 'UserController@fetchSiteManagers')->name('panel.get.organization.site.managers');
    });

    Route::group(['prefix' => 'breakRequest'], function() {
        Route::get('/', 'UserController@createBreakRequest')->name('panel.create.break.request');
    });


    /* Route::group(['prefix' => 'organizationManagers'], function() {
        Route::get('/', 'UserController@manageManagers')->name('panel.manage.organizationmanagers');
        Route::get('/new', 'OrganizationSiteController@create')->name('panel.get.new.organizationmanager');
        Route::post('/new', 'OrganizationSiteController@store')->name('panel.post.new.organizationmanager');
        Route::get('/edit/{id}', 'OrganizationSiteController@create')->name('panel.get.edit.organizationmanager');
        Route::post('/update', 'OrganizationSiteController@store')->name('panel.post.update.organizationmanager');
        Route::get('/delete/{id}', 'OrganizationSiteController@create')->name('panel.delete.organizationmanager');
    }); */

    Route::group(['prefix' => 'export'], function() {
        Route::get('/students', 'UserController@exportStudents')->name('export.students');
    });


});
Route::get('text-lessons-for-chapter/{chapterId}','Panel\ChapterController@getTextLesson')->name('chapter.getTextLesson');
Route::get('chapters-for-webinar/{webinarId}','Panel\WebinarController@getChapter')->name('webinar.getChapters');

// following route is written here to exclude this route from the panel middleware
Route::group(['namespace' => 'Panel', 'prefix' => 'panel', 'middleware' => ['impersonate','share']], function () {
    Route::group(['prefix' => 'setting'], function () {
        Route::get("/delete/document/{docId}", "UserController@deleteDocument")->name("delete.docs");
    });
});
