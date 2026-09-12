<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\EnsureIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/', IndexController::class)->name('home');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Admin panel
Route::middleware(['auth', 'verified', EnsureIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
        Route::put('/projects/{project}', [Admin\ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [Admin\ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::delete('/projects/{project}/image', [Admin\ProjectController::class, 'deleteImage'])->name('projects.image.destroy');
        Route::post('/projects/{project}/gallery/delete', [Admin\ProjectController::class, 'deleteGalleryImage'])->name('projects.gallery.destroy');

        Route::get('/blogs', [Admin\BlogController::class, 'index'])->name('blogs.index');
        Route::post('/blogs', [Admin\BlogController::class, 'store'])->name('blogs.store');
        Route::put('/blogs/{blog}', [Admin\BlogController::class, 'update'])->name('blogs.update');
        Route::delete('/blogs/{blog}', [Admin\BlogController::class, 'destroy'])->name('blogs.destroy');
        Route::delete('/blogs/{blog}/image', [Admin\BlogController::class, 'deleteImage'])->name('blogs.image.destroy');

        Route::get('/certifications', [Admin\CertificationController::class, 'index'])->name('certifications.index');
        Route::post('/certifications', [Admin\CertificationController::class, 'store'])->name('certifications.store');
        Route::put('/certifications/{certification}', [Admin\CertificationController::class, 'update'])->name('certifications.update');
        Route::delete('/certifications/{certification}', [Admin\CertificationController::class, 'destroy'])->name('certifications.destroy');
        Route::post('/certifications/{certification}/images/delete', [Admin\CertificationController::class, 'deleteImage'])->name('certifications.image.destroy');
        Route::delete('/certifications/{certification}/pdf', [Admin\CertificationController::class, 'deletePdf'])->name('certifications.pdf.destroy');

        Route::get('/experiences', [Admin\ExperienceController::class, 'index'])->name('experiences.index');
        Route::post('/experiences', [Admin\ExperienceController::class, 'store'])->name('experiences.store');
        Route::put('/experiences/{experience}', [Admin\ExperienceController::class, 'update'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [Admin\ExperienceController::class, 'destroy'])->name('experiences.destroy');

        Route::get('/contacts', [Admin\ContactController::class, 'index'])->name('contacts.index');
        Route::patch('/contacts/{contact}/read', [Admin\ContactController::class, 'markAsRead'])->name('contacts.read');
        Route::delete('/contacts/{contact}', [Admin\ContactController::class, 'destroy'])->name('contacts.destroy');

        Route::get('/analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('/settings', [Admin\SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SiteSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/seo-image', [Admin\SiteSettingController::class, 'uploadSeoImage'])->name('settings.seo-image.upload');
        Route::delete('/settings/seo-image', [Admin\SiteSettingController::class, 'deleteSeoImage'])->name('settings.seo-image.delete');

        Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [Admin\ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
    });

require __DIR__.'/settings.php';
