<?php

use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\PreferenceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TrainerController as AdminTrainerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LocaleController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PlayerController;
use App\Http\Controllers\Public\TeamController;
use App\Http\Controllers\Public\TrainerController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('dashboard', fn () => redirect()->route('admin.dashboard'));

        Route::patch('preferences/theme', [PreferenceController::class, 'theme'])->name('preferences.theme');

        Route::post('media/video', [MediaUploadController::class, 'video'])
            ->middleware('throttle:30,1')
            ->name('media.video');

        Route::post('players/reorder', [AdminPlayerController::class, 'reorder'])->name('players.reorder');
        Route::get('players', [AdminPlayerController::class, 'index'])->name('players.index');
        Route::get('players/create', [AdminPlayerController::class, 'create'])->name('players.create');
        Route::post('players', [AdminPlayerController::class, 'store'])->name('players.store');
        Route::get('players/{player:id}/edit', [AdminPlayerController::class, 'edit'])->name('players.edit');
        Route::match(['put', 'patch'], 'players/{player:id}', [AdminPlayerController::class, 'update'])->name('players.update');
        Route::delete('players/{player:id}', [AdminPlayerController::class, 'destroy'])->name('players.destroy');

        Route::post('trainers/reorder', [AdminTrainerController::class, 'reorder'])->name('trainers.reorder');
        Route::get('trainers', [AdminTrainerController::class, 'index'])->name('trainers.index');
        Route::get('trainers/create', [AdminTrainerController::class, 'create'])->name('trainers.create');
        Route::post('trainers', [AdminTrainerController::class, 'store'])->name('trainers.store');
        Route::get('trainers/{trainer:id}/edit', [AdminTrainerController::class, 'edit'])->name('trainers.edit');
        Route::match(['put', 'patch'], 'trainers/{trainer:id}', [AdminTrainerController::class, 'update'])->name('trainers.update');
        Route::delete('trainers/{trainer:id}', [AdminTrainerController::class, 'destroy'])->name('trainers.destroy');

        Route::get('team', fn () => redirect()->route('admin.team.members.index'))->name('team');
        Route::post('team/members/reorder', [TeamMemberController::class, 'reorder'])->name('team.members.reorder');
        Route::get('team/members', [TeamMemberController::class, 'index'])->name('team.members.index');
        Route::get('team/members/create', [TeamMemberController::class, 'create'])->name('team.members.create');
        Route::post('team/members', [TeamMemberController::class, 'store'])->name('team.members.store');
        Route::get('team/members/{member:id}/edit', [TeamMemberController::class, 'edit'])->name('team.members.edit');
        Route::match(['put', 'patch'], 'team/members/{member:id}', [TeamMemberController::class, 'update'])->name('team.members.update');
        Route::delete('team/members/{member:id}', [TeamMemberController::class, 'destroy'])->name('team.members.destroy');

        Route::get('news/categories', [NewsCategoryController::class, 'index'])->name('news.categories.index');
        Route::post('news/categories', [NewsCategoryController::class, 'store'])->name('news.categories.store');
        Route::put('news/categories/{category:id}', [NewsCategoryController::class, 'update'])->name('news.categories.update');
        Route::delete('news/categories/{category:id}', [NewsCategoryController::class, 'destroy'])->name('news.categories.destroy');

        Route::patch('news/{news:id}/featured', [AdminNewsController::class, 'toggleFeatured'])->name('news.featured');
        Route::get('news', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('news/{news:id}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::match(['put', 'patch'], 'news/{news:id}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('news/{news:id}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::patch('messages/{message}/read', [ContactMessageController::class, 'markRead'])->name('messages.read');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::middleware('admin:admin')->group(function () {
            Route::post('settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
            Route::put('settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
            Route::delete('settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
        });
    });
});

Route::get('/', [LocaleController::class, 'redirectToLocalizedHome'])->name('home');
Route::get('lang/{locale}', [LocaleController::class, 'remember'])->name('locale.remember');

Route::prefix('{locale}')->name('public.')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('players/{player}', [PlayerController::class, 'show'])->name('players.show');

    Route::get('trainers', [TrainerController::class, 'index'])->name('trainers.index');
    Route::get('trainers/{trainer}', [TrainerController::class, 'show'])->name('trainers.show');

    Route::get('agency-team', TeamController::class)->name('team');

    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{article}', [NewsController::class, 'show'])->name('news.show');

    Route::get('about', AboutController::class)->name('about');

    Route::get('contacts', [ContactController::class, 'index'])->name('contacts');
    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
});
