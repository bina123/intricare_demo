<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;
use Illuminate\Support\Facades\Route;

// Main Contacts page + AJAX fetch handled in same method
Route::get('/', [ContactController::class, 'index'])->name('contacts.index');

// CRUD routes
Route::post('/contacts/store', [ContactController::class, 'store'])->name('contacts.store');
Route::post('/contacts/update/{id}', [ContactController::class, 'update'])->name('contacts.update');
Route::delete('/contacts/delete/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
Route::get('/contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');

Route::get('/contacts/{id}/merge', [ContactController::class, 'mergeModal'])->name('contacts.mergeModal');
Route::post('/contacts/preview-merge', [ContactController::class, 'previewMerge'])->name('contacts.previewMerge');
Route::post('/contacts/perform-merge', [ContactController::class, 'performMerge'])->name('contacts.performMerge');

Route::get('/contacts/{id}/merge-logs', [ContactController::class, 'mergeLogs'])->name('contacts.mergeLogs');

// Custom fields management
Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('customfields.index');
Route::post('/custom-fields/store', [CustomFieldController::class, 'store'])->name('customfields.store');
Route::delete('/custom-fields/delete/{id}', [CustomFieldController::class, 'destroy'])->name('customfields.destroy');
