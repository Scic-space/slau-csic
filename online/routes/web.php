<?php

use App\Http\Controllers\QuestionBankExportController;
use App\Livewire\QuestionBank\Create;
use App\Livewire\QuestionBank\Edit;
use App\Livewire\QuestionBank\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/question-bank', Index::class)->name('question-bank.index');
    Route::get('/question-bank/create', Create::class)->name('question-bank.create');
    Route::get('/question-bank/{question}/edit', Edit::class)->name('question-bank.edit');
    Route::get('/question-bank/export', [QuestionBankExportController::class, 'export'])->name('question-bank.export');
});
