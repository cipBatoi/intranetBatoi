<div class="x_content">
    <p>
    <b>{{ $elemento->ModuloCiclo->Modulo->literal }} - {{ $elemento->ModuloCiclo->Ciclo->literal }}</b><br/>
    {{ $elemento->ModuloCiclo->Ciclo->Departament->literal }}</p>
    Ficheros : <a href="/programacion/{{$elemento->id}}/document" target="_blank"><i class='fa fa-file-pdf-o'></i> @lang("models.modelos.Programacion")</a>
    @for ($i=1;$i<=$elemento->anexos;$i++)
    <a href="/programacion/{{$elemento->id}}/veranexo/{{$i}}" target="_blank"><i class='fa fa-file-pdf-o'></i> {{ trans('messages.buttons.anexo')}}{{$i}}</a></span>
    @endfor
    <br/>Estado : {{ trans('messages.situations.' . $elemento->estado) }}<br/>
    @if ($elemento->estado < 3)
    {!! Form::open(['route' => ['programacion.storeanexo',$elemento->id],'class'=>'form-horizontal form-label-left','enctype'=>"multipart/form-data"]) !!}
        {{ csrf_field() }}
        {!! Field::File('Anexo') !!} 
        <a href="/programacion/{{$elemento->id}}/deleteanexo" class="btn btn-danger">@lang("messages.buttons.delete") </a>
        {!! Form::submit(trans('messages.buttons.submit'),['class'=>'btn btn-success','id'=>'submit']) !!}
        <a href='/programacion' class="btn btn-primary">@lang("messages.buttons.atras")</a>
    {!! Form::close() !!}
    
    </div>
    @endif
</div>
