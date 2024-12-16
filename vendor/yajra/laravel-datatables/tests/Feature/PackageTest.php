<?php

test('it loads all the packages', function () {
    expect(class_exists(\Yajra\DataTables\DataTablesServiceProvider::class))->toBeTrue();
    expect(class_exists(\Yajra\DataTables\HtmlServiceProvider::class))->toBeTrue();
    expect(class_exists(\Yajra\DataTables\FractalServiceProvider::class))->toBeTrue();
    expect(class_exists(\Yajra\DataTables\ButtonsServiceProvider::class))->toBeTrue();
    expect(class_exists(\Yajra\DataTables\ExportServiceProvider::class))->toBeTrue();
});
