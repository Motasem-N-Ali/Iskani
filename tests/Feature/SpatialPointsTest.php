<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Guards DECISIONS.md D3: points are SRID 4326 and always built as (longitude latitude)
| with 'axis-order=long-lat'. Coordinates are real Google Maps values (latitude, longitude).
| A swapped order would place a listing in the wrong country without any error.
*/

const RAMALLAH_LAT = 31.904659245339186;
const RAMALLAH_LNG = 35.2028695836448;
const BIRZEIT_LAT = 31.975687366394137;
const BIRZEIT_LNG = 35.19659747097564;

beforeEach(function () {
    Schema::dropIfExists('spatial_probe');

    // Same column type and index the real housings table will use.
    Schema::create('spatial_probe', function (Blueprint $table) {
        $table->id();
        $table->geometry('location', 'point', 4326)->spatialIndex();
    });
});

afterEach(function () {
    Schema::dropIfExists('spatial_probe');
});

it('keeps a Ramallah point in Ramallah when long-lat is stated explicitly', function () {
    DB::insert(
        "INSERT INTO spatial_probe (location) VALUES (ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
        ['POINT('.RAMALLAH_LNG.' '.RAMALLAH_LAT.')'],
    );

    $row = DB::selectOne(
        'SELECT ST_Latitude(location) AS lat, ST_Longitude(location) AS lng, ST_SRID(location) AS srid FROM spatial_probe'
    );

    expect((int) $row->srid)->toBe(4326)
        ->and((float) $row->lat)->toEqualWithDelta(RAMALLAH_LAT, 0.000001)
        ->and((float) $row->lng)->toEqualWithDelta(RAMALLAH_LNG, 0.000001);
});

it('writes GeoJSON as [longitude, latitude]', function () {
    DB::insert(
        "INSERT INTO spatial_probe (location) VALUES (ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
        ['POINT('.RAMALLAH_LNG.' '.RAMALLAH_LAT.')'],
    );

    $json = json_decode(DB::selectOne('SELECT ST_AsGeoJSON(location) AS g FROM spatial_probe')->g, true);

    expect($json['coordinates'][0])->toEqualWithDelta(RAMALLAH_LNG, 0.000001)
        ->and($json['coordinates'][1])->toEqualWithDelta(RAMALLAH_LAT, 0.000001);
});

it('measures about 7.9 km from Ramallah to Birzeit', function () {
    $metres = DB::selectOne(
        "SELECT ST_Distance(
            ST_GeomFromText(?, 4326, 'axis-order=long-lat'),
            ST_GeomFromText(?, 4326, 'axis-order=long-lat')
        ) AS metres",
        ['POINT('.RAMALLAH_LNG.' '.RAMALLAH_LAT.')', 'POINT('.BIRZEIT_LNG.' '.BIRZEIT_LAT.')'],
    )->metres;

    expect((float) $metres)->toBeGreaterThan(7800)->toBeLessThan(8000);
});

it('shows the trap: without the option, MySQL reads latitude first and Ramallah moves hundreds of km away', function () {
    $km = DB::selectOne(
        "SELECT ST_Distance(
            ST_GeomFromText(?, 4326),
            ST_GeomFromText(?, 4326, 'axis-order=long-lat')
        ) / 1000 AS km",
        ['POINT('.RAMALLAH_LNG.' '.RAMALLAH_LAT.')', 'POINT('.RAMALLAH_LNG.' '.RAMALLAH_LAT.')'],
    )->km;

    expect((float) $km)->toBeGreaterThan(400);
});
