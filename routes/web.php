<?php

use App\Http\Controllers\BkMkController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseLearningOutcomeController;
use App\Http\Controllers\CplBkController;
use App\Http\Controllers\CplBkMkReportController;
use App\Http\Controllers\CplCpmkController;
use App\Http\Controllers\CplCpmkMkReportController;
use App\Http\Controllers\CplMkController;
use App\Http\Controllers\CplPlController;
use App\Http\Controllers\GraduateProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramLearningOutcomeController;
use App\Http\Controllers\ProgramStudyController;
use App\Http\Controllers\StudyMaterialController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('testing', fn() => inertia('Testing'));

Route::get('testing', function () {
    return Inertia::render('Testing');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::controller(ProgramStudyController::class)->group(function(){
    Route::get('program-studies', 'index')->name('program-studies.index');
    Route::get('program-studies/create', 'create')->name('program-studies.create');
    Route::post('program-studies', 'store')->name('program-studies.store');
    Route::get('program-studies/{programStudy}/edit', 'edit')->name('program-studies.edit');
    Route::put('program-studies/{programStudy}', 'update')->name('program-studies.update');
    Route::delete('program-studies/{programStudy}', 'destroy')->name('program-studies.destroy');
});

Route::controller(UserController::class)->group(function(){
    Route::get('users', 'index')->name('users.index');
    Route::get('users/create', 'create')->name('users.create');
    Route::post('users', 'store')->name('users.store');
    Route::get('users/{user}/edit', 'edit')->name('users.edit');
    Route::put('users/{user}', 'update')->name('users.update');
    Route::delete('users/{user}', 'destroy')->name('users.destroy');
});

Route::controller(GraduateProfileController::class)->group(function(){
    Route::get('graduate-profiles', 'index')->name('graduate-profiles.index');
    Route::get('graduate-profiles/create', 'create')->name('graduate-profiles.create');
    Route::post('graduate-profiles', 'store')->name('graduate-profiles.store');
    Route::get('graduate-profiles/{graduateProfile}/edit', 'edit')->name('graduate-profiles.edit');
    Route::put('graduate-profiles/{graduateProfile}', 'update')->name('graduate-profiles.update');
    Route::delete('graduate-profiles/{graduateProfile}', 'destroy')->name('graduate-profiles.destroy');
});

Route::controller(ProgramLearningOutcomeController::class)->group(function(){
    Route::get('program-learning-outcomes', 'index')->name('program-learning-outcomes.index');
    Route::get('program-learning-outcomes/create', 'create')->name('program-learning-outcomes.create');
    Route::post('program-learning-outcomes', 'store')->name('program-learning-outcomes.store');
    Route::get('program-learning-outcomes/{programLearningOutcome}/edit', 'edit')->name('program-learning-outcomes.edit');
    Route::put('program-learning-outcomes/{programLearningOutcome}', 'update')->name('program-learning-outcomes.update');
    Route::delete('program-learning-outcomes/{programLearningOutcome}', 'destroy')->name('program-learning-outcomes.destroy');
});

Route::controller(StudyMaterialController::class)->group(function(){
    Route::get('study-materials', 'index')->name('study-materials.index');
    Route::get('study-materials/create', 'create')->name('study-materials.create');
    Route::post('study-materials', 'store')->name('study-materials.store');
    Route::get('study-materials/{studyMaterial}/edit', 'edit')->name('study-materials.edit');
    Route::put('study-materials/{studyMaterial}', 'update')->name('study-materials.update');
    Route::delete('study-materials/{studyMaterial}', 'destroy')->name('study-materials.destroy');
});

Route::controller(CourseController::class)->group(function(){
    Route::get('courses', 'index')->name('courses.index');
    Route::get('courses/create', 'create')->name('courses.create');
    Route::post('courses', 'store')->name('courses.store');
    Route::get('courses/{course}/edit', 'edit')->name('courses.edit');
    Route::put('courses/{course}', 'update')->name('courses.update');
    Route::delete('courses/{course}', 'destroy')->name('courses.destroy');
});

Route::controller(CplPlController::class)->group(function(){
    Route::get('cpl-profiles', 'index')->name('cpl-profiles.index');
    Route::get('cpl-profiles/create', 'create')->name('cpl-profiles.create');
    Route::post('cpl-profiles', 'store')->name('cpl-profiles.store');
    Route::get('cpl-profiles/{cpl}/edit', 'edit')->name('cpl-profiles.edit');
    Route::put('cpl-profiles/{cpl}', 'update')->name('cpl-profiles.update');
    Route::delete('cpl-profiles/{cpl}', 'destroy')->name('cpl-profiles.destroy');
});

Route::controller(CplBkController::class)->group(function(){
    Route::get('cpl-bk', 'index')->name('cpl-bk.index');
    Route::get('cpl-bk/create', 'create')->name('cpl-bk.create');
    Route::post('cpl-bk', 'store')->name('cpl-bk.store');
    Route::get('cpl-bk/{cpl}/edit', 'edit')->name('cpl-bk.edit');
    Route::put('cpl-bk/{cpl}', 'update')->name('cpl-bk.update');
    Route::delete('cpl-bk/{cpl}', 'destroy')->name('cpl-bk.destroy');
});

Route::controller(BkMkController::class)->group(function(){
    Route::get('bk-mk', 'index')->name('bk-mk.index');
    Route::get('bk-mk/create', 'create')->name('bk-mk.create');
    Route::post('bk-mk', 'store')->name('bk-mk.store');
    Route::get('bk-mk/{bk}/edit', 'edit')->name('bk-mk.edit');
    Route::put('bk-mk/{bk}', 'update')->name('bk-mk.update');
    Route::delete('bk-mk/{bk}', 'destroy')->name('bk-mk.destroy');
});

Route::controller(CplMkController::class)->group(function(){
    Route::get('cpl-mk', 'index')->name('cpl-mk.index');
    Route::get('cpl-mk/create', 'create')->name('cpl-mk.create');
    Route::post('cpl-mk', 'store')->name('cpl-mk.store');
    Route::get('cpl-mk/{mk}/edit', 'edit')->name('cpl-mk.edit');
    Route::put('cpl-mk/{mk}', 'update')->name('cpl-mk.update');
    Route::delete('cpl-mk/{mk}', 'destroy')->name('cpl-mk.destroy');
});

Route::controller(CourseLearningOutcomeController::class)->group(function(){
    Route::get('course-learning-outcomes', 'index')->name('course-learning-outcomes.index');
    Route::get('course-learning-outcomes/create', 'create')->name('course-learning-outcomes.create');
    Route::post('course-learning-outcomes', 'store')->name('course-learning-outcomes.store');
    Route::get('course-learning-outcomes/{cpmk}/edit', 'edit')->name('course-learning-outcomes.edit');
    Route::put('course-learning-outcomes/{cpmk}', 'update')->name('course-learning-outcomes.update');
    Route::delete('course-learning-outcomes/{cpmk}', 'destroy')->name('course-learning-outcomes.destroy');
});

Route::controller(CplCpmkController::class)->group(function(){
    Route::get('cpl-cpmk', 'index')->name('cpl-cpmk.index');
    Route::get('cpl-cpmk/create', 'create')->name('cpl-cpmk.create');
    Route::post('cpl-cpmk', 'store')->name('cpl-cpmk.store');
    Route::get('cpl-cpmk/{cpl}/edit', 'edit')->name('cpl-cpmk.edit');
    Route::put('cpl-cpmk/{cpl}', 'update')->name('cpl-cpmk.update');
    Route::delete('cpl-cpmk/{cpl}', 'destroy')->name('cpl-cpmk.destroy');
});

Route::get('/reports/cpl-bk-mk', [CplBkMkReportController::class, 'cplBkMk'])->name('reports.cpl-bk-mk');
Route::get('/reports/cpl-cpmk-mk', [CplCpmkMkReportController::class, 'cplCpmkMk'])->name('reports.cpl-cpmk-mk');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
