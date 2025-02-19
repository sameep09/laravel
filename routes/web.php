<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    //user management route section
    Route::group(['prefix' => 'user-management'], function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('user.index');
        Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('user.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/reset_password/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'reset_password'])->name('user.reset_password');
            Route::put('/reset_password/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'update_password'])->name('user.update_password');

            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'edit'])->name('user.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'update'])->name('user.update');
        });
        Route::delete('/', [App\Http\Controllers\UserController::class, 'delete'])->name('user.delete');

        Route::get('/change_password/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'change_password'])->name('user.change_password');
        Route::put('/change_password/{id}/{hashtag}', [App\Http\Controllers\UserController::class, 'updatemy_password'])->name('user.updatemy_password');
    });
    //user management route section ends

    //ministry route section
    Route::group(['prefix' => 'ministry'], function () {
        Route::get('/', [App\Http\Controllers\MinistryController::class, 'index'])->name('ministry.index');
        Route::post('/', [App\Http\Controllers\MinistryController::class, 'store'])->name('ministry.store');

        Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\MinistryController::class, 'edit'])->name('ministry.edit')->middleware('edit');
        Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\MinistryController::class, 'update'])->name('ministry.update')->middleware('edit');

        Route::delete('/', [App\Http\Controllers\MinistryController::class, 'delete'])->name('ministry.delete');
    });
    //ministry route section ends

    //country route section
    Route::group(['prefix' => 'country'], function () {
        Route::get('/', [App\Http\Controllers\CountryController::class, 'index'])->name('country.index');
        Route::post('/', [App\Http\Controllers\CountryController::class, 'store'])->name('country.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\CountryController::class, 'edit'])->name('country.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\CountryController::class, 'update'])->name('country.update');
        });
        Route::delete('/', [App\Http\Controllers\CountryController::class, 'delete'])->name('country.delete');
    });
    //country route section ends

    //ProgramType route section
    Route::group(['prefix' => 'pType'], function () {
        Route::get('/', [App\Http\Controllers\ProgramTypeController::class, 'index'])->name('pType.index');
        Route::post('/', [App\Http\Controllers\ProgramTypeController::class, 'store'])->name('pType.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\ProgramTypeController::class, 'edit'])->name('pType.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\ProgramTypeController::class, 'update'])->name('pType.update');
        });
        Route::delete('/', [App\Http\Controllers\ProgramTypeController::class, 'delete'])->name('pType.delete');
    });
    //ProgramType route section ends

    //meeting route section
    Route::group(['prefix' => 'meeting'], function () {
        Route::get('/', [App\Http\Controllers\MeetingController::class, 'index'])->name('meeting.index');
        Route::post('/', [App\Http\Controllers\MeetingController::class, 'store'])->name('meeting.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\MeetingController::class, 'edit'])->name('meeting.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\MeetingController::class, 'update'])->name('meeting.update');
        });
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\MeetingController::class, 'delete'])->name('meeting.delete');
        Route::get('/meeting-report/{id}/{hashtag}', [App\Http\Controllers\MeetingController::class, 'meeting_report'])->name('meeting.meeting_report');

        Route::put('/update-status', [App\Http\Controllers\MeetingController::class, 'update_status'])->name('meeting.updateStatus');

        Route::put('/report/{id}/{hashtag}', [App\Http\Controllers\MeetingController::class, 'store_report'])->name('meeting.store_report');
    });
    //meeting route section ends

    //meeting_attendee route section
    Route::group(['prefix' => 'meeting_attendee'], function () {
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\MeetingAttendeeController::class, 'index'])->name('meeting_attendee.index');

        Route::post('/', [App\Http\Controllers\MeetingAttendeeController::class, 'store'])->name('meeting_attendee.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\MeetingAttendeeController::class, 'edit'])->name('meeting_attendee.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\MeetingAttendeeController::class, 'update'])->name('meeting_attendee.update');
        });
        Route::delete('/', [App\Http\Controllers\MeetingAttendeeController::class, 'delete'])->name('meeting_attendee.delete');

        Route::get('/pull/{id}/{hashtag}', [App\Http\Controllers\MeetingAttendeeController::class, 'pull'])->name('meeting_attendee.pull');
    });
    //meeting_attendee route section ends

    //meeting_agenda route section
    Route::group(['prefix' => 'meeting_agenda'], function () {
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\MeetingAgendaController::class, 'agenda_index'])->name('meeting_agenda.agenda_index');

        Route::post('/agenda', [App\Http\Controllers\MeetingAgendaController::class, 'store_agenda'])->name('meeting_agenda.store_agenda');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit_agenda/{id}/{hashtag}', [App\Http\Controllers\MeetingAgendaController::class, 'edit_agenda'])->name('meeting_agenda.edit_agenda');
            Route::put('/edit_agenda/{id}/{hashtag}', [App\Http\Controllers\MeetingAgendaController::class, 'update_agenda'])->name('meeting_agenda.update_agenda');
        });
        Route::delete('/agenda', [App\Http\Controllers\MeetingAgendaController::class, 'delete_agenda'])->name('meeting_agenda.delete_agenda');
    });
    //meeting_agenda route section ends

    //agenda_ministries route section
    Route::group(['prefix' => 'agenda_ministries'], function () {
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'index'])->name('agenda_ministries.index');
        Route::post('/', [App\Http\Controllers\AgendaMinistryController::class, 'store'])->name('agenda_ministries.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'edit'])->name('agenda_ministries.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'update'])->name('agenda_ministries.update');
        });
        Route::delete('/', [App\Http\Controllers\AgendaMinistryController::class, 'delete'])->name('agenda_ministries.delete');

        Route::get('/form_two/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'form_two'])->name('agenda_ministries.form_two');
        Route::get('/form_three/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'form_three'])->name('agenda_ministries.form_three');

        Route::put('/report_two/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'store_report_two'])->name('meeting_agenda.store_report_two');
        Route::put('/report_three/{id}/{hashtag}', [App\Http\Controllers\AgendaMinistryController::class, 'store_report_three'])->name('meeting_agenda.store_report_three');
    });
    //agenda_ministries route section ends

    //nomination_meeting route section
    Route::group(['prefix' => 'nomination_meeting'], function () {
        Route::get('/', [App\Http\Controllers\NominationMeetingController::class, 'index'])->name('nomination_meeting.index');
        Route::post('/', [App\Http\Controllers\NominationMeetingController::class, 'store'])->name('nomination_meeting.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'edit'])->name('nomination_meeting.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'update'])->name('nomination_meeting.update');
        });

        Route::delete('/', [App\Http\Controllers\NominationMeetingController::class, 'delete'])->name('nomination_meeting.delete');
        Route::get('/agenda/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'agenda'])->name('nomination_meeting.agenda');
        Route::put('/agenda/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'storeAgenda'])->name('nomination_meeting.storeAgenda');

        Route::put('/update-status', [App\Http\Controllers\NominationMeetingController::class, 'update_status'])->name('nomination_meeting.updateStatus');
        Route::get('/report-all/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'report_all'])->name('nomination_meeting.report_all');
        Route::get('/report-six/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'report_six'])->name('nomination_meeting.report_six');
        Route::get('/report-six-all/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'report_six_all'])->name('nomination_meeting.report_six_all');

        Route::put('/nomination-report/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'store_nomination_report'])->name('nomination_meeting.store_nomination_report');
        Route::put('/nirnaya-report/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'store_nirnaya_report'])->name('nomination_meeting.store_nirnaya_report');
        Route::put('/report/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'store_report'])->name('nomination_meeting.store_report');
        Route::put('/nom_report/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingController::class, 'store_nom_meeting_report'])->name('nomination_meeting.store_nom_meeting_report');
    });
    //nomination_meeting route section ends

    //nomination_meeting_nirnaya route section
    Route::group(['prefix' => 'nomination_meeting_nirnaya'], function () {
        Route::get('/', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'index'])->name('nomination_meeting_nirnaya.index');
        Route::get('/agenda/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'agenda'])->name('nomination_meeting_nirnaya.agenda');

        Route::post('/', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'store'])->name('nomination_meeting_nirnaya.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'edit'])->name('nomination_meeting_nirnaya.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'update'])->name('nomination_meeting_nirnaya.update');
        });
        Route::delete('/', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'delete'])->name('nomination_meeting_nirnaya.delete');

        Route::put('/agenda/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'storeAgenda'])->name('nomination_meeting_nirnaya.storeAgenda');

        Route::put('/update-status', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'update_status'])->name('nomination_meeting_nirnaya.updateStatus');
        Route::get('/report-all/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'report_all'])->name('nomination_meeting_nirnaya.report_all');
        Route::get('/report-six/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'report_six'])->name('nomination_meeting_nirnaya.report_six');
        Route::get('/report-six-all/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingNirnayaController::class, 'report_six_all'])->name('nomination_meeting_nirnaya.report_six_all');
    });
    //nomination_meeting route section ends

    //nomination_meeting_attendee route section
    Route::group(['prefix' => 'nomination_meeting_attendee'], function () {
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'index'])->name('nomination_meeting_attendee.index');
        Route::post('/', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'store'])->name('nomination_meeting_attendee.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'edit'])->name('nomination_meeting_attendee.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'update'])->name('nomination_meeting_attendee.update');
        });
        Route::delete('/', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'delete'])->name('nomination_meeting_attendee.delete');
        Route::get('/pull/{id}/{hashtag}', [App\Http\Controllers\NominationMeetingAttendeeController::class, 'pull'])->name('nomination_meeting_attendee.pull');
    });
    //nomination_meeting_attendee route section ends

    //applicant route section
    Route::group(['prefix' => 'applicant'], function () {
        Route::get('/{id}/{hashtag}', [App\Http\Controllers\ApplicantController::class, 'index'])->name('applicant.index');
        Route::post('/', [App\Http\Controllers\ApplicantController::class, 'store'])->name('applicant.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\ApplicantController::class, 'edit'])->name('applicant.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\ApplicantController::class, 'update'])->name('applicant.update');
        });
        Route::delete('/', [App\Http\Controllers\ApplicantController::class, 'delete'])->name('applicant.delete');
    });
    //applicant route section ends

    //study_leave route section
    Route::group(['prefix' => 'study_leave'], function () {
        Route::get('/', [App\Http\Controllers\StudyLeaveController::class, 'index'])->name('study_leave.index');
        Route::post('/', [App\Http\Controllers\StudyLeaveController::class, 'store'])->name('study_leave.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StudyLeaveController::class, 'edit'])->name('study_leave.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StudyLeaveController::class, 'update'])->name('study_leave.update');
        });
        Route::delete('/', [App\Http\Controllers\StudyLeaveController::class, 'delete'])->name('study_leave.delete');
    });
    //study_leave route section ends

    //form_two route section
    Route::group(['prefix' => 'form_two'], function () {
        Route::get('/', [App\Http\Controllers\FormTwoController::class, 'index'])->name('form_two.index');
    });
    //form_two route section ends

    //form_three route section
    Route::group(['prefix' => 'form_three'], function () {
        Route::get('/', [App\Http\Controllers\FormThreeController::class, 'index'])->name('form_three.index');
    });
    //form_three route section ends

    //report route section
    Route::group(['prefix' => 'report'], function () {

        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('report.index');
        Route::post('/', [App\Http\Controllers\ReportController::class, 'set_report_param'])->name('report.set-report-param');
        Route::get('/view', [App\Http\Controllers\ReportController::class, 'view_reports'])->name('report.view-report');


        Route::get('/first_report_details/{id}/{hashtag}', [App\Http\Controllers\ReportController::class, 'first_report_details'])->name('report.first_report_details');

        Route::post('/second_report', [App\Http\Controllers\ReportController::class, 'second_report'])->name('report.second_report');


        Route::get('/param', [App\Http\Controllers\ReportController::class, 'prameter'])->name('report.report_param');
        Route::post('/set-parameters', [App\Http\Controllers\ReportController::class, 'set_parameters'])->name('report.set-parameters');
        Route::get('/basic_report', [App\Http\Controllers\ReportController::class, 'basic_report'])->name('report.basic_report');

        Route::get('/leave_param', [App\Http\Controllers\ReportController::class, 'leave_prameter'])->name('report.leave_report_param');
        Route::post('/set-leave_parameters', [App\Http\Controllers\ReportController::class, 'set_leave_parameters'])->name('report.set-leave-parameters');
        Route::get('/leave_report', [App\Http\Controllers\ReportController::class, 'leave_report'])->name('report.leave_report');
    });
    //report route section ends

    //report_template route section
    Route::group(['prefix' => 'report_template'], function () {
        Route::get('/', [App\Http\Controllers\ReportTemplateController::class, 'index'])->name('report_template.index');
        Route::post('/', [App\Http\Controllers\ReportTemplateController::class, 'store'])->name('report_template.store');
        Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\ReportTemplateController::class, 'edit'])->name('report_template.edit');
        Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\ReportTemplateController::class, 'update'])->name('report_template.update');
        Route::delete('/', [App\Http\Controllers\ReportTemplateController::class, 'delete'])->name('report_template.delete');
    });
    //report_temp route section ends

    //fiscal_year route section
    Route::group(['prefix' => 'fiscal_year'], function () {
        Route::get('/', [App\Http\Controllers\FiscalYearController::class, 'index'])->name('fiscal_year.index');
        Route::post('/', [App\Http\Controllers\FiscalYearController::class, 'store'])->name('fiscal_year.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\FiscalYearController::class, 'edit'])->name('fiscal_year.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\FiscalYearController::class, 'update'])->name('fiscal_year.update');
        });
        Route::delete('/', [App\Http\Controllers\FiscalYearController::class, 'delete'])->name('fiscal_year.delete');
    });
    //fiscal_year route section ends

    //staff_post route section
    Route::group(['prefix' => 'staff_post'], function () {
        Route::get('/', [App\Http\Controllers\StaffPostController::class, 'index'])->name('staff_post.index');
        Route::post('/', [App\Http\Controllers\StaffPostController::class, 'store'])->name('staff_post.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffPostController::class, 'edit'])->name('staff_post.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffPostController::class, 'update'])->name('staff_post.update');
        });
        Route::delete('/', [App\Http\Controllers\StaffPostController::class, 'delete'])->name('staff_post.delete');
    });
    //staff_post route section ends

    //staff_service route section
    Route::group(['prefix' => 'staff_service'], function () {
        Route::get('/', [App\Http\Controllers\StaffServiceController::class, 'index'])->name('staff_service.index');
        Route::post('/', [App\Http\Controllers\StaffServiceController::class, 'store'])->name('staff_service.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffServiceController::class, 'edit'])->name('staff_service.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffServiceController::class, 'update'])->name('staff_service.update');
        });
        Route::delete('/', [App\Http\Controllers\StaffServiceController::class, 'delete'])->name('staff_service.delete');
    });
    //staff_service route section ends

    //staff_group route section
    Route::group(['prefix' => 'staff_group'], function () {
        Route::get('/', [App\Http\Controllers\StaffGroupController::class, 'index'])->name('staff_group.index');
        Route::post('/', [App\Http\Controllers\StaffGroupController::class, 'store'])->name('staff_group.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffGroupController::class, 'edit'])->name('staff_group.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffGroupController::class, 'update'])->name('staff_group.update');
        });
        Route::delete('/', [App\Http\Controllers\StaffGroupController::class, 'delete'])->name('staff_group.delete');
    });
    //staff_group route section ends

    //staff_sub_group route section
    Route::group(['prefix' => 'staff_sub_group'], function () {
        Route::get('/', [App\Http\Controllers\StaffSubGroupController::class, 'index'])->name('staff_sub_group.index');
        Route::post('/', [App\Http\Controllers\StaffSubGroupController::class, 'store'])->name('staff_sub_group.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffSubGroupController::class, 'edit'])->name('staff_sub_group.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffSubGroupController::class, 'update'])->name('staff_sub_group.update');
        });
        Route::delete('/', [App\Http\Controllers\StaffSubGroupController::class, 'delete'])->name('staff_sub_group.delete');
    });
    //staff_sub_group route section ends

    //staff_level route section
    Route::group(['prefix' => 'staff_level'], function () {
        Route::get('/', [App\Http\Controllers\StaffLevelController::class, 'index'])->name('staff_level.index');
        Route::post('/', [App\Http\Controllers\StaffLevelController::class, 'store'])->name('staff_level.store');
        Route::group(['middleware' => 'edit'], function () {
            Route::get('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffLevelController::class, 'edit'])->name('staff_level.edit');
            Route::put('/edit/{id}/{hashtag}', [App\Http\Controllers\StaffLevelController::class, 'update'])->name('staff_level.update');
        });
        Route::delete('/', [App\Http\Controllers\StaffLevelController::class, 'delete'])->name('staff_level.delete');
    });
    //staff_level route section ends

    Route::post('get-report-option-by-type', [App\Http\Controllers\AjaxCallController::class, 'get_report_option_by_type']);
});
