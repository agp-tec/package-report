<?php

Route::group(['as' => 'web.', 'namespace' => 'Agp\Report\Controller\Web', 'middleware' => ['web']], function () {
    Route::resource('cidade', 'CidadeController');
});

Route::get('home', function () {
    return view('Report::cidade/index');
});

?>
