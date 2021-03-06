<!-- Modal -->
<div class="modal fade" id="AddAlumno" tabindex="-1" role="dialog" aria-labelledby="AddAlumnoTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="AddInstructorTitle">@lang("models.modelos.Alumno")
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h4>    
            </div>
            <form id='formFct' action='' method='post'>
                <div class="modal-body">
                    @csrf
                    @lang("validation.attributes.alumno") :
                    <select name='idAlumno'>
                        @foreach (hazArray(\Intranet\Entities\Alumno::misAlumnos()->orderBy('apellido1')->orderBy('apellido2')->get(),'nia',['NameFull','horasFct'],'-') as $key => $alumno)
                        <option value="{{ $key }}"> {{ $alumno }}</option>
                        @endforeach 
                    </select><br/>
                    @lang("validation.attributes.desde") : <input type='text' class="date" name='desde' value='{{$fct->desde}}'></input><br/>
                    @lang("validation.attributes.hasta") :<input type='text' class="date" name='hasta' value='{{$fct->hasta}}'></input><br/>
                    @lang("messages.generic.horas") :<input type='text' name='horas' value='{{$fct->horas}}'></input><br/><br/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <input id="submit" class="boton" type="submit" value="@lang('messages.generic.anadir') @lang('models.modelos.Alumno')">
                </div>
            </form>    
        </div>
    </div>
</div>