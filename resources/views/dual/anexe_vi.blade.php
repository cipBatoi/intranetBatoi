@extends('layouts.pdf')
@section('css')
    {{ Html::style('/css/dual.css') }}
@endsection
@section('content')
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_001.jpg','top'=>-50])
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_002.jpg','top'=>1650])
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_003.jpg','top'=>3350])
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_004.jpg','top'=>5050])
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_005.jpg','top'=>6750])
        @include('dual.partials.anexe_vi',['imagen'=>'img/pdf/dual/anexe_vi_006.jpg','top'=>8450])
@endsection