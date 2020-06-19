<?php

namespace Intranet\Http\Controllers;

use Illuminate\Http\Request;
use Intranet\Entities\AlumnoFctAval;
use Intranet\Entities\Reunion;
use Intranet\Entities\Profesor;
use Intranet\Entities\Asistencia;
use Response;
use Intranet\Botones\BotonImg;
use Intranet\Entities\TipoReunion;
use Intranet\Entities\OrdenReunion;
use Jenssegers\Date\Date;
use Styde\Html\Facades\Alert;
use Intranet\Entities\Documento;
use Intranet\Jobs\SendEmail;
use Intranet\Entities\Grupo;
use Illuminate\Support\Facades\DB;
use mikehaertl\pdftk\Pdf;

class ReunionController extends IntranetController
{

    use traitImprimir;

    protected $perfil = 'profesor';
    protected $model = 'Reunion';
    protected $gridFields = ['XGrupo', 'XTipo', 'Xnumero', 'descripcion', 'fecha', 'curso', 'id'];
    protected $modal = true;
    protected $parametresVista = [  'modal' => ['password']];

    /**
     * @param $elemento
     * @return string
     */
    public function makeMissage($elemento): string
    {
        if (haVencido($elemento->fecha))
            return "Ja està disponible l'acta de la reunió " . $elemento->descripcion . " del dia " . $elemento->fecha;

        return "Estas convocat a la reunió:  " . $elemento->descripcion . ' el dia ' . $elemento->fecha . ' a ' .
                $elemento->Espacio->descripcion;
    }

    protected function search()
    {
        return Reunion::MisReuniones()->get();
    }

    protected function createWithDefaultValues( $default=[]){
        return new Reunion(['idProfesor'=>AuthUser()->dni,'curso'=>Curso()]);
    }

    public function store(Request $request)
    {
        $elemento = Reunion::find($this->realStore($request));
        $contador = 1;
        //dd(TipoReunion::ordenes($elemento->tipo));
        foreach (TipoReunion::ordenes($elemento->tipo) as $key => $texto) {
            if (strpos($texto, '->'))
                $contador = $this->storeItems($contador,$texto,$elemento);
            else
                $contador = $this->storeItem($elemento->id,$contador,$texto,TipoReunion::resumen($elemento->tipo) != null ? TipoReunion::resumen($elemento->tipo)[$key] : '');
        }
        if ($elemento->fichero != '') return back();
        return redirect()->route('reunion.update', ['reunion' => $elemento->id]);
    }

    private function storeItems($contador,$texto,$elemento){
        $consulta = explode('->', $texto,3);
        $clase = $this->namespace . $consulta[0];
        $funcion = $consulta[1];
        $campo = $consulta[2];
        //dd("$clase::$funcion()");
        foreach ($clase::$funcion()->get() as $element)
            $contador = $this->storeItem($elemento->id,$contador,$element->$campo,TipoReunion::resumen($elemento->tipo) != null ? TipoReunion::resumen($elemento->tipo).$contador : '');
        return $contador;
    }

    private function storeItem($id,$contador,$text,$resumen){
        $orden = new OrdenReunion();
        $orden->idReunion = $id;
        $orden->orden = $contador++;
        $orden->descripcion = $text;
        $orden->resumen = $resumen;
        $orden->save();
        return $contador;
    }
    public function edit($id)
    {
        $elemento = Reunion::find($id);
        if ($elemento->fichero != '')
            return parent::edit($id);
        else {
            $ordenes = OrdenReunion::where('idReunion', '=', $id)->get();
            $Profesores = Profesor::select('dni', 'apellido1', 'apellido2', 'nombre')
                    ->OrderBy('apellido1')
                    ->OrderBy('apellido2')
                    ->get();
            foreach ($Profesores as $profesor) {
                $tProfesores[$profesor->dni] = $profesor->apellido1 . ' ' . $profesor->apellido2 . ',' . $profesor->nombre;
            }
            $sProfesores = $elemento->profesores()->orderBy('apellido1')->orderBy('apellido2')->get(['dni', 'apellido1', 'apellido2', 'nombre']);

            $elemento->setInputType('tipo', ['type' => 'hidden']);
            $elemento->setInputType('grupo', ['type' => 'hidden']);
            $default = $elemento->fillDefautOptions();
            $modelo = $this->model;
            if (!$elemento->avaluacioFinal && !$elemento->extraOrdinaria){
                return view('reunion.asistencia', compact('elemento', 'default', 'modelo', 'tProfesores', 'sProfesores', 'ordenes'));
            }
            return $this->editAvaluacioFinal(compact('elemento', 'default', 'modelo', 'tProfesores', 'sProfesores', 'ordenes'));

        }
    }

    private function editAvaluacioFinal($entorno){
        extract($entorno);
        $grupo = Grupo::QTutor($elemento->dni)->first();
        $sAlumnos = $elemento->alumnos()->orderBy('apellido1')->orderBy('apellido2')->get();
        if ($elemento->avaluacioFinal){
            $tAlumnos = hazArray($grupo->Alumnos->whereNotIn('nia',hazArray($sAlumnos,'nia')),'nia','nameFull');
            $select = 'auxiliares.promociona';
        }
        else {
            if ($elemento->GrupoClase->curso == 1){
                if ($grupo->isSemi) {
                    $tAlumnos = hazArray($grupo->Alumnos->whereNotIn('nia',hazArray($sAlumnos,'nia')),'nia','nameFull');
                } else {
                    if ($actaFinal = Reunion::actaFinal($elemento->idProfesor)->first()) {
                        $tAlumnos = hazArray($actaFinal->noPromocionan->whereNotIn('nia',hazArray($sAlumnos,'nia')),'nia','nameFull');
                    }
                }
            }
            else {
                $tAlumnos = hazArray($grupo->Alumnos->whereNotIn('nia', hazArray(AlumnoFctAval::misFcts()->titulan()->get(),'idAlumno'))
                    ->whereNotIn('nia',hazArray($sAlumnos,'nia')),'nia','nameFull');
            }
            $select = 'auxiliares.promocionExtraordinaria';
        }
        return view('reunion.asistencia', compact('elemento', 'default', 'modelo', 'tProfesores', 'sProfesores', 'ordenes','tAlumnos','sAlumnos','select'));


    }

    public function altaProfesor(Request $request, $reunion_id)
    {
        $reunion = Reunion::find($reunion_id);
        $reunion->profesores()->syncWithoutDetaching([$request->idProfesor => ['asiste' => 1]]);
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function borrarProfesor($reunion_id, $profesor_id)
    {

        $reunion = Reunion::find($reunion_id);
        $reunion->profesores()->detach($profesor_id);
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function borrarAlumno($reunion_id, $alumno_id)
    {

        $reunion = Reunion::find($reunion_id);
        $reunion->alumnos()->detach($alumno_id);
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function altaAlumno(Request $request, $reunion_id)
    {
        $reunion = Reunion::find($reunion_id);
        $reunion->alumnos()->syncWithoutDetaching([$request->idAlumno => ['capacitats' => $request->capacitats]]);
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function altaOrden(Request $request, $reunion_id)
    {
        if ($request->orden == '') {
            $max = OrdenReunion::where('idReunion', '=', $reunion_id)->max('orden');
            $request->merge(['orden' => $max + 1]);
        }
        $orden = OrdenReunion::create($request->all());
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function borrarOrden($reunion_id, $orden_id)
    {
        OrdenReunion::find($orden_id)->delete();
        return redirect()->route('reunion.update', ['reunion' => $reunion_id]);
    }

    public function notify($id)
    {
        $elemento = Reunion::findOrFail($id);
        foreach (Asistencia::where('idReunion', '=', $id)->get() as $profesor)
            avisa($profesor->idProfesor, $this->makeMissage($elemento), "/reunion/" . $id . "/pdf");
        return back();
    }

    public function email($id)
    {
        $elemento = Reunion::findOrFail($id);
        //esborra fitxer si ja estaven
        if (file_exists(storage_path("tmp/Reunion_$id.pdf")))
            unlink(storage_path("tmp/Reunion_$id.pdf"));
        if (file_exists(storage_path("tmp/invita_$id.ics")))
            unlink(storage_path("tmp/invita_$id.ics"));
        //guarda fitxers i construix variable
        $this->construye_pdf($id)->save(storage_path("tmp/Reunion_$id.pdf"));
        if (!haVencido($elemento->fecha)) {
            file_put_contents(storage_path("tmp/invita_$id.ics"), $this->do_ics($elemento->id));
            $attach = ["tmp/Reunion_$id.pdf" => 'application/pdf', "tmp/invita_$id.ics" => 'text/calendar'];
        } else
            $attach = ["tmp/Reunion_$id.pdf" => 'application/pdf'];
        
        $asistentes = Asistencia::where('idReunion', '=', $id)->get();
        $remitente = ['email' => $elemento->Responsable->email, 'nombre' => $elemento->Responsable->FullName];
        foreach ($asistentes as $asistente) {
            if (!haVencido($elemento->fecha)) 
                dispatch(new SendEmail($asistente->Profesor->email, $remitente, 'email.convocatoria', $elemento, $attach));
            else
                dispatch(new SendEmail($asistente->Profesor->email, $remitente, 'email.reunion', $elemento, $attach));
        }
        Alert::info('Correus enviats');
        return back();
    }

    protected function iniBotones()
    {
        $this->panel->setBotonera(['create'], ['pdf']);
        $actual = AuthUser()->dni;
        $this->panel->setBoton('grid', new BotonImg('reunion.edit', ['img' => 'fa-pencil', 'where' => ['idProfesor', '==', $actual, 'archivada', '==', '0']]));
        $this->panel->setBoton('grid', new BotonImg('reunion.delete', ['where' => ['idProfesor', '==', $actual, 'archivada', '==', '0']]));
        $this->panel->setBoton('grid', new BotonImg('reunion.notification', ['where' => ['idProfesor', '==', $actual, 'fichero', '==', '', 'archivada', '==', '0']]));
        $this->panel->setBoton('grid', new BotonImg('reunion.email', ['where' => ['idProfesor', '==', $actual, 'fichero', '==', '']]));
        $this->panel->setBoton('grid', new BotonImg('reunion.ics', ['img' => 'fa-calendar', 'where' => ['fecha', 'posterior', Date::yesterday()]]));
        $this->panel->setBoton('grid', new BotonImg('reunion.saveFile', ['where' => ['idProfesor', '==', $actual, 'archivada', '==', '0', 'fecha', 'anterior', Date::yesterday()]]));
        $this->panel->setBoton('grid', new BotonImg('reunion.deleteFile', ['img' => 'fa-unlock','where' => ['idProfesor', '==', $actual, 'archivada', '==', '1', 'fecha', 'anterior', Date::yesterday()]]));

    }

    public function pdf($id)
    {
        $elemento = Reunion::findOrFail($id);
        if ($elemento->fichero != '')
            if (file_exists(storage_path('/app/' . $elemento->fichero)))
                return response()->file(storage_path('/app/' . $elemento->fichero));
            else {
                Alert::message('No trobe fitxer', 'danger');
                return back();
            }
        else {
            if ($elemento->archivada)  $this->saveFile($id);
            return $this->construye_pdf($id)->stream();
        }
    }

    public function saveFile($id)
    {
        $elemento = $this->class::find($id);
        if ($elemento->fichero != '')
            $nomComplet = $elemento->fichero;
        else {
            $nom = 'Acta_' . $elemento->id . '.pdf';
            $directorio = 'gestor/' . Curso() . '/' . $this->model;
            $nomComplet = $directorio . '/' . $nom;
            if (!file_exists(storage_path('/app/' . $nomComplet)))
                $this->construye_pdf($id)->save(storage_path('/app/' . $nomComplet));
        }
        $elemento->archivada = 1;
        $elemento->fichero = $nomComplet;
        DB::transaction(function () use ($elemento) {
            Documento::crea($elemento, ['propietario' => $elemento->Creador->FullName,
                'tipoDocumento' => 'Acta',
                'descripcion' => $elemento->descripcion,
                'fichero' => $elemento->fichero,
                'supervisor' => $elemento->Creador->FullName,
                'grupo' => str_replace(' ', '_', $elemento->Xgrupo),
                'tags' => TipoReunion::literal($elemento->tipo),
                'created_at' => new Date($elemento->fecha),
                'rol' => config('roles.rol.profesor')]);
            $elemento->save();
        });
        return back();
    }

    public function deleteFile(Request $request,$id)
    {
        if ($request->pass == date('mdy')){
            $elemento = $this->class::find($id);
            $document = Documento::where('tipoDocumento','Acta')->where('curso',Curso())->where('idDocumento',$elemento->id)->first();
            if ($elemento->fichero != '' && $document)
                DB::transaction(function () use ($elemento,$document) {
                    $nom = $elemento->fichero;
                    $document->delete();
                    $elemento->archivada = 0;
                    $elemento->fichero = '';
                    $elemento->save();
                    unlink(storage_path('/app/' . $nom));
                });
        }
        return back();
    }

    public function listado($dia = null)
    {
        foreach (Grupo::all() as $grupo)
            foreach ( config('auxiliares.reunionesControlables') as $tipo => $howMany) {
                $reuniones[$grupo->nombre][$tipo] = Reunion::Convocante($grupo->tutor)->Tipo($tipo)->Archivada()->get();
            }
        return view('reunion.control', compact('reuniones'));
    }

    public function avisaFaltaActa(Request $request)
    {
        $cont = 0;
        if ($request->quien) $grupos = Grupo::where('curso',$request->quien)->get();
        else $grupos = Grupo::all();
        
        foreach ($grupos as $grupo) {
            if (!Reunion::Convocante($grupo->tutor)->Tipo($request->tipo)->Numero($request->numero)->Archivada()->count()) {
                $texto = 'Et falta per fer i/o arxivar la reunio ' . TipoReunion::literal($request->tipo) . ' ';
                $texto .= $request->numero > 0 ? config('auxiliares.numeracion')[$request->numero] : '';
                avisa($grupo->tutor, $texto);
                $cont++;
            }
        }
        Alert::info($cont . ' Avisos enviats');
        return back();
    }

    private function construye_pdf($id)
    {
        $elemento = Reunion::findOrFail($id);
        $hoy = new Date($elemento->fecha);
        $elemento->dia = FechaString($hoy);
        $elemento->hora = $hoy->format('H:i');
        $hoy = new Date($elemento->updated_at);
        $elemento->hoy = haVencido($elemento->fecha) ? $elemento->dia : FechaString($hoy);

        $ordenes = OrdenReunion::where('idReunion', '=', $id)->get();
        $informe = haVencido($elemento->fecha) ? 'pdf.reunion.' . TipoReunion::acta($elemento->tipo) : 'pdf.reunion.' . TipoReunion::convocatoria($elemento->tipo);
        $orientacion = 'portrait';
        $pdf = $this->hazPdf($informe, $ordenes, $elemento, $orientacion, 'a4');
        return $pdf;
    }


    public static function preparePdf($informe,$aR){
        $hoy = new Date();
        $elemento = FechaString($hoy,'ca');
        return self::hazPdf($informe, $aR,$elemento ,'portrait','a4');
    }


}
