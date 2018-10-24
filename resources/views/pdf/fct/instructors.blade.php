@extends('layouts.pdf')
@section('content')
@php $empresa = $todos->Colaboracion->Centro; @endphp
<div class="page" style="font-size: large;line-height: 2em">
    @include('pdf.partials.cabecera')
    <br/>
    <div class="container col-lg-12" style="width:90%;">
        <p style="text-indent: 50px">{{$datosInforme['consideracion']}}
            <strong>{{$datosInforme['secretario']}}</strong> 
            @if ($datosInforme['consideracion'] == 'En') secretari @else secretària @endif del 
            {{$datosInforme['centro']}} d'{{$datosInforme['poblacion']}}, província d'{{$datosInforme['provincia']}}.</p>
    </div>
    <div class="container" >
        <strong>CERTIFICA:</strong>
        <br/>
    </div>
    <div class="container" style="width:95%">
        <p style="text-indent: 30px;text-align: justify">Que l'empresa <strong> {{$empresa->nombre}} </strong> , ubicada a {{$empresa->direccion}} de/d'
            {{$empresa->localidad}}, ha col·laborat en les pràctiques corresponents a la Formació de Centres de Treball (FCT) del {{$todos->Colaboracion->Ciclo->Xtipo}} 
            <strong>{{$todos->Colaboracion->Ciclo->vliteral}}</strong> durant {{$todos->horas}} hores, en les que han participat els següents alumnes/as:
        </p>
        <ul style="font-size: normal;line-height: 1.5em">
            @foreach ($todos->Alumnos as $alumno)
                <li> <strong>{{$alumno->FullName}}</strong></li>
            @endforeach 
       </ul> 
       <p>Que dins d'aquesta empresa, En/Na/N' <strong>{{$datosInforme['instructor']->nombre}}</strong> amb DNI {{$datosInforme['instructor']->dni}},  ha sigut l'instructor/a de les pràctiques
           formatives.</p>
    </div>
    <div class="container" style="width:90%;">
        <br/>
        <p><strong>I per tal que així conste on convinga, signa el present escrit.</strong></p>
        
        <p>{{$datosInforme['poblacion']}},a {{$datosInforme['date']}} </p>
        <br/><br/><br/>
        <div style="width:40%; float:left; ">
            <p><strong>{{$datosInforme['secretario']}}</strong></p>
            <p>@if ($datosInforme['consideracion'] == 'En') SECRETARI @else SECRETARIA @endif</p>
        </div>
        <div style="width:40%; float:right; ">
            <p><strong>{{$datosInforme['director']}}</strong></p>
            <p>Vist-i-plau DIRECTOR</p>
        </div>
    </div>
</div>

<div class="page" style="font-size: large;line-height: 2em">
    @include('pdf.partials.cabecera')
    <br/>
    <div class="container col-lg-12" style="width:90%;">
        <p style="text-indent: 50px">@if ($datosInforme['consideracion'] == 'En') Don @else Doña @endif
            <strong>{{$datosInforme['secretario']}}</strong> 
            @if ($datosInforme['consideracion'] == 'En') secretario @else secretaria @endif del 
            {{$datosInforme['centro']}} de {{$datosInforme['poblacion']}}, provincia de {{$datosInforme['provincia']}}.</p>
    </div>
    <div class="container" >
        <strong>CERTIFICA:</strong>
        <br/>
    </div>
    <div class="container" style="width:95%">
        <p style="text-indent: 30px;text-align: justify">Que la empresa <strong> {{$empresa->nombre}} </strong> , ubicada en {{$empresa->direccion}} de
            {{$empresa->localidad}}, ha colaborado en las prácticas correspondientes a la Formación de Centros de Trabajo (FCT) del {{$todos->Colaboracion->Ciclo->Xtipo}} 
            <strong>{{$todos->Colaboracion->Ciclo->cliteral}}</strong> durante {{$todos->horas}} horas, en las que han participado los siguientes alumnos/as:
        </p>
        <ul style="font-size: normal;line-height: 1.5em">
            @foreach ($todos->Alumnos as $alumno)
                <li> <strong>{{$alumno->FullName}}</strong></li>
            @endforeach 
       </ul> 
       <p>Que dentro de esta empresa, Don/Doña <strong>{{$datosInforme['instructor']->nombre}}</strong> con DNI {{$datosInforme['instructor']->dni}}, ha sido el/la instructor/a de las prácticas
            formativas.</p>
    </div>
    <div class="container" style="width:90%;">
        <br/>
        <p><strong>I para que así conste donde convenga, firmo el presente escrito.</strong></p>
        
        <p>{{$datosInforme['poblacion']}},a {{$datosInforme['fecha']}} </p>
        <br/><br/><br/>
        <div style="width:40%; float:left; ">
            <p><strong>{{$datosInforme['secretario']}}</strong></p>
            <p>@if ($datosInforme['consideracion'] == 'En') SECRETARIO @else SECRETARIA @endif</p>
        </div>
        <div style="width:40%; float:right; ">
            <p><strong>{{$datosInforme['director']}}</strong></p>
            <p>Conforme DIRECTOR</p>
        </div>
    </div>
</div>
@foreach ($todos->Colaboradores as $instructor)

<div class="page" style="font-size: large;line-height: 2em">
    @include('pdf.partials.cabecera')
    <br/>
    <div class="container col-lg-12" style="width:90%;">
        <p style="text-indent: 50px">{{$datosInforme['consideracion']}}
            <strong>{{$datosInforme['secretario']}}</strong> 
            @if ($datosInforme['consideracion'] == 'En') secretari @else secretària @endif del 
            {{$datosInforme['centro']}} d'{{$datosInforme['poblacion']}}, província d'{{$datosInforme['provincia']}}.</p>
    </div>
    <div class="container" >
        <strong>CERTIFICA:</strong>
        <br/>
    </div>
    <div class="container" style="width:95%">
        <p style="text-indent: 30px;text-align: justify">Que l'empresa <strong> {{$empresa->nombre}} </strong> , ubicada a {{$empresa->direccion}} de/d'
            {{$empresa->localidad}}, ha col·laborat en les pràctiques corresponents a la Formació de Centres de Treball (FCT) del {{$todos->Colaboracion->Ciclo->Xtipo}} 
            <strong>{{$todos->Colaboracion->Ciclo->vliteral}}</strong> , en les que han participat els següents alumnes/as:
        </p>
        <ul style="font-size: normal;line-height: 1.5em">
            @foreach ($todos->Alumnos as $alumno)
                <li> <strong>{{$alumno->FullName}}</strong></li>
            @endforeach 
       </ul> 
       <p>Que dins d'aquesta empresa, En/Na/N' <strong>{{$instructor->nombre}} </strong> amb DNI {{$instructor->dni}},  ha col.laborat en l'instrucció dels alumnes en les pràctiques
           formatives durant <strong>{{$instructor->pivot->horas}}</strong> hores.</p>
    </div>
    <div class="container" style="width:90%;">
        <br/>
        <p><strong>I per tal que així conste on convinga, signa el present escrit.</strong></p>
        
        <p>{{$datosInforme['poblacion']}},a {{$datosInforme['date']}} </p>
        <br/><br/><br/>
        <div style="width:40%; float:left; ">
            <p><strong>{{$datosInforme['secretario']}}</strong></p>
            <p>@if ($datosInforme['consideracion'] == 'En') SECRETARI @else SECRETARIA @endif</p>
        </div>
        <div style="width:40%; float:right; ">
            <p><strong>{{$datosInforme['director']}}</strong></p>
            <p>Vist-i-plau DIRECTOR</p>
        </div>
    </div>
</div>
<div class="page" style="font-size: large;line-height: 2em">
    @include('pdf.partials.cabecera')
    <br/>
    <div class="container col-lg-12" style="width:90%;">
        <p style="text-indent: 50px">@if ($datosInforme['consideracion'] == 'En') Don @else Doña @endif
            <strong>{{$datosInforme['secretario']}}</strong> 
            @if ($datosInforme['consideracion'] == 'En') secretario @else secretaria @endif del 
            {{$datosInforme['centro']}} de {{$datosInforme['poblacion']}}, provincia de {{$datosInforme['provincia']}}.</p>
    </div>
    <div class="container" >
        <strong>CERTIFICA:</strong>
        <br/>
    </div>
    <div class="container" style="width:95%">
        <p style="text-indent: 30px;text-align: justify">Que la empresa <strong> {{$empresa->nombre}} </strong> , ubicada en {{$empresa->direccion}} de
            {{$empresa->localidad}}, ha colaborado en las prácticas correspondientes a la Formación de Centros de Trabajo (FCT) del {{$todos->Colaboracion->Ciclo->Xtipo}} 
            <strong>{{$todos->Colaboracion->Ciclo->cliteral}}</strong> , en las que han participado los siguientes alumnos/as:
        </p>
        <ul style="font-size: normal;line-height: 1.5em">
            @foreach ($todos->Alumnos as $alumno)
                <li> <strong>{{$alumno->FullName}}</strong></li>
            @endforeach 
       </ul> 
       <p>Que dentro de esta empresa, Don/Doña <strong>{{$instructor->nombre}}</strong> con DNI {{$instructor->dni}}, ha colaborador en la instrucción de los alumnos en las prácticas
           formativas durante <strong>{{$instructor->pivot->horas}}</strong> horas.</p>
    </div>
    <div class="container" style="width:90%;">
        <br/>
        <p><strong>I para que así conste donde convenga, firmo el presente escrito.</strong></p>
        
        <p>{{$datosInforme['poblacion']}},a {{$datosInforme['fecha']}} </p>
        <br/><br/><br/>
        <div style="width:40%; float:left; ">
            <p><strong>{{$datosInforme['secretario']}}</strong></p>
            <p>@if ($datosInforme['consideracion'] == 'En') SECRETARIO @else SECRETARIA @endif</p>
        </div>
        <div style="width:40%; float:right; ">
            <p><strong>{{$datosInforme['director']}}</strong></p>
            <p>Conforme DIRECTOR</p>
        </div>
    </div>
</div>
@endforeach
@endsection
