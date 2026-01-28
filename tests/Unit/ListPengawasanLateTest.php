<?php

use App\Support\ListPengawasanLate;
use Carbon\Carbon;

test('isLate true jika status bukan selesai dan deadline sudah lewat', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-27 10:00:00'));
    expect(ListPengawasanLate::isLate('Sedang Berjalan', '2026-01-26', 'Selesai'))->toBeTrue();
});

test('isLate false jika deadline hari ini', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-27 10:00:00'));
    expect(ListPengawasanLate::isLate('Sedang Berjalan', '2026-01-27', 'Selesai'))->toBeFalse();
});

test('isLate false jika status sudah selesai', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-27 10:00:00'));
    expect(ListPengawasanLate::isLate('Selesai', '2026-01-01', 'Selesai'))->toBeFalse();
});

test('isLate false jika deadline kosong', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-27 10:00:00'));
    expect(ListPengawasanLate::isLate('Sedang Berjalan', null, 'Selesai'))->toBeFalse();
});

